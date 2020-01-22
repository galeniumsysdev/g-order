<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Product;
use App\Cart;
use App\SoHeader;
use App\SoLine;
use App\User;
use Session;
use Auth;
use File;
use Illuminate\Support\Facades\DB;
use App\Customer;
use App\CustomerSite;
use App\Category;
use App\ProductFlexfield;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
//use App\Notifications\NewPurchaseOrder;
use Mail;
use App\Mail\CreateNewPo;
use Illuminate\Database\Eloquent\Collection;
use App\DPLSuggestNo;
use App\PoDraftHeader;
use App\PoDraftLine;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;


class ProductController extends Controller
{

  protected function validatorImage(array $data)
  {
      return Validator::make($data, [
          'name' => 'required|string|max:255',
          'email' => 'required|string|email|max:255|unique:users',
      ]);
  }

  public function getPrice(Request $request)
  {
    if (isset(Auth::user()->customer_id))
    {
      $vid=Auth::user()->id;
    }else{
      $vid='';
    }
    $price = DB::select("select getProductPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->uom]);
    $price = $price[0];
    $hargadiskon = DB::select("select getDiskonPrice ( :cust, :prod, :uom, 1 ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->uom]);
    $hargadiskon = $hargadiskon[0];
    $rate = DB::select("select p.satuan_primary, getItemRate ( p.satuan_primary, :uom, p.id ) AS rate, p.itemcode from products as p where id = :id", ['id'=>$request->product,'uom'=>$request->uom]);
    $konversi = $rate[0];
  //  dd($price->harga);
    return response()->json([
                    'result' => 'success',
                    'price' => (float)$price->harga,
                    'diskon' => (float)$hargadiskon->harga,
                    'konversi' => (float)$konversi->rate,
                    'uomprimary' =>$konversi->satuan_primary,
                    'itemcode' =>$konversi->itemcode
                  ],200);
  }

  /*public function getDistributor()
  {
    if(isset(Auth::user()->customer_id)){
      $customer = Customer::find(Auth::user()->customer_id);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
      if (is_null($oldDisttributor) or $oldDisttributor ==null)
      {
        if($customer->hasDistributor()->count()>1)
        {
          $distributor = DB::table('outlet_distributor as o')
                        ->join('customers as c','o.distributor_id','=','c.id')
                        ->join('users as u','u.customer_id','=','c.id')
                        ->where('o.outlet_id','=',Auth::user()->customer_id)
                        ->select('c.id','c.customer_name','u.avatar')
                        ->get();
          return $distributor;
          //return view('shop.sites',['distributors' => $distributor]);

        }else{//if(count($distributor)>1) {

            foreach ($customer->hasDistributor as $dist) {
              //dd('distributor:'.$dist->pivot->customer_name);
              Session::put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag,'export_flag'=>$dist->export_flag]);
              $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
            }
            return $oldDisttributor;
        }
      }
    }
  }*/

  public function getDistributor($jns)
  {
    $dist = Auth::user()->customer->hasDistributor()->whereRaw("ifnull(inactive,0)!=1")->get();

    if(in_array('PHARMA',$jns))
    {
      $dist=$dist->where('pharma_flag','=','1');
    }
    if(in_array('PSC',$jns))
    {
      $dist=$dist->where('psc_flag','=','1');
    }
    if(in_array('INTERNATIONAL',$jns))
    {
      $dist=$dist->where('export_flag','=','1');
    }
    if(in_array('TollIn',$jns))
    {
      $dist=$dist->where('tollin_flag','=','1');
    }
    return $dist;
  }

  public function getAjaxProduct(Request $request)
  {
    $products =Product::where([['enabled_flag','=','Y'],['title','like',$request->input('query')."%"]]);
    $products =$products->select('title','id')->get();
    return response()->json($products);
  }

  public function updatePareto(Request $request)
  {

    DB::beginTransaction();
    try{
      $product=Product::where('id','=',$request->idpareto)->update(['pareto'=>1,'last_update_by'=>Auth::user()->id]);
      DB::commit();
      return redirect()->route('product.pareto')->withMessage('Product Updated');
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }

  }
  public function destroyPareto($id)
  {
    DB::beginTransaction();
    try{
      $product=Product::where('id','=',$id)->update(['pareto'=>0,'last_update_by'=>Auth::user()->id]);
      DB::commit();
      return redirect()->route('product.pareto')->withMessage('Product removed from pareto products');
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }
  }

  public function getSqlProduct()
  {
    if(Auth::check()){
      if(Auth::user()->hasRole('Distributor'))
      {
          $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getProductPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item,getitemrate(p.satuan_primary, p.satuan_secondary, p.id) as rate, getDiskonPrice(:cust1, p.id, p.satuan_secondary,1 ) price_diskon, p.pareto from products as p where enabled_flag='Y' and getProductPrice ( :cust3, p.id, p.satuan_secondary  ) >0";
      }else{
          $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getProductPrice ( :cust, p.id, p.satuan_primary  ) AS harga, substr(itemcode,1,2) as item,getitemrate(p.satuan_primary, p.satuan_primary, p.id) as rate, getDiskonPrice(:cust1, p.id, p.satuan_primary,1 ) price_diskon,p.pareto from products as p where enabled_flag='Y' and getProductPrice ( :cust3, p.id, p.satuan_secondary  ) >0 ";
      }

      if(isset(Auth::user()->customer_id)){
        $customer = Customer::find(Auth::user()->customer_id);
        /*$oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
        if(!is_null($oldDisttributor)){*/
          if($customer->psc_flag!="1" )
          {
            $sqlproduct .= " and exists (select 1
                from category_products  as cp
                  ,categories cat
                where cp.product_id = p.id
                  and cp.flex_value = cat.flex_value
                  and cat.parent not like 'PSC')";
          }
          if($customer->pharma_flag!="1")
          {
            $sqlproduct .= " and exists (select 1
                from category_products as cp
                  ,categories cat
                where cp.product_id = p.id
                  and cp.flex_value = cat.flex_value
                  and cat.parent not like 'PHARMA')";
          }
          if($customer->export_flag!="1")
          {
            $sqlproduct .= " and exists (select 1
                from category_products as cp
                  ,categories cat
                where cp.product_id = p.id
                  and cp.flex_value = cat.flex_value
                  and cat.parent not like 'INTERNATIONAL')";
          }

          if($customer->tollin_flag=="1")
          {
            $sqlproduct .= " and exists (select 1
                from qp_list_lines_v qll
                where qll.product_attr_value = p.inventory_item_id
                  and qll.list_header_id = '".$customer->price_list_id."')";
          }else{
            $sqlproduct .= " and exists (select 1
                from category_products as cp
                  ,categories cat
                where cp.product_id = p.id
                  and cp.flex_value = cat.flex_value
                  and cat.parent not like 'TollIn')";
          }
          if (!empty(Auth::user()->customer->masa_berlaku)) $masaberlaku =Auth::user()->customer->masa_berlaku; else $masaberlaku = date_create("2017-01-01");
          if(Auth::user()->customer->ijin_pbf==0 or $masaberlaku < \Carbon\Carbon::now()->toDateString() ){
            $sqlproduct .= " and p.tipe_dot not in ('BIRU','MERAH','HIJAU')";
          }elseif(Auth::user()->customer->ijin_pbf==2 and  $masaberlaku >= \Carbon\Carbon::now()->toDateString()){/*jika ijin toko bat*/
            $sqlproduct .= " and p.tipe_dot not in ('MERAH')";
          }

          if(Auth::user()->hasRole('Apotik/Klinik') or Auth::user()->hasRole('Outlet'))
          {
            $sqlproduct .= " and exists (select 1
                from category_products as cp
                  ,categories cat
                where cp.product_id = p.id
                  and cp.flex_value = cat.flex_value
                  and cat.description <> 'BPJS')";
            if(isset(Auth::user()->customer->outet_type_id) and $customer->pharma_flag=="1")
            {
              if(!in_array(Auth::user()->customer->categoryOutlet->name,['Apotik', 'PBF', 'Rumah Sakit/Klinik']))
              {
                $sqlproduct .= " and p.tipe_dot != 'MERAH'";
              }
              if(!in_array(Auth::user()->customer->categoryOutlet->name,['Apotik', 'PBF', 'Rumah Sakit/Klinik','Toko Obat Berijin']))
              {
                $sqlproduct .= " and p.tipe_dot != 'BIRU'";
              }
            }

          }


        //}
      }

   }
   //var_dump($sqlproduct);
    return $sqlproduct;
  }

  public function getIndex()
  {
    if(Auth::check()){
        if(Auth::user()->hasRole('IT Galenium')) {
            return redirect('/admin');
        }elseif(Auth::user()->hasRole('Distributor') || Auth::user()->hasRole('Outlet') || Auth::user()->hasRole('Apotik/Klinik') ) {
              return redirect('/product/buy');
        }elseif(Auth::user()->hasRole('KurirGPL'))      {
              return redirect()->route('order.shippingSO');
        }else{/*if(Auth::user()->hasRole('Marketing PSC') || Auth::user()->hasRole('Marketing Pharma')) {*/
            return redirect('/home');
        }
    }else{

      $products = Product::where([['enabled_flag','=','Y'],['pareto','=',1]])->get();

      return view('shop.index',['products' => $products]);
    }

  }

  public function displayProduct(Request $request)
  {
    $perPage = 12; // Item per page
    $currentPage = Input::get('page') - 1;
    /*$pilihandistributor = Input::get('dist','');
    if ($pilihandistributor!='')
    {
      $dist = Customer::where('id','=',$pilihandistributor)->select('id','customer_name','pharma_flag','psc_flag','export_flag')->first();
      $request->session()->put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag,'export_flag'=>$dist->export_flag]);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
    }
      $oldDisttributor = $this->getDistributor();

      if(!is_null($oldDisttributor))
      {
        if(!is_array($oldDisttributor))
        {
          return view('shop.sites',['distributors' => $oldDisttributor]);
        }
      }
    */

    /*$sqlproduct = $this->getSqlProduct();
    $sqlproduct .= " order by pareto desc, title asc";
    //var_dump($sqlproduct);

      if (isset(Auth::user()->customer_id))
      {
        $vid=Auth::user()->id;
      }else{
        $vid='';
      }
    // /  dd($sqlproduct);
    //  $sqlproduct .= " limit 12";
    $dataproduct = DB::select($sqlproduct, ['cust'=>$vid,'cust1'=>$vid ]);

    foreach($dataproduct as $dp)
    {
      $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
      $dp->uom=$uom;
      $prg=null;
      if(Auth::user()->customer->oracle_customer_id){
      $prg =DB::table('qp_pricing_discount as qpd')
          ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
          ->where('qpd.list_line_type_code', '=','PRG')
          ->where('customer_id','=',Auth::user()->customer->oracle_customer_id)
          ->where('item_id','=',$dp->inventory_item_id)
          ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
          ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit')
          ->orderBy('pricing_group_sequence','asc')
          ->first();
      }
      if($prg) $dp->promo = $prg;else $dp->promo=null;
    }

    $products = collect($dataproduct);*/

    /*$pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
    $products= new LengthAwarePaginator($pagedData, count($products), $perPage);

  //  dd($products);
    $products ->setPath(url()->current());*/
    return view('swipe');
    //return view('shop.product',['products' => $products]);
  }

  public function search(Request $request)
  {
    $perPage = 12; // Item per page
    $currentPage = Input::get('page') - 1;

    $sqlproduct = $this->getSqlProduct();

    if(isset($request->search_product))
    {
      //$products = Product::where('title','like','%'.$request->search_product.'%')->paginate(12);//all();
      $sqlproduct .= " and title like '".$request->search_product."%'";

    }
    if (isset(Auth::user()->customer_id))
    {
      $vid=Auth::user()->id;
    }else{
      $vid='';
    }
    $sqlproduct .= " order by pareto desc, title asc";
    $dataproduct = DB::select($sqlproduct, ['cust'=>$vid,'cust1'=>$vid,'cust3'=>$vid]);

    foreach($dataproduct as $dp)
    {
      $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
      $dp->uom=$uom;
      $prg=collect([]);
      if(Auth::user()->customer->oracle_customer_id){
      $prg =DB::table('qp_pricing_discount as qpd')
          ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
          ->where('qpd.list_line_type_code', '=','PRG')
          ->where('customer_id','=',Auth::user()->customer->oracle_customer_id)
          ->where('item_id','=',$dp->inventory_item_id)
          ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
          ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit')
          ->orderBy('pricing_group_sequence','asc')
          ->first();
      }
      if(!empty($prg)) $dp->promo = $prg;else $dp->promo=null;
    }
    //  $sqlproduct .= " limit 12";
    $products = collect($dataproduct);
    $polineexist = DB::table('po_draft_lines')
            ->join('po_draft_headers as pdh', 'po_draft_lines.po_header_id','=','pdh.id')
            ->where('pdh.customer_id','=',Auth::user()->customer_id)
            ->select('product_id','qty_request','uom')
            ->get();
    /*$pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
    $products= new LengthAwarePaginator($pagedData, count($products), $perPage);
    $products->appends($_REQUEST)->render();
    $products ->setPath(url()->current());*/
    return view('shop.index',['products' => $products,'polineexist'=>$polineexist]);
  }

  public function category($id)
  {
      $kategory = Category::find($id);
      //$products = $kategory->products()->paginate(12);
      $perPage = 30; // Item per page
    /*  $oldDisttributor = $this->getDistributor();

      if(!is_null($oldDisttributor))
      {
        if(!is_array($oldDisttributor))
        {
          return view('shop.sites',['distributors' => $oldDisttributor]);
        }
      }*/
      $sqlproduct = $this->getSqlProduct();
      $currentPage = Input::get('page') - 1;
      $sqlproduct .= " and exists (select 1
          from category_products as cat
          where cat.product_id = p.id
            and cat.flex_value = '".$id."')";
      if (isset(Auth::user()->customer_id))
      {
        $vid=Auth::user()->id;
      }else{
        $vid='';
      }
      $sqlproduct .= " order by pareto desc, title asc";
      $dataproduct = DB::select($sqlproduct, ['cust'=>$vid,'cust1'=>$vid,'cust3'=>$vid]);

      foreach($dataproduct as $dp)
      {
        $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
        $dp->uom=$uom;
        $prg=null;
        if(Auth::user()->customer->oracle_customer_id){
        $prg =DB::table('qp_pricing_discount as qpd')
            ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
            ->where('qpd.list_line_type_code', '=','PRG')
            ->where('customer_id','=',Auth::user()->customer->oracle_customer_id)
            ->where('item_id','=',$dp->inventory_item_id)
            ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
            ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit')
            ->orderBy('pricing_group_sequence','asc')
            ->first();

        }
        if(isset($prg)) $dp->promo = $prg;else $dp->promo=null;
      }
      //  $sqlproduct .= " limit 12";
      $products = collect($dataproduct);
      $polineexist = DB::table('po_draft_lines')
              ->join('po_draft_headers as pdh', 'po_draft_lines.po_header_id','=','pdh.id')
              ->where('pdh.customer_id','=',Auth::user()->customer_id)
              ->select('product_id','qty_request','uom')
              ->get();
      //dd($products);
      /*$pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
      $products= new LengthAwarePaginator($pagedData, count($products), $perPage);
      $products ->setPath(url()->current());*/
    return view('shop.index',['products' => $products,'nama_kategori'=>$kategory->description,'polineexist'=>$polineexist]);
  }

  public function show($id)
  {
    //$product=Product::find($id);
    $sqlproduct = "select id, title, imagePath,description, description_en,satuan_secondary,satuan_primary, inventory_item_id, getItemPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item,getitemrate(p.satuan_primary, p.satuan_secondary, p.id) as rate, pareto  from products as p where p.id = '".$id."'";
    //$sqlproduct = $this->getSqlProduct();
    if (isset(Auth::user()->customer_id))
    {
      $vid=Auth::user()->id;
    }else{
      $vid='';
    }
    //  $sqlproduct .= " limit 12";
     $product = DB::select($sqlproduct, ['cust'=>$vid]);
     $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$product[0]->id)->select('uom_code')->get();
     $product[0]->uom=$uom;
     $polineexist = collect([]);
     $prg=null;
     if(Auth::check()){
       if(Auth::user()->customer->oracle_customer_id){
       $prg =DB::table('qp_pricing_discount as qpd')
           ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
           ->where('qpd.list_line_type_code', '=','PRG')
           ->where('customer_id','=',Auth::user()->customer->oracle_customer_id)
           ->where('item_id','=',$product[0]->inventory_item_id)
           ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
           ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit')
           ->orderBy('pricing_group_sequence','asc')
           ->first();
        if(!empty($prg)) $product[0]->promo = $prg;else $product[0]->promo=null;
       }
       $polineexist = DB::table('po_draft_lines')
               ->join('po_draft_headers as pdh', 'po_draft_lines.po_header_id','=','pdh.id')
               ->where('pdh.customer_id','=',Auth::user()->customer_id)
               ->select('product_id','qty_request','uom')
               ->get();
     }

     return view('shop.detailProduct01',['product' => $product[0],'polineexist'=>$polineexist]);
  }

  public function index()
  {
    $products=Product::all();//paginate(10);

     return view('admin.product.index',['products' => $products,'menu'=>'product']);
  }



  public function master($id)
  {
    //$product=Product::find($id);
    $categories = Category::where('enabled_flag','=','Y')->get();
    $tipedot=array('NO DOT'=>'Tanpa Dot','HIJAU'=>'Hijau','BIRU'=>'Biru','MERAH'=>'Merah');
    $product = DB::table('products as p')
              ->leftjoin('category_products as cp','p.id','=','cp.product_id')
              ->leftjoin('categories as c', 'cp.flex_value','=','c.flex_value')
              ->where('p.id','=',$id)
              ->select('p.id as id','p.title','p.itemcode','p.description','p.description_en','p.imagePath','p.satuan_primary','c.description as category_name','c.flex_value','c.parent','p.inventory_item_id','p.enabled_flag','p.pareto','p.long_description','p.tipe_dot')
              ->first();
    //dd($product) ;
    return view('admin.product.edit',['product' => $product,'categories'=>$categories,'menu'=>'product','tipedot'=>$tipedot]);
  }

  public function update(Request $request,$id)
  {
    DB::beginTransaction();
    try{
    $product=Product::find($id);
    $image = $request->file('input_img');
    $this->validate($request, [
        'nama' => 'required|unique:products,title,'.$id.',id|max:191',
        'enabledflag'=>'required',
    ]);
    //echo($request->file('input_img')->getClientOriginalName());
    /*product image yg lama di hapus jika file input tidak kosong*/
    if($image){
      if($image->getClientOriginalName()!=$product->imagePath and
        $product->imagePath!=""){
          //echo ("<br>try to delete image");
        if (isset($product->imagePath)){
          if ($product->imagePath!=""){
            if (File::exists(public_path('img\\'.$product->imagePath))){
              unlink(public_path('img\\'.$product->imagePath));
              //echo ("<br>delete image");
            }
            $product->imagePath="";
          }
        }
      }
      //echo("<br>Product:".$product->imagePath);
      if(is_null($product->imagePath) or $product->imagePath=="")
      {
        $this->validate($request, [
            'input_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('img');
      //  dd($destinationPath);
        $image->move($destinationPath, $input['imagename']);
        $product->imagePath = $input['imagename'] ;
      }
    }


    //dd('stop');
    //$product=Product::find($id);
    $product->title =  $request->nama;
    $product->pareto = $request->pareto;
    $product->enabled_flag = $request->enabledflag;
    $product->description =$request->id_descr;
    $product->description_en =$request->en_descr;
    $product->long_description = $request->generik;
    $product->tipe_dot=$request->dot_obat;
    $product->save();
    if($request->category){
      $product->categories()->detach();
      $product->categories()->attach($request->category);
      //$product->categories()->sync([$request->category]);
    }
    DB::commit();
  }catch (\Exception $e) {
    DB::rollback();
    throw $e;
  }


    return redirect()->route('product.master',$id)->withMessage('Product Updated');
  }

  //show cart
    public function getCart(){
      /*if (!Session::has('cart')){
        //Session::forget('cart');
        return view('shop.shopping-cart',['products'=>null]);
      }*/
      /*$oldCart = Session::get('cart');
      $cart =  new Cart($oldCart);*/
      $headerpo = PoDraftHeader::firstorCreate(['customer_id'=>Auth::user()->customer_id]);
      $linepo = PoDraftLine::where('po_header_id','=',$headerpo->id)->get();
      if($linepo->count()==0) $linepo=null;
      $jns = PoDraftLine::where('po_header_id','=',$headerpo->id)->select('jns')->groupBy('jns')->get();
//	$jns = $jns->toArray();
      $jns=$jns->pluck('jns')->toArray();
      //$jns = array_unique(array_pluck($cart->items,'jns'));
      $dist=$this->getDistributor($jns);
      //return view('shop.shopping-cart',['products'=>$cart->items, 'totalPrice'=>$cart->totalPrice,'totalDiscount'=>$cart->totalDiscount,'totalAmount'=>$cart->totalAmount, 'tax'=>$cart->totalTax ,'distributor'=>$dist]);
      return view('shop.shopping-cart2',['products'=>$linepo, 'headerpo'=>$headerpo ,'distributor'=>$dist]);
    }

    public function checkOut(Request $request){
      /*if (!Session::has('cart')){
        //Session::forget('cart');
        return view('shop.shopping-cart',['products'=>null]);
      }
      $oldCart = Session::get('cart');*/
      if ($request->isMethod('get')) $distributorid = $request->old('dist_id');
      else $distributorid = $request->dist_id;
      if($distributorid=='' and $request->isMethod('post'))
      {
        return redirect()->back()->withInput()->withErrors(['dist_id'=>'Distributor is required']);
      }
      $headerpo = PoDraftHeader::where('customer_id','=',Auth::user()->customer_id)->first();
      if($headerpo){
        $linepo  = PoDraftLine::where('po_header_id','=',$headerpo->id)->get();
        if($linepo->count()==0){
            return view('shop.shopping-cart',['products'=>null]);
        }
        $jns= PoDraftLine::where('po_header_id','=',$headerpo->id)->select('jns')->groupBy('jns')->get();
        $jns = $jns->pluck('jns')->toArray();
      }
      $dist = Customer::where('id','=',$distributorid)->first();
      $pharma=false;
      //$cart =  new Cart($oldCart);
      //$jns = array_unique(array_pluck($cart->items,'jns'));
      if(in_array('PHARMA',$jns))
      {
        $pharma=true;
      }
      $alamat = DB::table("customer_sites as c")
          ->select("id", DB::raw("concat(c.address1,
												IF(c.state IS NULL, '', concat(',',c.state)),
												IF(c.city IS NULL, '', concat(',',c.city)),
												IF(c.province IS NULL, '', concat(',',c.province)),
												IF(c.postalcode IS NULL, '', concat(',',c.postalcode))) as address1"))
          ->where('customer_id','=',auth()->user()->customer_id)
          ->where('site_use_code','=','SHIP_TO')
          ->where('status','=','A')
          ->orderBy('c.primary_flag','desc')
          ->get();
      $billto=null;
      $bonus =collect([]);
      if ($dist!='')
      {
        $userdist = User::where('customer_id','=',$distributorid)->first();

        if ($userdist->hasRole('Principal'))
        {
          $billto = DB::table("customer_sites as c")
              ->select("id", DB::raw("concat(c.address1,
    												IF(c.state IS NULL, '', concat(',',c.state)),
    												IF(c.city IS NULL, '', concat(',',c.city)),
    												IF(c.province IS NULL, '', concat(',',c.province)),
    												IF(c.postalcode IS NULL, '', concat(',',c.postalcode))) as address1"))
              ->where('customer_id','=',auth()->user()->customer_id)
              ->where('site_use_code','=','BILL_TO')
              ->where('status','=','A')
              ->orderBy('c.primary_flag','desc')
              ->get();

          $bonus =DB::table('qp_pricing_discount as qpd')
              ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
              ->join('po_draft_lines as pdl','pdl.inventory_item_id','=','qpd.item_id')
              ->join('products as p', 'qpa.product_attr_value','=','p.inventory_item_id')
              ->where('qpd.list_line_type_code', '=','PRG')
              ->where('qpd.price_break_type_code','=','RECURRING')
              ->where('qpd.customer_id','=',Auth::user()->customer->oracle_customer_id)
              ->where('pdl.po_header_id','=',$headerpo->id)
              ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
              ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit'
                        ,'p.title','p.imagePath','p.id as product_id')
              ->selectRaw("(case when pdl.uom = qpa.benefit_uom_code then
       floor(pdl.qty_request/CONVERT(qpd.pricing_attr_value_from, UNSIGNED INTEGER))
      when  pdl.primary_uom = qpa.benefit_uom_code then
      	floor(pdl.qty_request_primary/CONVERT(qpd.pricing_attr_value_from, UNSIGNED INTEGER))
       end) as bonus")
              ->orderBy('pricing_group_sequence','asc')
              ->get();
              //dd($bonus);
        }
        //dd($billto);
      }


      //dd($alamat);
      //return view('shop.checkout',['products'=>$cart->items, 'totalPrice'=>$cart->totalPrice,'totalDiscount'=>$cart->totalDiscount,'totalAmount'=>$cart->totalAmount, 'tax'=>$cart->totalTax ,'addresses'=> $alamat,'billto'=>$billto,'distributor'=>$dist,'pharma'=>$pharma]);
      return view('shop.checkout2',['products'=>$linepo, 'headerpo'=>$headerpo ,'addresses'=> $alamat,'billto'=>$billto,'distributor'=>$dist,'pharma'=>$pharma,'bonus'=>$bonus]);

    }

    //function click add to cart button
    public function getAddToCart(Request $request,$id){
        //$request->hrg = floatval($request->hrg);
        $product = Product::where('id','=',$id)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
        $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$id)->select('uom_code')->get();
        $product->uom=$uom;
        $product->jns=$product->categories()->first()->parent;

        $tax=false;
        $headerpo = PoDraftHeader::firstorCreate(['customer_id'=>Auth::user()->customer_id]);
        if($headerpo){
          $jns = PoDraftLine::where('po_header_id','=',$headerpo->id)->select('jns')->groupBy('jns')->get();
          $jns=$jns->pluck('jns')->toArray();
          array_push($jns,$product->jns);
          if($this->getDistributor($jns)->count()==0)
          {
            return response()->json([
                            'result' => 'errdist',
                            'jns'=>implode(",",$jns),
                          ],200);
          }
        }
        if(Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->Country=="ID"
        and Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->city!="KOTA B A T A M")
        {
            $tax=true;
        }
        //$cart = new Cart($oldCart);
        //$cart->add($product,$id,$request->qty,$request->satuan,floatval($request->hrg),floatval($request->disc) );

        $linepo  = PoDraftLine::where(
                    [['po_header_id','=',$headerpo->id],['product_id','=',$product->id]])->first();
        if($linepo)
        {
          if($linepo->uom!=$request->satuan)
          {
            return response()->json([
                            'result' => 'exist',
                            'totline' => $headerpo->lines()->count(),
                          ],200);
          }else{
            return response()->json([
                            'result' => 'exist',
                            'totline' => $headerpo->lines()->count(),
                          ],200);
          }
        }else{
          if($tax) $taxamount =  round($request->qty*floatval($request->disc)*0.1,0);else $taxamount=0;
          $linepo = PoDraftLine::updateorCreate(
                      ['po_header_id'=>$headerpo->id,'product_id'=>$product->id],
                      ['qty_request'=>$request->qty
                      ,'uom'=>$request->satuan
                      ,'qty_request_primary'=>$product->getConversion($request->satuan)*$request->qty
                      ,'primary_uom'=>$product->satuan_primary
                      ,'conversion_qty'=>$product->getConversion($request->satuan)
                      ,'inventory_item_id'=>$product->inventory_item_id
                      ,'list_price'=>floatval($request->hrg)
                      ,'unit_price'=>floatval($request->disc)
                      ,'amount'=>$request->qty*floatval($request->hrg)
                      ,'discount'=>round($request->qty*floatval($request->hrg),0)-round($request->qty*floatval($request->disc),0)
                      ,'tax_amount'=>$taxamount
                      ,'jns'=>$product->jns
                      ]
            );

        }

        $headerpo->subtotal +=($request->qty*floatval($request->hrg));
        $headerpo->discount+= round($request->qty*floatval($request->hrg),0)-round($request->qty*floatval($request->disc),0);
        if($tax)
        {
            $headerpo->tax =PoDraftLine::where('po_header_id','=',$headerpo->id)->sum('tax_amount');
        }else{
            $headerpo->tax =0;
        }

        $headerpo->Amount= $headerpo->subtotal - $headerpo->discount + $headerpo->tax;
        $headerpo->save();

        //$request->session()->put('cart',$cart);
        return response()->json([
                        'result' => 'success',
                        'totline' => $headerpo->lines()->count(),
                      ],200);
        //return redirect()->route('product.index');

      }

    public function getRemoveItem($id){
      DB::beginTransaction();
      DB::enableQueryLog();
      try{
        $headerpo = PoDraftHeader::where('customer_id','=',Auth::user()->customer_id)->first();
        $removeitem = PoDraftLine::where('po_header_id','=',$headerpo->id)
                    ->where('product_id','=',$id)->delete();
        $newline = PoDraftLine::where('po_header_id','=',$headerpo->id)
                  ->select(DB::raw("sum(discount) as discount")
                        , DB::raw("sum(tax_amount) as tax_amount")
                        , DB::raw("sum(amount) as subtotal")
                        , 'po_header_id'
                        )->groupBy('po_header_id')->first();
        //dd(DB::getQueryLog());
        if($newline){
          $headerpo->Tax = $newline->tax_amount;
          $headerpo->subtotal= $newline->subtotal;
          $headerpo->discount = $newline->discount;
          $headerpo->amount = $newline->subtotal-$newline->discount+$newline->tax_amount;
          $headerpo->save();
        }else{
          $headerpo->Tax = 0;
          $headerpo->subtotal= 0;
          $headerpo->discount = 0;
          $headerpo->amount =0;
          $headerpo->save();
        }
        DB::commit();
        /*$oldCart = Session::has('cart')?Session::get('cart'):null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);

        if (count($cart->items)>0){
          Session::put('cart',$cart);
        }else{
          Session::forget('cart');
          Session::forget('distributor_to');
        }*/
        return redirect()->route('product.shoppingCart');
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      } catch (\Throwable $e) {
          DB::rollback();
          throw $e;
      }
    }

    public function getEditToCart(Request $request,$id){
        $uom = substr($id,-3,3);
        if (isset(Auth::user()->customer_id))
        {
          $vid=Auth::user()->id;
        }else{
          $vid='';
        }

        $price = DB::select("select getProductPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->satuan]);
        $request->hrg = $price[0]->harga;

        $disc = DB::select("select getDiskonPrice ( :cust, :prod, :uom,1 ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->satuan]);
        if($disc){
            $request->disc = $disc[0]->harga;
        }else{$request->disc = $request->hrg;}
        if(Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->Country=="ID"
        and Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->city!="KOTA B A T A M")
        {
            $tax=true;
        }
        DB::beginTransaction();
        try{
          $headerpo = PoDraftHeader::where('customer_id','=',Auth::user()->customer_id)->first();
          $line = PoDraftLine::where('po_header_id','=',$headerpo->id)->where('product_id','=',$request->product)
              ->where('uom','=',$uom)
              ->first();
          if($line)
          {
            $product = Product::where('id','=',$request->product)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
            $konversi =$product->getConversion($request->satuan);
            $line->uom = $request->satuan;
            $line->qty_request = $request->qty;
            $line->conversion_qty = $konversi;
            $line->qty_request_primary = $request->qty * $konversi;
            $line->primary_uom = $product->satuan_primary;
            $line->list_price = $request->hrg;
            $line->unit_price = $request->disc;
            $line->discount = round($request->qty * $request->hrg,0) - round($request->qty *$request->disc,0);
            $line->amount = $request->qty *$request->hrg;
            if($tax) $taxamount = round($request->qty*floatval($request->disc)*0.1,0);else $taxamount=0;
            if($tax) $line->tax_amount = round(0.1 * ($request->qty * $request->disc),0);else $line->tax_amount = 0;
            $line->jns = $product->categories()->first()->parent;
            $line->save();
          }
          $newline = PoDraftLine::where('po_header_id','=',$headerpo->id)
                    ->select(DB::raw("sum(discount) as discount")
                          , DB::raw("sum(tax_amount) as tax_amount")
                          , DB::raw("sum(amount) as subtotal")
                          , 'po_header_id'
                          )->groupBy('po_header_id')->first();
          //dd(DB::getQueryLog());
          $headerpo->Tax = $newline->tax_amount;
          $headerpo->subtotal= $newline->subtotal;
          $headerpo->discount = $newline->discount;
          $headerpo->amount = $newline->subtotal-$newline->discount+$newline->tax_amount;
          $headerpo->save();
          DB::commit();

        }catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }
        /*$oldCart = Session::has('cart')?Session::get('cart'):null;
        $cart = new Cart($oldCart);
        if($uom!=$request->satuan)
        {
          $cart->removeItem($id);
          $product = Product::where('id','=',$request->product)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
          $uomdata = DB::table('mtl_uom_conversions_v')->where('product_id','=',$request->product)->select('uom_code')->get();
          $product->uom=$uomdata;
          //$this->getAddToCart($request,$request->product);
          $cart->add($product,$request->product,$request->qty,$request->satuan,floatval($request->hrg), floatval($request->disc) );
        }else{

          $cart->editItem($id,$request->qty,$request->satuan,$request->id,$request->hrg,$request->disc);

        }
        Session::put('cart',$cart);*/
        return response()->json([
                        'result' => 'success',
                        'price' => number_format($request->hrg,2),
                        'disc' => number_format($request->disc,2),
                        'amount' => number_format($request->hrg*$request->qty,2),
                        'subtotal' => number_format($headerpo->subtotal,2),
                        'disctot' => number_format($headerpo->discount,2),
                        'tax' => number_format($headerpo->Tax,2),
                        'total' => number_format($headerpo->amount,2),
                      ],200);
        //return redirect()->route('product.index');

    }

    public function postOrder(Request $request){
      /*if(!Session::has('cart'))
      {
        return view('shop.shopping-cart',['products'=>null]);
      }
      $oldCart = Session::has('cart')?Session::get('cart'):null;
      $cart = new Cart($oldCart);*/
      $billid=null;
      $shipid=null;
      $orgid=null;
      $warehouseid=null;
      $ordertypeid=null;
      $check_dpl =null;
      $statusso=0;
      if(isset($request->coupon_no))
      {
        $check_dpl = DPLSuggestNo::where('outlet_id',Auth::user()->customer_id)
                      ->where('suggest_no',$request->coupon_no)
                      ->whereNull('notrx')
                      ->count();
        if(!$check_dpl)
        {
          return redirect()->back()
                       ->withErrors(['coupon_no'=>trans('pesan.notmatchdpl')])
                       ->withInput();
        }
        $statusso = -99;
      }
      DB::beginTransaction();
      try {
        $headerpo = PoDraftHeader::where('customer_id','=',Auth::user()->customer_id)->first();
        $linepo = PoDraftLine::where('po_header_id','=',$headerpo->id)->get();
        $distributor = Customer::find($request->dist_id);
        $customer =  Customer::find(auth()->user()->customer_id);
        if($customer->export_flag=="1")
        {
            $currency='USD';
        }else{
            $currency='IDR';
        }

        if($customer->tax_reference!=$request->npwp)
        {
            if(isset($request->npwp) and is_null($customer->tax_reference))
            {
              $customer->tax_reference=$request->npwp;
              $customer->save();
            }elseif(is_null($request->npwp) and isset($customer->tax_reference)){
              $request->npwp = $customer->tax_reference;
            }elseif($request->npwp!=$customer->tax_reference){
              $customer->tax_reference=$request->npwp;
              $customer->save();
            }
        }

        //$tipetax=$customer->customer_category_code;
        if($customer->sites->where('primary_flag','=','Y')->first()->Country=="ID"
        and $customer->sites->where('primary_flag','=','Y')->first()->city!="KOTA B A T A M"){
          $tipetax ="10%";
        }else $tipetax= null;
        $userdistributor = User::where('customer_id','=',$request->dist_id)->first();
        if($userdistributor->hasRole('Principal')) /*jika ke gpl atau YMP maka data oracle harus diisi*/
        {
          Validator::make($request->all(), [
              'alamat' => 'required|numeric',
              'billto' => 'required|numeric',
           ])->validate();
          $oracleshipid = CustomerSite::where('id','=',$request->alamat)->select('site_use_id','org_id','warehouse','order_type_id')->first();
          if($oracleshipid)
          {
            $shipid = $oracleshipid->site_use_id;
            $ordertypeid = $oracleshipid->order_type_id;
            if(is_null($oracleshipid->org_id))
            {
              $orgid = config('constant.org_id');
            }else{
                $orgid = $oracleshipid->org_id;
            }

            if(is_null($oracleshipid->warehouse))
            {
              if($orgid==config('constant.org_id'))
              {
                  $warehouseid = config('constant.warehouseid_YMP');
              }else{$warehouseid = config('constant.warehouseid_GPL');}

            }else{
              $warehouseid = $oracleshipid->warehouse;
            }

          }else{
            return redirect()->back()->withInput()->with('msg', 'Ship to not found');
          }
          $alamatbillto=$request->billto;
          if ($alamatbillto=="")
          {
            //redirect back
              return redirect()->back()->withInput()->with('msg', 'Bill to not found');
          }else{
            $oraclebillid = CustomerSite::where('id','=',$alamatbillto)->select('site_use_id')->first();
            if($oraclebillid)
            {
              $billid = $oraclebillid->site_use_id;
            }else{
                return redirect()->back()->withInput()->with('msg', 'Bill to not found');
            }
          }
        }
      //}
      $thn = date('Y');
      $bln=date('m');
      $tmpnotrx = DB::table('tmp_notrx')->where([
          ['tahun','=',$thn],
          ['bulan','=',$bln],
      ])->select('lastnum')->first();
      if(!$tmpnotrx)
      {
          $tmpnotrx = 0;
      }else{
          $tmpnotrx = (int)$tmpnotrx->lastnum;
      }
      $lastnumber=$tmpnotrx+1;
      $path=null;
      $notrx = 'PO-'.date('Ymd').'-'.$this->getRomanNumerals(date('m')).'-'.str_pad($lastnumber,5,'0',STR_PAD_LEFT) ;
      if($request->hasFile('filepo'))
      {
        $validator = Validator::make($request->all(), [
            'filepo' => 'required|mimes:jpeg,jpg,png,pdf,xls,xlsx,doc,docx,zip|max:10240',
        ])->validate();
        $path = $request->file('filepo')->storeAs(
            'PO', $notrx.".".$request->file('filepo')->getClientOriginalExtension()
        );
      }

       Validator::make($request->all(), [
           'no_order' => 'required|unique:so_headers,customer_po,null,null,customer_id,'.Auth::user()->customer_id.'|max:50',
           'alamat' => 'required',
        ])->validate();
        if(isset($request->coupon_no) and isset($checkdpl))
        {
          $checkdpl->notrx = $notrx;
          $checkdpl->distributor_id= $request->dist_id;
          $checkdpl->save();
        }
      $header= SoHeader::create([
          'distributor_id' => $request->dist_id,
          'customer_id' => auth()->user()->customer_id,
          'cust_ship_to' => $request->alamat,
          'cust_bill_to' => $request->billto,
          'customer_po' => $request->no_order,
          'file_po' => $path,
          'approve' => 0,
          'tgl_order'=> Carbon::now(),
          'currency'=> $currency,
          'payment_term_id'=> null,
          'price_list_id'=> $customer->price_list_id,
          'order_type_id'=> $ordertypeid,
          'oracle_ship_to'=> $shipid,
          'oracle_bill_to'=> $billid,
          'oracle_customer_id' =>$customer->oracle_customer_id,
          'notrx' => $notrx,
          'status' => $statusso,
          'org_id' => $orgid,
          'warehouse' =>$warehouseid,
          'suggest_no'=>$request->coupon_no,
          'npwp'=>$request->npwp
        ]);
      if($header){
        if($tmpnotrx==0){
          DB::table('tmp_notrx')->insert(
              ['tahun' => $thn,'bulan'=>$bln,'lastnum'=>$lastnumber]
          );
        }else{
          DB::table('tmp_notrx')->where([
              ['tahun','=',  $thn],
              ['bulan','=',$bln]
            ])
            ->update(
              ['lastnum'=>$lastnumber]
          );
        }

        $total =0;
        foreach($linepo as $product)
        {
          $item = $product->item;

          /*$konversi = $item->getConversion($product->uom);
          $qtyprimary = $product->qty_request*$konversi;
          if($konversi==0)
          {
            $konversi=1;
            $qtyprimary=$product['qty'];
          }*/
          $tmpprice = $product->list_price;
          if(isset(Auth::user()->customer->oracle_customer_id)){
            $operand_reg =$item->getRateDiskon()->where('pricing_group_sequence','=',1)->first();
            if($operand_reg)
            {
              $disc1 =$operand_reg->operand;
              $amountdisc1=$tmpprice*$disc1/100;
              $tmpprice =$tmpprice-$amountdisc1;
              //echo "tempprice".$tmpprice."<br>";
            }else{
              $disc1 = 0;
              $amountdisc1=0;
            }
            $operand_product = $item->getRateDiskon()->where('pricing_group_sequence','=',2)->first();
            if($operand_product)
            {
              $disc2 =$operand_product->operand;
              $amountdisc2=$tmpprice*$disc2/100;
              $tmpprice =$tmpprice-$amountdisc2;
              //echo "tempprice2".$tmpprice."<br>";
            }else{
              $disc2 =0;
              $amountdisc2=0;
            }

            if($tmpprice!=$product->unit_price)
            {
              DB::rollback();
              $product->unit_price=$tmpprice;
              $product->save();DB::commit();
              return redirect()->back()
                          ->withInput()
                           ->with('msg','Please check price again!');
            }
          }else{
            $disc1=null;
            $disc2=null;
            $amountdisc1=null;
            $amountdisc2=null;
          }
          $amount = ($product->amount - $product->discount);
          $total += ($amount+$product->tax_amount);

          SoLine::Create([
            'header_id'=> $header->id,
            'product_id'=> $product->product_id,
            'uom'=> $product->uom,
            'qty_request' => $product->qty_request,
            'list_price' => $product->list_price,
            'unit_price' =>$product->unit_price,
            'amount' => $product->amount,
            'inventory_item_id'=> $product->inventory_item_id,
            'uom_primary' => $product->primary_uom,
            'qty_request_primary' => $product->qty_request_primary,
            'conversion_qty' => $product->conversion_qty,
            'tax_type'=>$tipetax,
            'tax_amount'=>$product->tax_amount
            ,'disc_reg_percentage'=>$disc1
            ,'disc_product_percentage'=>$disc2
            ,'disc_reg_amount'=>$amountdisc1
            ,'disc_product_amount'=>$amountdisc2
          ]);
        }
        $bonus =DB::table('qp_pricing_discount as qpd')
            ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
            ->join('po_draft_lines as pdl','pdl.inventory_item_id','=','qpd.item_id')
            //->join('products as p', 'qpa.product_attr_value','=','p.inventory_item_id')
            ->where('qpd.list_line_type_code', '=','PRG')
            ->where('qpd.price_break_type_code','=','RECURRING')
            ->where('qpd.customer_id','=',Auth::user()->customer->oracle_customer_id)
            ->where('pdl.po_header_id','=',$headerpo->id)
            ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
            ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit'
                      ,'qpa.list_line_id')
            ->selectRaw("(case when pdl.uom = qpa.benefit_uom_code then
     floor(pdl.qty_request/CONVERT(qpd.pricing_attr_value_from, UNSIGNED INTEGER))
    when  pdl.primary_uom = qpa.benefit_uom_code then
      floor(pdl.qty_request_primary/CONVERT(qpd.pricing_attr_value_from, UNSIGNED INTEGER))
     end) as bonus")
            ->orderBy('pricing_group_sequence','asc')
            ->get();
        if($bonus)
        {
          $bonus = $bonus->where('bonus','>',0);
          foreach( $bonus as $getpromo)
          {
            $productbonus = Product::where('inventory_item_id','=',$getpromo->product_attr_value)->first();
            $konversi =$productbonus->getConversion($getpromo->benefit_uom_code);
            if($konversi==0)
            {
              $konversi=1;
            }
            SoLine::Create([
              'header_id'=> $header->id,
              'product_id'=> $productbonus->id,
              'uom'=> $getpromo->benefit_uom_code,
              'qty_request' => $getpromo->bonus,
              'list_price' => 0,
              'unit_price' =>0,
              'amount' => 0,
              'inventory_item_id'=> $productbonus->inventory_item_id,
              'uom_primary' => $productbonus->satuan_primary,
              'qty_request_primary' => $getpromo->bonus * $konversi,
              'conversion_qty' =>$konversi,
              'tax_type'=>$tipetax,
              'tax_amount'=>0
              ,'bonus_list_line_id'=>$getpromo->list_line_id
            ]);
          }
        }

        $delpoline = DB::table('po_draft_lines')->where('po_header_id','=',$headerpo->id)->delete();
        $delpoheader = DB::table('po_draft_headers')->where('customer_id','=',Auth::user()->customer_id)
                        ->where('id','=',$headerpo->id)->delete();

        if($statusso==-99)/*jika DPL notify ke SPV dan selanjutnya*/
        {
          $suggest_no=$request->coupon_no;

          $updateDPL = DPLSuggestNo::where('suggest_no',$suggest_no)
                                    ->update(array('notrx'=>$notrx,'file_sp'=>$path,'last_update_by'=>Auth::user()->id));

          $notified_users = app('App\Http\Controllers\DPLController')->getArrayNotifiedEmail($suggest_no,'');
    			if(!empty($notified_users)){
    				$data = [
    					'title' => 'Pengajuan DPL',
    					'message' => 'Pengajuan DPL #'.$request->coupon_no,
    					'id' => $suggest_no,
    					'href' => route('dpl.readNotifApproval'),
    					'mail' => [
    						'greeting'=>'Pengajuan DPL #'.$request->coupon_no,
    						'content'=> 'Pengajuan DPL #'.$request->coupon_no,
    					]
    				];
    				foreach ($notified_users as $key => $email) {
    					foreach ($email as $key2 => $mail) {
                $data['email'] = $mail;
    						$apps_user = User::where('email',$mail)->first();
                if(!empty($apps_user))
    						  $apps_user->notify(new PushNotif($data));
    					}
    				}
    			}

        }else{
          /*$data= ['distributor'=>$distributor->id,'user'=>$userdistributor->id,'so_header_id'=>$header->id,'customer'=>auth()->user()->customer_id];
          //$data= ['distributor'=>$oldDisttributor['id'],'user'=>$u->id,'so_header_id'=>1,'customer'=>auth()->user()->customer_id];
          $userdistributor->notify(new NewPurchaseOrder($data));*/
          $newlines = DB::table('so_Lines_v')->where('header_id','=',$header->id)->get();
          $data = [
            'title'=> 'New PO',
    				'message' => 'New PO '.$notrx.' From '.auth()->user()->customer->customer_name,
    				'id' => $header->id,
    				'href' => route('order.notifnewpo')
            ,'mail' => [ 'greeting'=> "",
                            'content' => "",
                    		    'markdown'=> "emails.orders.create",
                    		    'attribute'=>	["so_headers"=>$header
                                          ,"lines"=>$newlines
                                          ,'total'=>$total
                                          ,'customer'=>auth()->user()->customer->customer_name]
                        ]
    			];
          $data['email'] = $userdistributor->email;
          $userdistributor->notify(new PushNotif($data));
        }
        //notification to distributor

        Mail::to(Auth::user())->send(new CreateNewPo($header));
      }
    } catch (\Exception $e) {
      DB::rollback();
      throw $e;
    } catch (\Throwable $e) {
        DB::rollback();
        throw $e;
    }
    DB::commit();

      //lepas session
      /*Session::forget('cart');
      Session::forget('distributor_to');*/
      return redirect()->route('order.listPO');
    }

    public function getRomanNumerals($decimalInteger)
    {
     $n = intval($decimalInteger);
     $res = '';

     $roman_numerals = array(
        'M'  => 1000,
        'CM' => 900,
        'D'  => 500,
        'CD' => 400,
        'C'  => 100,
        'XC' => 90,
        'L'  => 50,
        'XL' => 40,
        'X'  => 10,
        'IX' => 9,
        'V'  => 5,
        'IV' => 4,
        'I'  => 1);

     foreach ($roman_numerals as $roman => $numeral)
     {
      $matches = intval($n / $numeral);
      $res .= str_repeat($roman, $matches);
      $n = $n % $numeral;
     }

     return $res;
    }

    public function listParetoProduct()
    {
      $products = Product::where('pareto','=',1)->get();
      return view('admin.product.pareto',['products' => $products,'menu'=>'pareto']);
    }

    public function getRateDiskon( $groupid,$productid,$customerid)
    {
      $diskon = DB::table('list_discount_v')
              ->where([
                ['pricing_group_sequence','=',$groupid]
                ,['product_id','=',$productid]
                ,['customer_id','=',$customerid]
              ])->select('pricing_group_sequence',DB::raw("sum(operand) as operand"))
              ->groupBy('pricing_group_sequence')
              ->get();
      dd($diskon->where('pricing_group_sequence','=',1)->first());
      return $diskon;
    }


}
