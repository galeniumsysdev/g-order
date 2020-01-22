<?php

namespace App\Http\Controllers;

use App\OutletProducts;
use App\OutletStock;
use App\Product;
use App\Customer;
use App\csvproduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;
use Carbon\Carbon;

use Auth;
use Excel;
use PHPExcel_Shared_Date;

class OutletProductController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function importProduct()
  {
  	return view('admin.outlet.outletImportProduct',array('data'=>''));
  }

  public function importProductView(Request $request)
  {
  	if($request->file_import){
	    $file = $request->file_import;

	    $data = Excel::load($file, function($reader){})->get();
      foreach ($data as $key => $prod) {
        $check = OutletProducts::where('title', strtoupper($prod['nama_barang']))
                                ->where('outlet_products.outlet_id',Auth::user()->customer_id)
                                ->count();
        if($check)
          $data[$key]['exist'] = '<span class="text-danger duplicate">Duplicate</span>';
        else
          $data[$key]['exist'] = '';
      }
	  }

  	return view('admin.outlet.outletImportProduct', array('data'=>$data));
  }

  public function importProductProcess(Request $request)
  {
  	$data = json_decode($request->data);

  	$product = array();
  	foreach ($data as $key => $new) {
  		$product[$key]['id'] = Uuid::generate();
      $product[$key]['outlet_id'] = Auth::user()->customer_id;
  		$product[$key]['title'] = strtoupper($new->nama_barang);
  		$product[$key]['unit'] = strtoupper($new->satuan);
  		$product[$key]['price'] = (intval($new->price)) ? intval($new->price) : 0;
      $product[$key]['generic'] = $new->generik;
  		$product[$key]['enabled_flag'] = 'Y';
  		$product[$key]['created_at'] = date('Y-m-d H:i:s', time());
  		$product[$key]['updated_at'] = date('Y-m-d H:i:s', time());
      $product[$key]['created_by'] = Auth::user()->id;
      $product[$key]['last_update_by'] = Auth::user()->id;
  	}
  	$insert_product = OutletProducts::insert($product);

  	return redirect('/outlet/product/import')->with('msg','Product imported successfully.');
  }

  public function downloadTemplateProduct()
  {
  	return redirect('/file/template_upload_new_other_product.xlsx');
  }

  public function downloadTemplateStock()
  {
  	return Excel::create('Product Stock '.date('Ymd His'), function($excel){
  		$excel->setTitle('Product Stock '.date('Ymd His'))
  					->setCreator(Auth::user()->name)
  					->setCompany('PT. Galenium Pharmasia Laboratories')
  					->sheet('Product Stock '.date('Ymd His'), function($sheet){
  						$sheet->row(1, array('ID','Nama Barang','Stock','Satuan','Kelompok','Batch','Exp. Date(Cth:2017-01-31)'));
  						$sheet->setColumnFormat(array('D'=>'@','G' => 'yyyy-mm-dd'));

  						$productsOutlet = OutletProducts::select('outlet_products.id as op_id','title','unit',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'),'batch','exp_date')
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id)
  													->groupBy('op_id','unit','title','flag','batch','exp_date');
              $productsAll = Product::select('products.id as op_id','title','products.satuan_primary as unit',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'),'batch','exp_date')
                                      ->leftjoin('outlet_stock as os',function($join)
                                        {
                                          $join->on('os.product_id','=','products.id');
                                          $join->on('os.outlet_id','=',DB::raw("'".Auth::user()->customer_id."'"));
                                        })
                                      ->join('category_products as cp','cp.product_id','products.id')
                                      ->join('categories as c','c.flex_value','cp.flex_value')
                                      ->where('c.parent','PHARMA')
                                      //->where('os.outlet_id',Auth::user()->customer_id)
                                      ->groupBy('unit','op_id','title','flag','batch','exp_date')
                                      ->union($productsOutlet)
                                      ->orderBy('title')
                                      ->get();

  						foreach ($productsAll as $key => $prod) {
  							$sheet->row($key+2, array($prod->op_id,
  																				$prod->title,
  																				($prod->product_qty ? $prod->product_qty : 0),
                                          $prod->unit,
                                          $prod->flag,
  																				$prod->batch,
                                          (!is_null($prod->exp_date)?PHPExcel_Shared_Date::PHPToExcel(strtotime($prod->exp_date)):null)
                                        ));
  						}
  					});
  	})->download('xlsx');
  }

  public function importProductStock()
  {
  	return view('admin.outlet.outletImportProductStock',array('data'=>''));
  }

  public function importProductStockView(Request $request)
  {
  	if($request->file_import){
	    $file = $request->file_import;

	    $data = Excel::load($file, function($reader){
        $reader->formatDates(true, 'Y-m-d');
      })->get();
	    foreach ($data as $key => $value) {
        if(isset($value->id)){
    	    	$last_stock = OutletStock::where('product_id',$value->id)->where('outlet_id',Auth::user()->customer_id);
            if(is_null($value->batch)){
              $expdate= null;
              $last_stock =$last_stock->whereNull('batch')->sum('qty');
            }else {
              if($last_stock->where('batch',$value->batch)->count()>0) $expdate= $last_stock->where('batch',$value->batch)->first()->exp_date;else $expdate=null;
              $last_stock =$last_stock->where('batch',$value->batch)->sum('qty');
            }
    	    	$data[$key]['last_stock'] = $last_stock;
            $data[$key]['last_expdate'] = $expdate;

		        }else return redirect()->route('outlet.importProductStock')->with('msg','ID must be exists');
	    }
	  }

  	return view('admin.outlet.outletImportProductStock', array('data'=>$data));
  }

  public function importProductStockProcess(Request $request)
  {
  	$data = json_decode($request->data);
  	$stock = array();
  	$idx = 0;
  	foreach ($data as $key => $prod) {
      if($prod->last_stock!=0){
      $stock[$idx]['trx_date'] = date('Y-m-d', time());
  		$stock[$idx]['product_id'] = $prod->id;
      $stock[$idx]['outlet_id'] = Auth::user()->customer_id;
  		$stock[$idx]['event'] = 'adjust';
  		$stock[$idx]['qty'] = ($prod->last_stock > 0) ? '-'.$prod->last_stock : $prod->last_stock;
  		$stock[$idx]['batch'] = $prod->batch;
      //$stock[$idx]['Exp_date'] = isset($prod->{'exp._datecth2017_01_31'}->date)?$prod->{'exp._datecth2017_01_31'}->date:null;
      $stock[$idx]['Exp_date'] = isset($prod->last_expdate)?$prod->last_expdate:null;
  		$stock[$idx]['created_at'] = date('Y-m-d H:i:s', time());
  		$stock[$idx]['updated_at'] = date('Y-m-d H:i:s', time());
      $stock[$idx]['created_by'] =Auth::user()->id;
      $stock[$idx]['last_update_by'] =Auth::user()->id;
  		$idx++;
      }
      $stock[$idx]['trx_date'] = date('Y-m-d', time());
  		$stock[$idx]['product_id'] = $prod->id;
      $stock[$idx]['outlet_id'] = Auth::user()->customer_id;
  		$stock[$idx]['event'] = 'add_upload';
  		$stock[$idx]['qty'] = $prod->stock;
  		$stock[$idx]['batch'] = $prod->batch;
      $stock[$idx]['Exp_date'] =isset($prod->{'exp._datecth2017_01_31'})?$prod->{'exp._datecth2017_01_31'}:null;
  		$stock[$idx]['created_at'] = date('Y-m-d H:i:s', time());
  		$stock[$idx]['updated_at'] = date('Y-m-d H:i:s', time());
      $stock[$idx]['created_by'] =Auth::user()->id;
      $stock[$idx]['last_update_by'] =Auth::user()->id;
  		$idx++;
  	}
      //dd($stock);
  	$insert_stock = OutletStock::insert($stock);

  	return redirect('/outlet/product/import/stock')->with('msg','Stock imported successfully.');
  }

	public function getListProductStock()
  {
  	$stockOutlet = OutletProducts::select('outlet_products.id as op_id','outlet_products.unit','title',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id)
  													->groupBy('op_id','unit','title','flag');
    $stockAll = Product::select('products.id as op_id','products.satuan_primary as unit','title',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'))
                              ->leftjoin('outlet_stock as os',function($join)
                                {
                                  $join->on('os.product_id','=','products.id');
                                  $join->on('os.outlet_id','=',DB::raw("'".Auth::user()->customer_id."'"));
                                })
                              ->join('category_products as cp','cp.product_id','products.id')
                              ->join('categories as c','c.flex_value','cp.flex_value')
                              ->where('c.parent','PHARMA')
                            //  ->where('os.outlet_id',Auth::user()->customer_id)
                              ->groupBy('unit','op_id','title','flag')
                              ->union($stockOutlet)
                              ->orderBy('title')
                              ->get();

  	return response()->json($stockAll);
  }

  public function listProductStock()
  {
  	$stockOutlet = OutletProducts::select('outlet_products.unit','outlet_products.id as op_id','title','generic',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id)
  													->groupBy('unit','op_id','title','flag','generic');
    $stockAll = Product::select('products.satuan_primary as unit','products.id as op_id','title','products.long_description as generic',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'))
                            ->leftjoin('outlet_stock as os',function($join)
                              {
                                $join->on('os.product_id','=','products.id');
                                $join->on('os.outlet_id','=',DB::raw("'".Auth::user()->customer_id."'"));
                              })
                            ->join('category_products as cp','cp.product_id','products.id')
                            ->join('categories as c','c.flex_value','cp.flex_value')
                            ->where('c.parent','PHARMA')
                            ->groupBy('unit','op_id','title','flag','long_description')
                            ->union($stockOutlet)
                            ->orderBy('title')
  													->get();

  	foreach ($stockAll as $key => $prod) {
      $flag = ($prod->flag == 'galenium') ? 'g' : '';
  		$stockAll[$key]->stock = '<a href="'.route('outlet.detailProductStock',array('product_id'=>$prod->op_id,'flag'=>$flag)).'">'.floatval($prod->product_qty).' '.$prod->unit.'</a>';
  	}
  	return view('admin.outlet.listProductStock', array('stock'=>$stockAll));
  }

  public function detailProductStock($product_id, $flag = '')
  {
    if($flag != 'g'){
      $header = OutletProducts::select('outlet_products.unit','outlet_products.id as p_id','title',DB::raw('sum(qty) as product_qty'))
                              ->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                              ->where('outlet_products.id',$product_id)
                              ->where('os.outlet_id',Auth::user()->customer_id)
                              ->groupBy('unit','p_id','title')
                              ->first();

    	$stock = OutletStock::select('outlet_products.unit','outlet_products.title','outlet_stock.*')
    												->leftjoin('outlet_products','outlet_products.id','outlet_stock.product_id')
    												->where('product_id',$product_id)
                            ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
    												->orderBy('outlet_stock.trx_date','desc')
    												->get();
    }
    else{
      $header = Product::select('products.satuan_primary as unit','products.id as p_id','title',DB::raw('sum(qty) as product_qty'))
                              ->leftjoin('outlet_stock as os','os.product_id','products.id')
                              ->join('category_products as cp','cp.product_id','products.id')
                              ->join('categories as c','c.flex_value','cp.flex_value')
                              ->where('c.parent','PHARMA')
                              ->where('products.id',$product_id)
                              ->where('os.outlet_id',Auth::user()->customer_id)
                              ->groupBy('unit','p_id','title')
                              ->first();

      $stock = OutletStock::select('products.satuan_primary as unit','products.title','outlet_stock.*')
                            ->leftjoin('products','products.id','outlet_stock.product_id')
                            ->where('product_id',$product_id)
                            ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                            ->orderBy('outlet_stock.trx_date','desc')
                            ->get();
    }

  	$trx = array();
  	$count = 0;
  	$idx_adj = -1;
  	$title = $header['title'];
  	$last_stock = (($header['product_qty']) ? $header['product_qty'] : 0).' '.$header['unit'];
		foreach ($stock as $key => $list) {
			if($list->qty != 0 && $list->event != 'adjust'){
				if($list->event == 'trx_in'){
					$trx[$count]['class'] = 'bg-success';
					$trx[$count]['event'] = 'Add';
					$trx[$count]['qty'] = $list->qty.' '.$list->unit;
				}
				elseif($list->event == 'trx_out'){
					$trx[$count]['class'] = 'bg-danger';
					$trx[$count]['event'] = 'Out';
					$trx[$count]['qty'] = $list->qty.' '.$list->unit;
				}
				elseif($list->event == 'add_upload'){
					$trx[$count]['class'] = 'bg-info';
					$trx[$count]['event'] = ($idx_adj == -1) ? '<strong>Adjustment</strong>' : 'Adjustment';
					$trx[$count]['qty'] = ($idx_adj == -1) ? '<strong>'.$list->qty.' '.$list->unit.'</strong>' : $list->qty.' '.$list->unit;
					$idx_adj = 0;
				}
				$trx[$count]['batch'] = $list->batch;
				$trx[$count]['trx_date'] = $list->trx_date;
				$count++;
			}
		}

  	return view('admin.outlet.detailProductStock', array('title'=>$title, 'last_stock'=>$last_stock, 'stock'=>$trx));
  }

  public function formProduct($id = '')
  {
    if($id)
      $product = OutletProducts::where('id',$id)->first();
    else{
      $product = new \stdClass;
      $product->id = '';
      $product->title = '';
      $product->unit = '';
      $product->price = 0;
      $product->generic = '';
    }

    return view('admin.outlet.outletProductForm', array('product'=>$product));
  }

  public function submitProduct(Request $request)
  {
    $id = $request->id;
    $product_name = strtoupper($request->product_name);
    $unit = strtoupper($request->product_unit);
    $price = $request->product_price;
    $generic = $request->product_generic;

    if(!$id){
      $check = OutletProducts::where('title',$product_name)
                              ->where('outlet_products.outlet_id',Auth::user()->customer_id)
                              ->count();

      if($check)
        return redirect()->back()->withInput()->with('msg','<span class="text-danger">Product is already exist.</span>');
      else
        $product = new OutletProducts;
        $product->id = Uuid::generate();
        $product->outlet_id = Auth::user()->customer_id;
        $product->enabled_flag = 'Y';
    }
    else{
      $product = OutletProducts::find($id);
    }
    $product->title = $product_name;
    $product->unit = $unit;
    $product->price = $price;
    $product->generic = $generic;
    $save = $product->save();

    if($save){
      if(!$id)
        return redirect()->back()->with('msg','Product saved successfully.');
      else
        return redirect()->back()->with('msg','Product edited successfully.');
    }
  }

  public function deleteProduct($id)
  {
    $delete = OutletProducts::where('id',$id)->update(array('enabled_flag'=>0,'last_update_by'=>Auth::user()->id));

    if($delete){
      return redirect()->back()->with('msg','Product deleted successfully.');
    }
  }

  public function outletTrx()
  {
  	return view('admin.outlet.outletTrx');
  }

  public function outletTrxList()
  {
    $start_date = ($_GET && $_GET['start_date']) ? date('Y-m-d',strtotime($_GET['start_date'])) : date('Y-m-d',strtotime('-1 month'));
    $end_date = ($_GET && $_GET['end_date']) ? date('Y-m-d',strtotime($_GET['end_date'])) : date('Y-m-d');
    $product_name = ($_GET && $_GET['product_name']) ? trim($_GET['product_name']) : '';
    $generic = ($_GET && $_GET['generic']) ? trim($_GET['generic']) : '';

  	$stockOutlet = OutletStock::select('title','outlet_stock.*','outlet_products.generic as generic')
  											->leftjoin('outlet_products','outlet_products.id','outlet_stock.product_id')
                        ->where('outlet_products.enabled_flag','Y')
                        ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                        ->whereBetween('outlet_stock.trx_date',array($start_date,$end_date))
                        ->where('title','LIKE','%'.$product_name.'%')
                        ->where('generic','LIKE','%'.$generic.'%');

    $stockAll = OutletStock::select('title','outlet_stock.*','long_description as generic')
                        ->leftjoin('products','products.id','outlet_stock.product_id')
                        ->join('category_products as cp','cp.product_id','products.id')
                        ->join('categories as c','c.flex_value','cp.flex_value')
                        ->where('c.parent','PHARMA')
                        ->where('products.Enabled_Flag','Y')
                        ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                        ->whereBetween('outlet_stock.trx_date',array($start_date,$end_date))
                        ->where('title','LIKE','%'.$product_name.'%')
                        ->where('long_description','LIKE','%'.$generic.'%')
                        ->union($stockOutlet)
                        ->orderBy('trx_date','desc')
                        ->orderBy('created_at','desc')
  											->get();

  	$trx = array();
  	$count = 0;
  	foreach ($stockAll as $key => $list) {
  		if($list->qty != 0 && $list->event != 'adjust'){
  			if($list->event == 'trx_in'){
  				$trx[$count]['class'] = 'bg-success';
	  			$trx[$count]['event'] = 'Add';
  			}
	  		elseif($list->event == 'trx_out'){
	  			$trx[$count]['class'] = 'bg-danger';
	  			$trx[$count]['event'] = 'Out';
	  		}
	  		elseif($list->event == 'add_upload'){
	  			$trx[$count]['class'] = 'bg-info';
	  			$trx[$count]['event'] = 'Adjustment';
	  		}
	  		$trx[$count]['title'] = $list->title;
        $trx[$count]['generic'] = $list->generic;
	  		$trx[$count]['qty'] = $list->qty.' '.$list->unit;
	  		$trx[$count]['batch'] = $list->batch;
        $trx[$count]['deliveryorder_no'] = $list->deliveryorder_no;
        $trx[$count]['exp_date'] = $list->exp_date;
	  		$trx[$count]['trx_date'] = $list->trx_date;
	  		$count++;
	  	}
  	}
  	return view('admin.outlet.outletTrxList',array('data'=>$trx));
  }

  public function outletTrxInProcess(Request $request)
  {
    if(!is_null($request->batch_no_in) and is_null($request->exp_date_in))
    {
      return redirect()->back()->withInput()->with('msg','Expired date harus disi jika batch number diisi');
    }
  	$instock = new OutletStock;
    $instock->trx_date = date('Y-m-d',strtotime($request->trx_in_date));
  	$instock->product_id = $request->product_code_in;
    $instock->outlet_id = Auth::user()->customer_id;
  	$instock->event = 'trx_in';
  	$instock->qty = $request->qty_in;
    $instock->batch = $request->batch_no_in;
    $instock->exp_date =is_null($request->exp_date_in)?null:date('Y-m-d',strtotime($request->exp_date_in));
    $instock->deliveryorder_no = $request->delivery_no_in;
  	$instock->save();

  	return redirect()->route('outlet.trx')->with('msg','Transaction In has been done successfully.');
  }

  public function outletTrxOutProcess(Request $request)
  {
    /*check onhand*/
    $datastock=$this->getStockBatchProduct($request);
    if(is_null($request->batch_no_out))
      $datastock = $datastock->where('batch','=',null);
    if($datastock->count()>0)
    {
      $tglexpired = $datastock->first()->exp_date;
      $jmlstock = $datastock->first()->product_qty;
    }else{
      return redirect()->back()->withInput()->with('err','Transaksi gagal disimpan.Stock tidak mencukupi');
    }
    if($jmlstock < $request->qty_out)
    {
      return redirect()->back()->withInput()->with('err','Transaksi keluar gagal disimpan karena stock tidak mencukupi. Sisa Stock ='.$jmlstock);
    }
  	$outstock = new OutletStock;
    $outstock->trx_date = date('Y-m-d',strtotime($request->trx_out_date));
  	$outstock->product_id = $request->product_code_out;
    $outstock->outlet_id = Auth::user()->customer_id;
  	$outstock->event = 'trx_out';
  	$outstock->qty = '-'.$request->qty_out;
    $outstock->batch = $request->batch_no_out;
    if(isset($request->batch_no_out)) $outstock->Exp_date = $datastock->first()->exp_date;
  	$outstock->save();

  	return redirect()->route('outlet.trx')->with('msg','Transaction Out has been done successfully.');
  }

  public function downloadProductStock()
  {
    return view('admin.outlet.outletStockDownload',array('data'=>''));
  }

  public function downloadProductStockView(Request $request)
  {
    $data['start_date'] = date('Y-m-d',strtotime($request->start_date));
    $data['end_date'] = date('Y-m-d',strtotime($request->end_date));
    $data['outlet_name'] = $request->outlet_name;
    $data['province'] = $request->province;
    $data['area'] = $request->area;

    $stockOutlet = OutletStock::select('title','outlet_stock.product_id','outlet.customer_name as outlet_name','outlet_products.price as price','batch')
                        ->join('outlet_products',function($join)
                          {
                            $join->on('outlet_products.id','=','outlet_stock.product_id');
                            $join->on('outlet_products.outlet_id','=','outlet_stock.outlet_id');
                          })
                        ->join('customers as outlet','outlet.id','outlet_stock.outlet_id')
                        ->join('customer_sites as cs','cs.customer_id','outlet.id')
                        ->where('outlet_products.enabled_flag','Y')
                        ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                        ->whereBetween('outlet_stock.trx_date',array($data['start_date'],$data['end_date']))
                        ->groupby('title','product_id','outlet_name','price','batch');

    if($data['outlet_name'])
      $stockOutlet = $stockOutlet->where('outlet.customer_name',$data['outlet_name']);

    if($data['province'])
      $stockOutlet = $stockOutlet->where('province',$data['province']);

    if($data['area'])
      $stockOutlet = $stockOutlet->where('city',$data['area']);

    if($request->product_name)
      $stockOutlet = $stockOutlet->where('title','LIKE','%'.$request->product_name.'%');

    $stockAll = OutletStock::select('title','outlet_stock.product_id','outlet.customer_name as outlet_name','qp.operand as price','batch')
                        ->join('products','products.id','outlet_stock.product_id')
                        ->join('qp_list_lines_v as qp',function($join){
                          $join->on('qp.product_attr_value','products.inventory_item_id');
                          $join->where('qp.list_header_id',config('constant.price_hna'));
                        })
                        ->join('customers as outlet','outlet.id','outlet_stock.outlet_id')
                        ->join('customer_sites as cs','cs.customer_id','outlet.id')
                        ->join('category_products as cp','cp.product_id','products.id')
                        ->join('categories as c','c.flex_value','cp.flex_value')
                        ->where('c.parent','PHARMA')
                        ->where('products.Enabled_Flag','Y')
                        ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                        ->whereBetween('outlet_stock.trx_date',array($data['start_date'],$data['end_date']))
                        ->groupby('title','product_id','outlet_name','price','batch');

    if($data['outlet_name'])
      $stockAll = $stockAll->where('outlet.customer_name',$data['outlet_name']);

    if($data['province'])
      $stockAll = $stockAll->where('province',$data['province']);

    if($data['area'])
      $stockAll = $stockAll->where('city',$data['area']);

    if($request->product_name)
      $stockAll = $stockAll->where('title','LIKE','%'.$request->product_name.'%');

    $stockAll = $stockAll->union($stockOutlet)
                        ->orderBy('title','asc')
                        ->get();

    $data['table'] = array();

    foreach ($stockAll as $key => $prod) {
      $begin = OutletStock::where('product_id',$prod->product_id)
                              ->where('batch',$prod->batch)
                              ->whereDate('trx_date','<=',$data['start_date'])
                              ->sum('qty');
      $end = OutletStock::where('product_id',$prod->product_id)
                              ->where('batch',$prod->batch)
                              ->whereDate('trx_date','<=',$data['end_date'])
                              ->sum('qty');
      $in = OutletStock::where('product_id',$prod->product_id)
                              ->where('batch',$prod->batch)
                              ->whereDate('trx_date','>=',$data['start_date'])
                              ->whereDate('trx_date','<=',$data['end_date'])
                              ->where('qty','>',0)
                              ->sum('qty');
      $out = OutletStock::where('product_id',$prod->product_id)
                              ->where('batch',$prod->batch)
                              ->whereDate('trx_date','>=',$data['start_date'])
                              ->whereDate('trx_date','<=',$data['end_date'])
                              ->where('qty','<',0)
                              ->sum('qty');

      $data['table'][$key]['outlet_name'] = $prod->outlet_name;
      $data['table'][$key]['title'] = $prod->title;
      $data['table'][$key]['batch'] = $prod->batch;
      $data['table'][$key]['begin'] = $begin;
      $data['table'][$key]['in'] = $in;
      $data['table'][$key]['out'] = $out;
      $data['table'][$key]['end'] = $end;
      $data['table'][$key]['unit_price'] = $prod->price;
      $data['table'][$key]['value_price'] = $end*$prod->price;
    }

    return view('admin.outlet.outletStockDownload',array('data'=>$data));
  }

  public function downloadProductStockProcess(Request $request)
  {
    $data['start_date'] = date('Y-m-d',strtotime($request->start_date));
    $data['end_date'] = date('Y-m-d',strtotime($request->end_date));
    $data['outlet_name'] = $request->outlet_name;
    $data['province'] = $request->province;
    $data['area'] = $request->area;
    return Excel::create('Report Stock', function($excel) use($data){
      $excel->setTitle('Report Stock')
            ->setCreator(Auth::user()->name)
            ->sheet('Report Stock', function($sheet) use($data){
              $sheet->setWidth(array(
                'A' => 35,
                'B' => 45,
                'C' => 25,
                'D' => 8,
                'E' => 8,
                'F' => 8,
                'G' => 8,
                'H' => 15,
                'I' => 15,
              ));

              $sheet->row(1, array('STOCK OUTLET'));
              $sheet->row(2, array(date('d F Y',strtotime($data['start_date'])).' - '.date('d F Y',strtotime($data['end_date']))));
              $sheet->row(4, array('NAMA OUTLET','NAMA BARANG','BATCH','JUMLAH','','','','UNIT PRICE','VALUE'));
              $sheet->row(5, array('','','','Beg','In','Out','End'));
              $sheet->mergeCells('D4:G4');
              $sheet->mergeCells('A4:A5');
              $sheet->mergeCells('B4:B5');
              $sheet->mergeCells('C4:C5');
              $sheet->mergeCells('H4:H5');
              $sheet->mergeCells('I4:I5');

              $sheet->cell('A1', function($cell) {
                $cell->setFont(array(
                    'size'       => '20',
                    'bold'       =>  true
                ));
              });

              $sheet->cell('A2', function($cell) {
                $cell->setFont(array(
                    'size'       => '16',
                    'bold'       =>  true
                ));
              });

              $sheet->cells('A4:I5', function($cells) {
                  $cells->setAlignment('center');
                  $cells->setValignment('center');
                  $cells->setFontWeight('bold');
              });

              $stockOutlet = OutletStock::select('title','outlet_stock.product_id','outlet.customer_name as outlet_name','outlet_products.price as price','batch')
                                  ->join('outlet_products',function($join)
                                    {
                                      $join->on('outlet_products.id','=','outlet_stock.product_id');
                                      $join->on('outlet_products.outlet_id','=','outlet_stock.outlet_id');
                                    })
                                  ->join('customers as outlet','outlet.id','outlet_stock.outlet_id')
                                  ->join('customer_sites as cs','cs.customer_id','outlet.id')
                                  ->where('outlet_products.enabled_flag','Y')
                                  ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                                  ->whereBetween('outlet_stock.trx_date',array($data['start_date'],$data['end_date']))
                                  ->groupby('title','product_id','outlet_name','price','batch');

              if($data['outlet_name'])
                $stockOutlet = $stockOutlet->where('outlet.customer_name',$data['outlet_name']);

              if($data['province'])
                $stockOutlet = $stockOutlet->where('province',$data['province']);

              if($data['area'])
                $stockOutlet = $stockOutlet->where('city',$data['area']);

              $stockAll = OutletStock::select('title','outlet_stock.product_id','outlet.customer_name as outlet_name','qp.operand as price','batch')
                                  ->join('products','products.id','outlet_stock.product_id')
                                  ->join('qp_list_lines_v as qp',function($join){
                                    $join->on('qp.product_attr_value','products.inventory_item_id');
                                    $join->where('qp.list_header_id',config('constant.price_hna'));
                                  })
                                  ->join('customers as outlet','outlet.id','outlet_stock.outlet_id')
                                  ->join('customer_sites as cs','cs.customer_id','outlet.id')
                                  ->join('category_products as cp','cp.product_id','products.id')
                                  ->join('categories as c','c.flex_value','cp.flex_value')
                                  ->where('c.parent','PHARMA')
                                  ->where('products.Enabled_Flag','Y')
                                  ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                                  ->whereBetween('outlet_stock.trx_date',array($data['start_date'],$data['end_date']))
                                  ->groupby('title','product_id','outlet_name','price','batch');

              if($data['outlet_name'])
                $stockAll = $stockAll->where('outlet.customer_name',$data['outlet_name']);

              if($data['province'])
                $stockAll = $stockAll->where('province',$data['province']);

              if($data['area'])
                $stockAll = $stockAll->where('city',$data['area']);

              $stockAll = $stockAll->union($stockOutlet)
                                  ->orderBy('title','asc')
                                  ->get();

              foreach ($stockAll as $key => $prod) {
                $begin = OutletStock::where('product_id',$prod->product_id)
                                        ->where('batch',$prod->batch)
                                        ->whereDate('trx_date','<=',$data['start_date'])
                                        ->sum('qty');
                $end = OutletStock::where('product_id',$prod->product_id)
                                        ->where('batch',$prod->batch)
                                        ->whereDate('trx_date','<=',$data['end_date'])
                                        ->sum('qty');
                $in = OutletStock::where('product_id',$prod->product_id)
                                        ->where('batch',$prod->batch)
                                        ->whereDate('trx_date','>=',$data['start_date'])
                                        ->whereDate('trx_date','<=',$data['end_date'])
                                        ->where('qty','>',0)
                                        ->sum('qty');
                $out = OutletStock::where('product_id',$prod->product_id)
                                        ->where('batch',$prod->batch)
                                        ->whereDate('trx_date','>=',$data['start_date'])
                                        ->whereDate('trx_date','<=',$data['end_date'])
                                        ->where('qty','<',0)
                                        ->sum('qty');

                $sheet->row($key+6, array($prod->outlet_name,
                                          $prod->title,
                                          $prod->batch,
                                          $begin,
                                          $in,
                                          $out,
                                          $end,
                                          $prod->price,
                                          $end*$prod->price
                                          ));
              }
            });
    })->download('xlsx');
  }

  public function getListOutlet()
  {
    $outlet = Customer::all();
    return response()->json($outlet);
  }

  public function getStockBatchProduct(Request $request)
  {
    $stockOutlet = OutletProducts::select('outlet_products.id as op_id','outlet_products.unit','title',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'),'batch','exp_date')
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id);

    $stockOutlet=$stockOutlet->where('os.product_id',$request->product_code_out);
    if($request->batch_no_out) $stockOutlet=$stockOutlet->where('os.batch',$request->batch_no_out);
    //else $stockOutlet=$stockOutlet->whereNull('os.batch',$request->batch_no_out);
    $stockOutlet=$stockOutlet->groupBy('op_id','unit','title','flag','batch','exp_date')
                ->havingraw("sum(qty)>0");

    $stockAll = Product::select('products.id as op_id','products.satuan_primary as unit','title',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'),'batch','exp_date')
                              ->leftjoin('outlet_stock as os',function($join)
                                {
                                  $join->on('os.product_id','=','products.id');
                                  $join->on('os.outlet_id','=',DB::raw("'".Auth::user()->customer_id."'"));
                                })
                              ->join('category_products as cp','cp.product_id','products.id')
                              ->join('categories as c','c.flex_value','cp.flex_value')
                              ->where('c.parent','PHARMA')
                              ->where('os.product_id',$request->product_code_out);

    if($request->batch_no_out) $stockAll=$stockAll->where('os.batch',$request->batch_no_out);
    //else $stockOutlet=$stockOutlet->whereNull('os.batch',$request->batch_no_out);
    $stockAll=$stockAll->groupBy('unit','op_id','title','flag','batch','exp_date')->havingraw("sum(qty)>0")
                              ->union($stockOutlet)
                              ->orderBy('title')
                              ->orderBy('exp_date')
                              ->orderBy('batch')
                              ->get();
    return $stockAll;
  }

  public function getListBatchStock(Request $request,$product_id=null)
  {
    $data = $this->getStockBatchProduct($request);
    return response()->json($data);
  }

  public function isDate($value)
  {
      if (!$value) {
          return false;
      }
      try {
          new \DateTime($value);
          return true;
      } catch (\Exception $e) {
          return false;
      }
  }

}
