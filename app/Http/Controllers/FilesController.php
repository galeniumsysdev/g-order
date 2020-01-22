<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Storage;
use App\FileCMO;
use App\Http\Requests;
use App\User;
use DB;
use Auth;
use File;
use App\CsvData;
use App\Contact;
use App\csvproduct;
use Carbon\Carbon;

use App\Product;
use App\Cart;
use App\SoHeader;
use App\SoLine;
use Session;

use App\Customer;
use App\CustomerSite;
use App\Category;
use App\ProductFlexfield;

use App\PoDraftHeader;
use App\PoDraftLine;


use App\Http\Requests\CsvImportRequest;
use Maatwebsite\Excel\Facades\Excel;
//use App\Notifications\RejectCmo;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;

class FilesController extends Controller
{
  public function handleUpload(Request $request)
  {
      //if($request->hasFile('file')) {
          $file1 = $request->file('filepdf');
          $file2 = $request->file('fileexcel');
          $distributorid=Auth::user()->customer_id;
          $allowedFileTypes = config('constant.allowedFileTypes');
          $maxFileSize = config('constant.maxFileSize');
          $version=0;
          $rules = [
              'filepdf' => 'required|mimes:pdf',
              'fileexcel' => 'required|mimes:xls,xlsx',
          ];
          $version =DB::table('files_cmo')->where([
            ['distributor_id','=',Auth::user()->customer_id],
            ['period','=',$request->period]
            ])->max('version');
            if(is_null($version))
            {
              $version=0;
            }else{
              $version=$version+1;
            }

          $this->validate($request, $rules);
          $fileName1 = $request->period.'_'.$distributorid.'_'.$version.'.'.$file1->getClientOriginalExtension();
          $fileName2 = $request->period."_".$distributorid."_".$version.".".$file2->getClientOriginalExtension();
          //$fileName1 = $file1->getClientOriginalName()."_".$version;
          //$fileName2 = $file2->getClientOriginalName()."_".$version;
          //dd($fileName1."-".$fileName2);
          $destinationPath = config('constant.fileDestinationPath');
          $uploaded = Storage::put($destinationPath.'/'.$fileName1, file_get_contents($file1->getRealPath()));
          $uploaded2 = Storage::put($destinationPath.'/'.$fileName2, file_get_contents($file2->getRealPath()));

          $bln=substr($request->period,-2,2);
          $thn=substr($request->period,0,4);
        //dd($bln.'-'.$thn."-".$version);
          if($uploaded) {
              $filecmo = FileCMO::create([
                'distributor_id' =>Auth::user()->customer_id,
                'version'=>$version,
                'period'=>$request->period,
                'bulan'=>$bln,
                'tahun'=>$thn,
                'file_pdf' => $fileName1,
                'file_excel' => $fileName2
              ]);
          }
      //}
      $content = 'Distributor '.Auth::user()->name.' telah mengupload file CMO period:'. $request->period;
      if($version!=0)
      {
        $content .=  'versi ke '.$version.'<br>';
      }
      $content .='Silahkan buka aplikasi eOrder untuk memdownload file.<br>' ;
      $data=[
        'title' => 'Upload CMO',
        'message' => 'File CMO period #'.$request->period.' versi '.$version.' telah diupload oleh '.Auth::user()->name,
        'id' => $filecmo->id,
        'href' => route('files.readnotif'),
        'mail' => [
          'greeting'=>'File CMO period #'.$request->period.' oleh '.Auth::user()->name,
          'content' =>$content,
        ]
      ];
      $cust_Yasa=config('constant.customer_yasa', 'GPL1000001');
      $userYasa = User::whereExists(function ($query) use($cust_Yasa) {
            $query->select(DB::raw(1))
                  ->from('customers as c')
                  ->whereRaw("users.customer_id = c.id and c.customer_number = '".$cust_Yasa."'");
        })->get();
      if($userYasa)
      {
        foreach($userYasa as $yasa){
          $data['email'] = $yasa->email;
          $yasa->notify(new PushNotif($data));
        }
      }
      return redirect()->to('/uploadCMO')->withMessage(trans('pesan.successupload'));
  }

  public function importcsv()
    {
        return view('files.importCSV');
    }



      //if($request->hasFile('file')) {
      //     $file1 = $request->file('filecsv');
      //     //$file2 = $request->file('fileexcel');
      //     $distributorid=Auth::user()->customer_id;
      //     $allowedFileTypes = config('constant.allowedFileTypes');
      //     $maxFileSize = config('constant.maxFileSize');
      //     $version=0;
      //     $rules = [
      //         'filecsv' => 'required|mimes:csv,txt',
      //         //'fileexcel' => 'required|mimes:xls,xlsx',
      //     ];
      //     $version =DB::table('files_cmo')->where([
      //       ['distributor_id','=',Auth::user()->customer_id],
      //       ['period','=',$request->period]
      //       ])->max('version');
      //       if(is_null($version))
      //       {
      //         $version=0;
      //       }else{
      //         $version=$version+1;
      //       }
      //
      //     $this->validate($request, $rules);
      //     $fileName1 = $request->period.'_'.$distributorid.'_'.$version.'.'.$file1->getClientOriginalExtension();
      //     //$fileName2 = $request->period."_".$distributorid."_".$version.".".$file2->getClientOriginalExtension();
      //     //$fileName1 = $file1->getClientOriginalName()."_".$version;
      //     //$fileName2 = $file2->getClientOriginalName()."_".$version;
      //     //dd($fileName1."-".$fileName2);
      //     $destinationPath = config('constant.fileDestinationPath');
      //     $uploaded = Storage::put($destinationPath.'/'.$fileName1, file_get_contents($file1->getRealPath()));
      //   //  $uploaded2 = Storage::put($destinationPath.'/'.$fileName2, file_get_contents($file2->getRealPath()));
      //
      //     //$bln=substr($request->period,-2,2);
      //   //  $thn=substr($request->period,0,4);
      //   //dd($bln.'-'.$thn."-".$version);
      //     if($uploaded) {
      //         $filecmo = FileCMO::create([
      //           'distributor_id' =>Auth::user()->customer_id,
      //           'version'=>$version,
      //           'period'=>$request->period,
      //           'bulan'=>$bln,
      //           'tahun'=>$thn,
      //           'file_pdf' => $fileName1,
      //           'file_excel' => $fileName2
      //         ]);
      //     }
      // //}
      // $content = 'Distributor '.Auth::user()->name.' telah mengupload file CMO period:'. $request->period;
      // if($version!=0)
      // {
      //   $content .=  'versi ke '.$version.'<br>';
      // }
      // $content .='Silahkan buka aplikasi eOrder untuk memdownload file.<br>' ;
      // $data=[
      //   'title' => 'Upload CMO',
      //   'message' => 'File CMO period #'.$request->period.' versi '.$version.' telah diupload oleh '.Auth::user()->name,
      //   'id' => $filecmo->id,
      //   'href' => route('files.readnotif'),
      //   'mail' => [
      //     'greeting'=>'File CMO period #'.$request->period.' oleh '.Auth::user()->name,
      //     'content' =>$content,
      //   ]
      // ];
      // $cust_Yasa=config('constant.customer_yasa', 'GPL1000001');
      // $userYasa = User::whereExists(function ($query) use($cust_Yasa) {
      //       $query->select(DB::raw(1))
      //             ->from('customers as c')
      //             ->whereRaw("users.customer_id = c.id and c.customer_number = '".$cust_Yasa."'");
      //   })->get();
      // if($userYasa)
      // {
      //   foreach($userYasa as $yasa){
      //     $data['email'] = $yasa->email;
      //     $yasa->notify(new PushNotif($data));
      //   }
      // }
      // return redirect()->to('/uploadCMO')->withMessage(trans('pesan.successupload'));




  public function upload() {
      /*$directory = config('constant.fileDestinationPath');
      $files = Storage::files('$directory');
      var_dump($files);*/
      $period = date('M-Y', strtotime('+1 month'));

      $periodint = date('Ym', strtotime('+1 month'));
      //var_dump($periodint);
      $files = DB::table('files_cmo')
              ->where([
                        ['distributor_id','=',Auth::user()->customer_id],
                        ['period','=',$periodint]
                      ])
              ->where(function ($query) {
                $query->whereNull('approve')
                      ->orwhere('approve', '=', 1);
              })->latest()->first();
      $filereject = DB::table('files_cmo')
              ->where([
                        ['distributor_id','=',Auth::user()->customer_id],
                        ['period','=',$periodint]
                      ])
              ->where('approve', '=', 0)
              ->latest()->first();
      return view('files.upload')->with(array('files' => $files,'period'=>$period,'periodint'=>$periodint,'filereject'=>$filereject));
  }


  public function privacy() {
      /*$directory = config('constant.fileDestinationPath');
      $files = Storage::files('$directory');
      var_dump($files);*/

      return view('privacy_policy');
  }


  public function privacy_policy() {
      /*$directory = config('constant.fileDestinationPath');
      $files = Storage::files('$directory');
      var_dump($files);*/

      return view('privacy');
  }

  public function uploadcsv() {
      /*$directory = config('constant.fileDestinationPath');
      $files = Storage::files('$directory');
      var_dump($files);*/
      $period = date('M-Y', strtotime('+1 month'));

      $periodint = date('Ym', strtotime('+1 month'));
      //var_dump($periodint);
      $files = DB::table('files_cmo')
              ->where([
                        ['distributor_id','=',Auth::user()->customer_id],
                        ['period','=',$periodint]
                      ])
              ->where(function ($query) {
                $query->whereNull('approve')
                      ->orwhere('approve', '=', 1);
              })->latest()->first();
      $filereject = DB::table('files_cmo')
              ->where([
                        ['distributor_id','=',Auth::user()->customer_id],
                        ['period','=',$periodint]
                      ])
              ->where('approve', '=', 0)
              ->latest()->first();
      return view('files.uploadcsv')->with(array('files' => $files,'period'=>$period,'periodint'=>$periodint,'filereject'=>$filereject));
  }



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



  public function parseImport(CsvImportRequest $request)
{
  $path = $request->file('csv_file')->getRealPath();

        if ($request->has('header')) {
            $data = Excel::load($path, function($reader) {})->get()->toArray();
        } else {
            $data = array_map('str_getcsv', file($path));
        }

        if (count($data) > 0) {
            if ($request->has('header')) {
                $csv_header_fields = [];
                foreach ($data[0] as $key => $value) {
                    $csv_header_fields[] = $key;

                }
            }


            // $i=0;
            // foreach ($data as $key) {
            //     // $csv_header_fields[] = $key;
            //     // $temp = $key[0];
            //
            //      $data[$i]['harga'] = 20000;
            //       print_r($key['kode_product']);
            //       $i++;
            // }
            $csv_data = array_slice($data, 0, 200);

            $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('csv_file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)

            ]);
        } else {
            return redirect()->back();
        }
          return view('files.import_fields_CSV', compact( 'csv_header_fields', 'csv_data', 'csv_data_file'));

      }


      public function processImport(Request $request)
      {
          $data = CsvData::find($request->csv_data_file_id);
          $csv_data = json_decode($data->csv_data, true);
            foreach ($csv_data as $key) {
            // $product [] = [
            //   'customer_id' => Auth::user()->customer_id,
            //   'kode_product' => $key['kode_product'] ,
            //   'nama_product' => $key['nama_product'] ,
            //   'quantity' => $key['quantity'],
            //   'uom' => $key['uom'] ,
            //    'created_at' => date('Y-m-d H:i:s', time()),
            //   'updated_at' => date('Y-m-d H:i:s', time())
            // ];
              // $product1 = $key['kode_product'];
              // // $product1 = $key['nama_product'];


              $product = Product::where('itemcode','=',$key['kode_product'])->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
              if(!$product)
              {
                return response()->json([
                                'result' =>  'Tidak Sesuai/Format CSV Salah',
                                'Kode Product' => $key['kode_product'],
                              ],200);
              }else{


              $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$product->id)->select('uom_code')->get();
              $product->uom=$uom;
              $product->jns=$product->categories()->first()->parent;

              $price = DB::select("select getProductPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>Auth::user()->id,'prod'=>$product->id,'uom'=>$key['uom']]);
              $price = $price[0];

              $hargadiskon = DB::select("select getDiskonPrice ( :cust, :prod, :uom, 1 ) AS harga from dual", ['cust'=>Auth::user()->id,'prod'=>$product->id,'uom'=>$key['uom']]);
              $hargadiskon = $hargadiskon[0];


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
                if($linepo->uom!=$key['uom'])
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
                if($tax) $taxamount =  round($key['quantity']*floatval($hargadiskon->harga)*0.1,0);else $taxamount=0;
                $linepo = PoDraftLine::updateorCreate(
                            ['po_header_id'=>$headerpo->id,'product_id'=>$product->id],
                            ['qty_request'=>$key['quantity']
                            ,'uom'=>$key['uom']
                            ,'qty_request_primary'=>$product->getConversion($key['uom'])*$key['quantity']
                            ,'primary_uom'=>$product->satuan_primary
                            ,'conversion_qty'=>$product->getConversion($key['uom'])
                            ,'inventory_item_id'=>$product->inventory_item_id
                            ,'list_price'=>floatval($price->harga)
                            ,'unit_price'=>floatval($hargadiskon->harga)
                            ,'amount'=>$key['quantity']*floatval($price->harga)
                            ,'discount'=>round($key['quantity']*floatval($price->harga),0)-round($key['quantity']*floatval($hargadiskon->harga),0)
                            ,'tax_amount'=>$taxamount
                            ,'jns'=>$product->jns
                            ]
                  );
            }
             //  print_r($product->id);
             //  print_r('<-->');
             //  print_r($price->harga);
             // // print_r('<-->');
             //  print_r($hargadiskon->harga);
             $headerpo->subtotal +=($key['quantity']*floatval($price->harga));
             $headerpo->discount+= round($key['quantity']*floatval($price->harga),0)-round($key['quantity']*floatval($hargadiskon->harga),0);
             if($tax)
             {
                 $headerpo->tax =PoDraftLine::where('po_header_id','=',$headerpo->id)->sum('tax_amount');
             }else{
                 $headerpo->tax =0;
             }

             $headerpo->Amount= $headerpo->subtotal - $headerpo->discount + $headerpo->tax;
             $headerpo->save();

             // //$request->session()->put('cart',$cart);
             // return response()->json([
             //                 'result' => 'success',
             //                 'totline' => $headerpo->lines()->count(),
             //               ],200);
             //return redirect()->route('product.index');
          }}

          // $insert_product = csvproduct ::insert($product);
          return view('files.import_success');

    }



    public function downloadTemplateCSV()
    {
      return redirect('/file/Template_CSV_po.csv');
    }


  public function downfunc($id = '') {
       if($id){
         $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id')
                   ->where('files_cmo.id','=',$id)
                   ->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve','bulan','tahun','files_cmo.keterangan')
                   ->orderBy('Period','desc')
                   ->get();
         $distributor = $downloads->first()->distributor_id;
         $bulan = $downloads->first()->bulan;
         $tahun = $downloads->first()->tahun;
         $status= $downloads->first()->approve;
         //dd($downloads);
       }else{
         $bulan=date('m')+1;
         $tahun=date('Y');
         $distributor=null;
         $status="";
         $id=null;
          $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id')
                    ->where('distributor_id','=',Auth::user()->customer_id)
                    ->where('tahun','=',$tahun)
                    ->where('bulan','=',$bulan)
                    ->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve','files_cmo.keterangan')
                    ->orderBy('Period','desc')
                    ->get();
        }
        return view ('files.viewfile',compact('downloads','bulan','tahun','distributor','status','id'));
    }

  public function search(Request $request){
      $bulan = $request->bulan;
      $tahun = $request->tahun;
      $distributor = $request->distributor;
      $status =$request->status;
      $id=null;

      $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id');
      if(Auth::user()->hasRole('Principal'))
      {
        if($request->distributor!="")
        {
            $downloads=$downloads->where('customer_name','like',$request->distributor.'%');
        }
        if($request->status!="%")
        {
          if($request->status=="")
          {
            $downloads=$downloads->whereNull('files_cmo.approve');
          }else{
            $downloads=$downloads->where('files_cmo.approve','=',$request->status);
          }
        }
      }elseif(Auth::user()->hasRole('Distributor')){
        $downloads=$downloads->where('distributor_id','=',Auth::user()->customer_id);
      }else{
        $downloads=$downloads->where('files_cmo.approve','=',1);
      }
      if($request->tahun!="")
      {
          $downloads=$downloads->where('tahun','=',$request->tahun);
      }
      if($request->bulan!="")
      {
          $downloads=$downloads->where('bulan','=',$request->bulan);
      }
      $downloads=$downloads->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve','files_cmo.keterangan');
      //var_dump($downloads->toSql());
      $downloads=$downloads->orderBy('Period','desc')->orderBy('version', 'desc');
      $downloads=$downloads->get();
      return view ('files.viewfile',compact('downloads','bulan','tahun','distributor','status','id'));
  }

  public function approvecmo(Request $request, $id){
    DB::beginTransaction();
    try{
      $cmo_distributor = FileCMO::find($id);
      if($cmo_distributor){
        $oldstatus =$cmo_distributor->approve;
        if($request->approve=="approve")
        {
          $cmo_distributor->approve=1;
          $cmo_distributor->first_download=Carbon::now();
          $cmo_distributor->save();
          /*DB::table('files_cmo')->where('id','=',$id)->wherenull('approve')
          ->update(['approve' => 1, 'first_download'=>Carbon::now(),'updated_at'=>Carbon::now()]);*/
          //$cmo_distributor =FileCMO::find($id);
          $userdistributor =User::where('customer_id','=',$cmo_distributor->getDistributor->id)->first();
          $content = 'File CMO Anda untuk period:'.$cmo_distributor->period.' telah diperiksa dan diterima oleh Yasa Mitra Perdana.';
          $content .='Terimakasih telah menggupload menggunakan '.config('app.name').'.<br>' ;
          $data=[
            'title' => 'Konfirmasi CMO Oleh Yasa',
            'message' => 'File CMO period #'.$cmo_distributor->period.' telah diterima',
            'id' => $cmo_distributor->id,
            'href' => route('files.readnotif'),
            'mail' => [
              'greeting'=>'File CMO period #'.$cmo_distributor->period.' telah diterima',
              'content' =>$content,
            ]
          ];
        }elseif($request->approve=="reject"){
          $this->validate($request, [
          'reason_reject' => 'required',
          ]);
          $cmo_distributor->approve=0;
          $cmo_distributor->first_download=Carbon::now();
          $cmo_distributor->keterangan=$request->reason_reject;
          $cmo_distributor->save();
          $userdistributor =User::where('customer_id','=',$cmo_distributor->getDistributor->id)->first();
          $content = 'Mohon maaf, Harap upload kembali file CMO Anda untuk period:'.$cmo_distributor->period.'.';
          $content .='Silahkan konfirmasi ke Yasa Mitra Perdana untuk penjelasan lebih detail.<br>' ;
          $message = 'File CMO period #'.$cmo_distributor->period.' ditolak';
          if (isset($request->reason_reject))
          {
            $message .= " dengan alasan: ". $request->reason_reject;
          }
          $data=[
            'title' => 'Penolakan CMO Oleh Yasa',
            'message' => $message,
            'id' => $cmo_distributor->id,
            'href' => route('files.readnotif'),
            'mail' => [
              'greeting'=>'File CMO period #'.$cmo_distributor->period.' ditolak',
              'content' =>$content,
            ]
          ];
        }
        if($userdistributor and is_null($oldstatus))
        {
          $data['email'] = $userdistributor->email;
          $userdistributor->notify(new PushNotif($data));
        }
      }
      DB::commit();
      //return $this->search($request);
      return redirect()->route('files.viewfile',['id'=>$id]);
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }
    //return redirect()->route('files.postviewfile', ['request'=>$request]);
  }

  public function readNotif($id,$notifid)
  {
    $notif = Auth::User()->notifications()
               ->where('id','=',$notifid)->first();
               //->update(['read_at' => Carbon::now()])
    if($notif){
      $notif->read_at = Carbon::now();
      $notif->save();
      if($notif->data['tipe']=="Upload CMO") return redirect()->route('files.viewfile',['id'=>$notif->data['id']]);
      else return redirect()->route('files.uploadcmo');
    }

  }



}
