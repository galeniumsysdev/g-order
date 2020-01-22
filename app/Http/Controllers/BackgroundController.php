<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SoHeader;
use App\SoLine;
use Auth;
use App\Customer;
use Carbon\Carbon;
use App\Notifications\BookOrderOracle;
use App\Notifications\RejectPOByDistributor;
use App\Notifications\ShippingOrderOracle;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;
use Excel;
use App\QpListHeaders;
use App\QpListLine;
use App\OeTransactionType;
use App\User;
use App\SoShipping;
use App\CustomerSite;
use App\CustomerContact;
use App\qp_modifier_summary;
use App\qp_qualifiers;
use App\QpPricingDiskon;
use App\Product;
use App\UomConversion;
use Mail;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
//use App\Mail\CustumerInterfaceBg;

class BackgroundController extends Controller
{
    public function getStatusOrderOracle()
    {set_time_limit(0);  
      DB::beginTransaction();
      try{
      $request= DB::table('tbl_request')->where('event','=','SalesOrder')
                ->max('created_at');
      if($request)
      {
        $lasttime = date_create($request);
        //echo"type:".gettype($lasttime);
      }else{
        $lasttime = date_create("2017-07-01");
      }
      $newrequest= DB::table('tbl_request')->insertGetId([
        'created_at'=>Carbon::now(),
        'updated_at'=>Carbon::now(),
        'event'=>'SalesOrder'
      ]);
      $connoracle = DB::connection('oracle');
      if($connoracle){
        echo "masuk<br>";
        $headers = SoHeader::whereNotNull('oracle_customer_id')->where([
                  ['approve','=',1],
                  ['status','>=',0],
                  ['status','<=',3],
                 // ['notrx','>=','PO-20180703-VII-00009'],['notrx','<=','PO-20180717-VII-00041']
        ])->get();
        if($headers){
          foreach($headers as $h)
          {
            echo "notrx:".$h->notrx."<br>";

                if($h->status==0)
                {
                  $mysoline = SoLine::where([
                                      ['header_id','=',$h->id],
                                      ['qty_confirm','!=',0]
                                      ])
                            ->get();
                  //getSo di Oracle
                  foreach($mysoline as $sl)
                  {
                    echo "line:".$sl->line_id."<br>";
                    $oraSO=$this->getSalesOrder($h->notrx,$sl);
                    if(!is_null($oraSO))
                    {
                      if($oraSO->inventory_item_id!=$sl->inventory_item_id)
                      {
                        $newproducts = Product::where('inventory_item_id',$oraSO->inventory_item_id)->first();
                        if($newproducts){
                          $sl->product_id =$newproducts->id;
                          $sl->inventory_item_id = $oraSO->inventory_item_id;
                          $sl->uom_primary = $newproducts->satuan_primary;
                          $sl->conversion_qty = $newproducts->getConversion($sl->uom);
                        }
                      }
                      echo "qty:".$oraSO->ordered_quantity."<br>";
                      $sl->qty_confirm =$oraSO->ordered_quantity_primary;
                      //$sl->qty_confirm_primary=$oraSO->ordered_quantity_primary;
                      if($oraSO->ordered_quantity!=0){
                        $sl->list_price=$oraSO->unit_list_price/$oraSO->ordered_quantity;
                        $sl->unit_price=$oraSO->amount/$oraSO->ordered_quantity;
                      }else{
                        $sl->list_price=$oraSO->unit_list_price;
                        $sl->unit_price=$oraSO->amount;
                      }
                      $sl->tax_amount=$oraSO->tax_value;
                      $sl->amount=$oraSO->unit_list_price;
                      $sl->disc_reg_amount = null;
                      $sl->disc_reg_percentage = null;
                      $sl->disc_product_amount = null;
                      $sl->disc_product_percentage=null;
                      $orapriceadj = $this->getadjustmentSO(null,$h->notrx,$sl);
                      foreach($orapriceadj as $adj )
                      {
                        echo "bucket:".$adj->pricing_group_sequence."<br>";
                        echo "amount:".$adj->adjusted_amount."<br>";
                        echo "percentage:".$adj->operand."<br>";
                        if($adj->pricing_group_sequence==1)
                        {
                          $sl->disc_reg_amount = $adj->adjusted_amount*-1;
                          $sl->disc_reg_percentage = $adj->operand;
                        }elseif($adj->pricing_group_sequence==2)
                        {
                          $sl->disc_product_amount = $adj->adjusted_amount*-1;
                          $sl->disc_product_percentage = $adj->operand;
                        }else{
                          $sl->disc_product_amount += $adj->adjusted_amount*-1;
                        }
                          $sl->save();
                      }
                    }
                  }//endforeach soline
                  //
                  //$oraheader = $connoracle->selectone('select min(booked_date)');
                  $newline =$this->getadjustmentHeaderSO($h->notrx,$h->id);
                  $oraheader = $connoracle->selectone("select min(booked_date) as booked_date from oe_order_headers_all oha
                                where  oha.flow_status_code in ('BOOKED') and exists (select 1 from oe_ordeR_lines_all ola
                                            where ola.headeR_id =oha.headeR_id
                                              and nvl(ola.attribute1,oha.orig_sys_document_ref)='".$h->notrx."')");
                  //dd($oraheader);
                  if(!is_null($oraheader->booked_date))
                  {
                    $h->status=1;
                    $h->interface_flag="Y";
                    $h->status_oracle ="BOOKED";
                    $h->save();
                    //notification to user
                     $customer = Customer::where('id','=',$h->customer_id)->first();
                     $content ='PO Anda nomor '.$h->notrx.' telah dikonfirmasi oleh '.$h->distributor->customer_name.'. Silahkan check PO anda kembali.<br>';
                     $content .= 'Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
                     $data = [
             					'title' => 'Konfirmasi PO',
             					'message' => 'Konfirmasi PO '.$h->customer_po.' oleh distributor.',
             					'id' => $h->id,
             					'href' => route('order.notifnewpo'),
             					'mail' => [
             						'greeting'=>'Konfirmasi PO '.$h->customer_po.' oleh distributor.',
                        'content' =>$content,
             					]
             				];
                     foreach($customer->users as $u)
                     {
                      $data['email']= $u->email;
                       //$u->notify(new BookOrderOracle($h,$customer->customer_name));
                      // event(new PusherBroadcaster($data, $u->email));
                       $u->notify(new PushNotif($data));
                     }
                  }


                }//endif status==0 (belum di booked)
                elseif($h->status>0 and $h->status<=3 )
                {
                  echo "status sudah booked belum kirim untuk notrx:".$h->notrx."<br>";
                  $jmlolddelivery = SoShipping::where('header_id','=',$h->id)->groupBy('deliveryno')->select('deliveryno')->get()->count();
                  $mysoline = SoLine::where([
                                      ['header_id','=',$h->id],
                                      ['qty_confirm','!=',0]
                                      ])
                            ->get();
                  $berubah=false;
                  //getSo di Oracle
                  foreach($mysoline as $sl)
                  {
                    $jumshippingbefore = $sl->qty_shipping;
                    echo "line:".$sl->line_id."<br>";
                    $ship = $this->getShippingSO($h->notrx,$sl->line_id,$lasttime,$sl->product_id,$h->id);
                    if($ship==1)
                    {
                      $jmlkirim = $sl->shippings()->sum('qty_shipping');
                      if($sl->shippings()->sum('qty_accept')!=0) $sl->qty_accept = $sl->shippings()->sum('qty_accept');
                      $sl->save();
                      echo "jmlkirim:".$jmlkirim."<br>";
                      if ($jumshippingbefore != $jmlkirim)
                      {
                        $sl->qty_shipping = $jmlkirim;
                        if($sl->shippings()->sum('qty_accept')!=0) $sl->qty_accept = $sl->shippings()->sum('qty_accept');
                        $sl->save();
                        $berubah=true;
                      }

                      //dd($jmlkirim);
                    }
                  }

                  //notif to customer jika berubah
                  if($berubah)
                  {
                    $soline_notsend = DB::table('so_lines_sum_v')
                                      ->where('header_id','=',$h->id)
                                      //->where('qty_confirm_primary','<>','qty_shipping_primary')
                                      ;
                    $soline_notsend = $soline_notsend->first();
                    //dd($soline_notsend);
                    //echo "count: ".$soline_notsend->qty_confirm_primary()."<br>";
                    //if($soline_notsend->count()>0){
                    if($soline_notsend->qty_confirm_primary<>$soline_notsend->qty_shipping_primary){
                      $h->status=2;
                    }elseif($soline_notsend->qty_accept_primary==$soline_notsend->qty_shipping_primary){
                      $h->status=4;
                    }else{
                      $h->status=3;
                    }
                    $h->save();
                    if($jmlolddelivery!=SoShipping::where('header_id','=',$h->id)->groupBy('deliveryno')->select('deliveryno')->get()->count())
                    {
                      $content = 'PO Anda nomor '.$h->customer_po.' telah dikirimkan oleh '.$h->distributor->customer_name.'. ';
                      $content .='Silahkan check PO anda kembali.<br>' ;
                      $content .='Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
                      $data=[
                        'title' => 'Pengiriman PO',
                        'message' => 'PO #'.$h->customer_po.' telah dikirim',
                        'id' => $h->id,
                        'href' => route('order.notifnewpo'),
                        'mail' => [
                          'greeting'=>'Pengriman Barang PO #'.$h->customer_po.'.',
                          'content' =>$content,
                        ]
                      ];
                      foreach ($h->outlet->users as $u)
                      {
                        $data['email']=$u->email;
                        //event(new PusherBroadcaster($data, $u->email));
                        $u->notify(new PushNotif($data));
                      }
                    }
                  }

                }
            }//foreach

          }//if$headers


        }// end if(connoracle){
        else{
          echo "can't connect to oracle";
        }
	 DB::table('tbl_request')->where('id','=',$newrequest)->update(['tgl_selesai'=>Carbon::now()]);
        DB::commit();
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }

    public function insert_interface_oracle(SoHeader $h)
    {
      $oraheader = $connoracle->table('oe_headers_iface_all')->insert([
        'order_source_id'=>config('constant.order_source_id')
        ,'orig_sys_document_ref'=>$h->notrx
        ,'org_id'=>$h->org_id
        ,'sold_from_org_id'=>$h->org_id
        //,'ship_from_org_id'=>$h->warehouse
        ,'ordered_date'=>$h->tgl_order
        ,'order_type_id'=>$h->order_type_id
        ,'sold_to_org_id'=>$h->oracle_customer_id
        ,'payment_term_id'=>$h->payment_term_id
        ,'operation_code'=>'INSERT'
        ,'created_by'=>-1
        ,'creation_date'=>Carbon::now()
        ,'last_updated_by'=>-1
        ,'last_update_date'=>Carbon::now()
        ,'customer_po_number'=>$h->customer_po
        ,'price_list_id'=>$h->price_list_id
        ,'ship_to_org_id'=>$h->oracle_ship_to
        ,'invoice_to_org_id'=>$h->oracle_bill_to
      ]);

      $solines = DB::table('so_lines')->where('header_id','=',$h->id)->get();
      $i=0;
      foreach($solines as $soline)
      {
        $i+=1;
          if($oraheader){
            $oraline = $connoracle->table('oe_lines_iface_all')->insert([
              'order_source_id'=>config('constant.order_source_id')
              ,'orig_sys_document_ref' => $h->notrx
              ,'orig_sys_line_ref'=>$soline->line_id
              ,'line_number'=>$i
              ,'inventory_item_id'=>$soline->inventory_item_id
              ,'ordered_quantity'=>$soline->qty_confirm
              ,'order_quantity_uom'=>$soline->uom
              /*,'ship_from_org_id'=>$soline->qty_shipping*/
              ,'org_id'=>$h->org_id
              //,'pricing_quantity'
              //,'unit_selling_price'
              ,'unit_list_price'=>$soline->unit_price
              //,'price_list_id'
              //,'payment_term_id'
              //,'schedule_ship_date'
              ,'request_date'=>$h->tgl_order
              ,'created_by'=>-1
              ,'creation_date'=>Carbon::now()
              ,'last_updated_by'=>-1
              ,'last_update_date'=>Carbon::now()
              //,'line_type_id'
              ,'calculate_price_flag'=>'Y'
            ]);
          }

      }

    }

    public function synchronize_oracle(){
      DB::beginTransaction();
      try{
        $request= DB::table('tbl_request')->where('event','=','synchronize')
                  ->max('created_at');
        if($request)
        {
          $lasttime = date_create($request);
        }else{
          $lasttime = date_create("2017-07-01");
        }
        $sheetArray = [];
        echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
        $connoracle = DB::connection('oracle');
        if($connoracle){
          $tgloracle = $connoracle->selectone("select sysdate as tgl from dual");
          if(!empty($tgloracle)) $tgloracle=date_create($tgloracle->tgl); else $tgloracle = Carbon::now();
          $newrequest= DB::table('tbl_request')->insertGetId([
            'created_at'=>$tgloracle,
            'updated_at'=>$tgloracle,
            'event'=>'synchronize',
          ]);
          echo "request id:".$newrequest."<br>";
          $masteritem = $this->getMasterItem($lasttime);
          if(count($masteritem) > 1) $sheetArray['product']=$masteritem;
          $datakonversi = $this->getConversionItem($lasttime);
          if(count($datakonversi)>1) $sheetArray['conversion']=$datakonversi;
          $datacustomer = $this->getCustomer($lasttime,true);
          $sheetArray = array_merge($sheetArray,$datacustomer);

          $price = $this->getMasterDiscount($lasttime,true);
          if(count($price)>0) $sheetArray = array_merge($sheetArray,$price);
          $transactiontype = $connoracle->table('oe_transaction_types_all as otta')
                            ->join('oe_transaction_types_tl as ottt','otta.transaction_type_id','=','ottt.transaction_type_id')
                            ->where([['otta.transaction_type_code', '=', 'ORDER'],
                                    ['otta.order_category_code', '=','ORDER']
                                  ])
                            ->select('otta.transaction_type_id','ottt.name', 'ottt.description', 'otta.start_date_active', 'end_date_active', 'currency_code','price_list_id'
                              , 'warehouse_id', 'org_id' )
                      ->where('otta.last_update_date','>=',$lasttime)
                      ->get();
          if($transactiontype->count()>0){
            $tipetransaksi=[];
            $tipetransaksi[]=['transaction type id','name','start_date','end_date','org_id'];
            foreach($transactiontype as $ott)
            {
                //echo "transaction_type_id:".$ott->transaction_type_id."<br>";
              $mytransactiontype = OeTransactionType::updateOrCreate(
                ['transaction_type_id'=>$ott->transaction_type_id],
                ['name'=>$ott->name,'description=>$ott->description','start_date_active'=>$ott->start_date_active
                ,'end_date_active'=>$ott->end_date_active,'currency_code'=>$ott->currency_code,'price_list_id'=>$ott->price_list_id
                ,'warehouse_id'=>$ott->warehouse_id,'org_id'=>$ott->org_id
                ]
              );
              $tipetransaksi[]=[$ott->transaction_type_id,$ott->name,$ott->start_date_active,$ott->end_date_active,$ott->org_id];
            }
            $sheetArray['tipe transaksi']=$tipetransaksi;
          }
          //dd($sheetArray);
          if(count($sheetArray)>0)
          {
            $file = Excel::create("sync_gorder".date_format($tgloracle,'Ymd His'),function($excel) use ($sheetArray) {
              $excel->setTitle('Synchronize DB gOrder');
              $excel->setCreator('Shanty')
              ->setCompany('Solinda');
              foreach($sheetArray as $key=>$vArray)
              {
                if(count($vArray)>1){
                  $excel->sheet($key, function($sheet) use ($vArray) {
                    $sheet->fromArray($vArray, null, 'A1', true,false);
                    $sheet->row(1, function($row) {
                      $row->setBackground('#6495ED');
                      $row->setFontWeight('bold');
                      $row->setAlignment('center');
                    });
                  });
                }
              }
            });
            $userit = User::whereexists(function($query){
              $query->select(DB::raw(1))
                    ->from('role_user as ru')
                    ->join('roles as r','ru.role_id','r.id')
                    ->whereraw('ru.user_id = users.id')
                    ->wherein('r.name',['IT Galenium']);
            })->select('email','name','id')->get();
            foreach($userit as $u){
              \Mail::send('emails.customerinterface',["user"=>$u],function($m) use($file,$u){
                  $m->to(trim($u->email), $u->name)->subject('Synchronize DB gOrder');
                  $m->attach($file->store("xlsx",false,true)['full']);
              });
            }
          }else{
            $userit = User::whereexists(function($query){
              $query->select(DB::raw(1))
                    ->from('role_user as ru')
                    ->join('roles as r','ru.role_id','r.id')
                    ->whereraw('ru.user_id = users.id')
                    ->wherein('r.name',['IT Galenium']);
            })->select('email','name','id')->get();
            foreach($userit as $u){
              \Mail::send('emails.nointerface',["user"=>$u],function($m) use($u){
                  $m->to(trim($u->email), $u->name)->subject('Synchronize DB gOrder');
              });
            }
          }
          //$customrsite =
          DB::table('tbl_request')->where('id','=',$newrequest)->update(['tgl_selesai'=>Carbon::now()]);
        }
        DB::commit();
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }

    public function getPricelist($lasttime=null,$bgprocess=false)
    {
      $data=[];
      $sheetarray=[];
      if(is_null($lasttime))
      {
        $request= DB::table('tbl_request')->where('event','=','synchronize')
                  ->max('created_at');
        if($request)
        {
          $lasttime = date_create($request);
          //echo"type:".gettype($lasttime);
        }else{
          $lasttime = date_create("2017-07-01");
        }
      }
      //echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $qp_listheader = $connoracle->table('qp_list_headers')
                    ->where('last_update_date','>=',$lasttime)
                    ->select('List_header_id','name', 'description','version_no', 'currency_code'
                    , 'start_date_active', 'end_date_active', 'automatic_flag', 'list_type_code', 'terms_id', 'rounding_factor'
                    , 'discount_lines_flag', 'active_flag', 'orig_org_id', 'global_flag')->get();
        //dd($qp_listheader);
        if($qp_listheader->count()>0){
          $sheetarray[]=['List header id','Name','start_date_active','end_date_active','list_type_code'];
          foreach($qp_listheader as $ql){
              //echo "list header id:".$ql->list_header_id.":".$ql->name."<br>";
            $mylistheader = QpListHeaders::updateOrCreate (
              ['list_header_id'=>$ql->list_header_id],
              ['name'=>$ql->name,'description'=>$ql->description,'version_no'=>$ql->version_no,'currency_code'=>$ql->currency_code
              ,'start_date_active'=>$ql->start_date_active,'end_date_active'=>$ql->end_date_active,'automatic_flag'=>$ql->automatic_flag
              ,'list_type_code'=>$ql->list_type_code,'discount_lines_flag'=>$ql->discount_lines_flag,'active_flag'=>$ql->active_flag
              ,'orig_org_id'=>$ql->orig_org_id,'global_flag'=>$ql->global_flag
              ]
            );
            $sheetarray[]=[$ql->list_header_id,$ql->name,$ql->start_date_active,$ql->end_date_active,$ql->list_type_code];
          }
        }
        if(count($sheetarray)>1) $data['price header'] = $sheetarray;
        $linearray = [];
        $qp_listlines =$connoracle->table('qp_list_lines_v as qll')
                        ->join('qp_list_headers_all qlh','qll.list_headeR_id','=','qlh.list_header_id')
                        ->where('qll.last_update_date','>=',$lasttime)
                        ->where('qll.list_line_type_code','=','PLL')
                        ->where('qll.product_attribute','=','PRICING_ATTRIBUTE1')
                        ->select('qll.list_line_id', 'qll.list_header_id', 'product_attribute_context','product_attr_value'
                                ,'product_uom_code','qll.start_date_active','qll.end_date_active','revision_date','operand'
                                ,'qlh.currency_code','qlh.active_flag','qlh.name')
                        ->get();
        if($qp_listlines)
        {
          $linearray[]=['Price Name','Line Id','Product','Operand','Start Date','End Date'];
          //echo "<h2>Data Priceline Oracle</h2>";
          //secho "<table><tr><th>Price Name</th><th>Line Id</th><th>Products</th><th>Operand</th><th>Start Date</th><th>End Date</th></tr>";

          foreach($qp_listlines as $ql)
          {
            $myqplines = QpListLine::updateOrCreate(
              ['list_line_id'=>$ql->list_line_id],
              ['list_header_id'=>$ql->list_header_id
              ,'product_attribute_context'=>$ql->product_attribute_context
              , 'product_attr_value'=>$ql->product_attr_value
              , 'product_uom_code'=>$ql->product_uom_code
              ,'start_date_active'=>$ql->start_date_active
              ,'end_date_active'=>$ql->end_date_active
              ,'revision_date'=>$ql->revision_date
              ,'operand'=>$ql->operand
              ,'currency_code'=>$ql->currency_code
              ,'enabled_flag'=>$ql->active_flag
            ]);
            $product = Product::where('inventory_item_id','=',$ql->product_attr_value)->select('title')->first();
            if(isset($product)) $nmproduct = $product->title;else $nmproduct = $ql->product_attr_value;
            $linearray[]=[$ql->name,$ql->list_line_id,$nmproduct,$ql->operand,$ql->start_date_active,$ql->end_date_active];
            /*echo "<tr>";
            echo "<td>".$ql->name."</td>";
            echo "<td>".$ql->list_line_id."</td>";
            echo "<td>".$nmproduct."</td>";
            echo "<td>".$ql->operand."</td>";
            echo "<td>".$ql->start_date_active."</td>";
            echo "<td>".$ql->end_date_active."</td>";
            echo "</tr>";*/
          }
          /*echo "</table><br>";*/
          if(count($linearray)>1) $data['price lines'] = $linearray;
          $dellistline = QpListLine::whereraw("ifnull(end_date_active,curdate()+interval 1 day) < curdate()")
                        ->delete();
          $oralistlines =$connoracle->table('qp_list_lines_v as qll')
                        ->where('list_line_type_code','=','PLL')
                        ->select('list_line_id')->get();
          if($oralistlines){
            $dellistline = QpListLine::whereNotIn('list_line_id',$oralistlines->pluck('list_line_id')->toArray())
                        ->delete();
          }

        }
        DB::commit();
        if($bgprocess) return $data;
        else{
          if(count($data)>0){
          $file = Excel::create("sync_pricing".date('Ymd His'),function($excel) use ($data) {
              $excel->setTitle('Interface Pricing');
              $excel->setCreator('Shanty')
              ->setCompany('Solinda');
              foreach($data as $key=>$vArray)
              {
                if(count($vArray)>1){
                  $excel->sheet($key, function($sheet) use ($vArray) {
                    $sheet->fromArray($vArray, null, 'A1', true,false);
                    $sheet->row(1, function($row) {
                      $row->setBackground('#6495ED');
                      $row->setFontWeight('bold');
                      $row->setAlignment('center');
                    });
                  });
                }
              }
            })->download('xlsx');
          }else{
            echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
            echo "Tidak ada data yang diproses<br>";
          }
        }
        return $data;
      }else{
        echo "Can't connect to oracle database";
        return 0;
      }
    }

    public function getCustomer($lasttime = null,$bgprocess = false)
    {
      DB::beginTransaction();
      try{
        if($lasttime==null)
        {
          $request= DB::table('tbl_request')->where('event','=','customer')
                    ->max('created_at');
          if($request)
          {
            $lasttime = date_create($request);
            //echo"type:".gettype($lasttime);
          }else{
            $lasttime = date_create("2017-01-01");
          }
          //echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
        }
        $sheetcustomer = [];
        $connoracle = DB::connection('oracle');
        $tglskrg = $connoracle->selectone("select sysdate as tgl from dual");
        if(!empty($tglskrg)) $tglskrg=$tglskrg->tgl; else $tglskrg = Carbon::now();
        $newrequestcust= DB::table('tbl_request')->insertGetId([
          'created_at'=>$tglskrg,
          'updated_at'=>$tglskrg,
          'event'=>'customer',
        ]);
        //echo "request id:".$newrequestcust."<br>";
        $customers = $connoracle->table('ar_customers as ac')
                    ->leftjoin('HZ_CUSTOMER_PROFILES as hcp','ac.customer_id', 'hcp.cust_Account_id')
                    ->leftjoin('ra_terms as rt','hcp.STANDARD_TERMS','rt.term_id')
                    ->whereIn('customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','OUTLET','EXPORT','TOLL IN'])
                    ->where('ac.last_update_date','>=',$lasttime)
                    ->select('customer_name' , 'customer_number','customer_id', 'ac.status', 'ac.attribute3 as CUSTOMER_CATEGORY_CODE'
                          , DB::raw('ac.CUSTOMER_CLASS_CODE as customer_class_code')
                          , 'primary_salesrep_id'
                          , 'tax_reference'
                          , 'tax_code'
                          , 'price_list_id'
                          , 'order_type_id'
                          , 'customer_name_phonetic'
                          , 'rt.name as payment_term','ac.orig_system_reference','ac.attribute4','ac.attribute5' )
                    ->orderBy('customer_number','asc')
                    ->get();
        if(count($customers)){
          $sheetcustomer[] = ['customer_id','customer_number','customer_name','status'];
          //echo "<h2>Data Customer Oracle</h2>";
          //echo "<table><tr><th>Customer Number</th><th>Customer Name</th></tr>";
          foreach($customers as $c)
          {
            //echo"<tr>";
            //echo "<td>".$c->customer_number."</td>";
            //echo "<td>".$c->customer_name."</td>";
            //echo "</tr>";
            $psc_flag=null;
            $pharma_flag=null;
            $export_flag=null;
            $tollin_flag=null;
            $ijinpbf =0;
            if($c->customer_class_code == 'DISTRIBUTOR PSC' or $c->customer_class_code=='OUTLET')
            {
              $psc_flag="1";
            }elseif($c->customer_class_code == 'DISTRIBUTOR PHARMA'){
              $pharma_flag="1";
            }elseif($c->customer_class_code == 'TOLL IN'){
              $tollin_flag="1";
            }elseif($c->customer_class_code == 'EXPORT'){
              $export_flag="1";
            }

            if($c->attribute4!='' and !is_null($c->attribute4)) $ijinpbf=1;


              $existscustomer = Customer::where('id',$c->orig_system_reference)->first();


            if($existscustomer){
              //echo "insert customer oracle to customer id:" . $c->orig_system_reference."<br>";
              $mycustomer = Customer::updateOrCreate(
                ['id'=>$c->orig_system_reference],
                ['oracle_customer_id'=>$c->customer_id, 'customer_name'=>$c->customer_name,'customer_number'=>$c->customer_number,'status'=>$c->status
                ,'customer_category_code'=>$c->customer_category_code,'customer_class_code'=>$c->customer_class_code
                ,'primary_salesrep_id'=>$c->primary_salesrep_id,'tax_reference'=>$c->tax_reference,'tax_code'=>$c->tax_code
                ,'price_list_id'=>$c->price_list_id,'order_type_id'=>$c->order_type_id,'customer_name_phonetic'=>$c->customer_name_phonetic
                ,'payment_term_name'=>$c->payment_term,'no_ijin'=>$c->attribute4,'masa_berlaku'=>$c->attribute5,'ijin_pbf'=>$ijinpbf
                ]
              );
              $sheetcustomer[]=[$c->customer_id,$c->customer_number,$c->customer_name,'merge with'.$existscustomer->id];
            }else{
              $mycustomer = Customer::updateOrCreate(
                ['oracle_customer_id'=>$c->customer_id],
                ['customer_name'=>$c->customer_name,'customer_number'=>$c->customer_number,'status'=>$c->status
                ,'customer_category_code'=>$c->customer_category_code,'customer_class_code'=>$c->customer_class_code
                ,'primary_salesrep_id'=>$c->primary_salesrep_id,'tax_reference'=>$c->tax_reference,'tax_code'=>$c->tax_code
                ,'price_list_id'=>$c->price_list_id,'order_type_id'=>$c->order_type_id,'customer_name_phonetic'=>$c->customer_name_phonetic
                ,'payment_term_name'=>$c->payment_term,'psc_flag'=>$psc_flag,'pharma_flag'=>$pharma_flag,'export_flag'=>$export_flag,'tollin_flag'=>$tollin_flag
                ,'no_ijin'=>$c->attribute4,'masa_berlaku'=>$c->attribute5,'ijin_pbf'=>$ijinpbf
                ]
              );
              $sheetcustomer[]=[$c->customer_id,$c->customer_number,$c->customer_name,'update/insert'];
            }
            if($c->status=='I'){
              $updateuser = User::where('customer_id','=',$c->customer_id)
              ->update(['validate_flag'=>0]);
            }elseif($c->status=='A'){
              $updateuser = User::where('customer_id','=',$c->customer_id)
                            ->whereNotNull('password')->first();
              if ($updateuser)
              {
                if($updateuser->validate_flag==0)
                {
                  $updateuser->validate_flag=1;
                  $updateuser->save();
                }
              }
            }
          }
          //echo"</table>";
        }
        $sheetArray=[];
        if(count($sheetcustomer)>1) $sheetArray['customer'] = $sheetcustomer;
        $customersite = $this->getCustomerSites($lasttime);
        $customercontacts = $this->getCustomerContacts($lasttime);
        if(count($customersite)>1) $sheetArray['customer sites'] = $customersite;
        if(count($customercontacts)>1) $sheetArray['customer contact'] = $customercontacts;
        DB::table('tbl_request')->where('id','=',$newrequestcust)->update(['tgl_selesai'=>Carbon::now()]);
        DB::commit();
        if($bgprocess) return $sheetArray;
        else{
          if(count($sheetArray)>0){
          $file = Excel::create("sync_cust".date('Ymd His'),function($excel) use ($sheetArray) {
              $excel->setTitle('Interface customer');
              $excel->setCreator('Shanty')
              ->setCompany('Solinda');
              foreach($sheetArray as $key=>$vArray)
              {
                if(count($vArray)>1){
                  $excel->sheet($key, function($sheet) use ($vArray) {
                    $sheet->fromArray($vArray, null, 'A1', true,false);
                    $sheet->row(1, function($row) {
                      $row->setBackground('#6495ED');
                      $row->setFontWeight('bold');
                      $row->setAlignment('center');
                    });
                  });
                }
              }
            })->download('xlsx');
          }else{
            echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
            echo "Tidak ada data yang diproses<br>";
          }
        }
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

    }
    public function getCustomerSites($lasttime)
    {
      $connoracle = DB::connection('oracle');
      $sitesheet=[];
      if($connoracle){
        $sites = $connoracle->table('HZ_CUST_ACCT_SITES_ALL hcas')
                    ->join('hz_party_sites hps','hcas.PARTY_SITE_ID', '=', 'hps.party_site_id')
                    ->join('hz_locations hl','hps.location_id','=','hl.location_id')
                    ->join('HZ_CUST_SITE_USES_ALL hcsua', 'hcas.CUST_ACCT_SITE_ID','=','hcsua.CUST_ACCT_SITE_ID')
                    ->join('ar_customers ac','ac.customer_id', '=', 'hcas.cust_Account_id' )
                    ->whereIn('site_use_code', ['SHIP_TO','BILL_TO'])
                    /*->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                              ->from('ar_customers as ac')
                              ->whereRaw("ac.customer_id = hcas.cust_Account_id")
                              ->wherein('customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','OUTLET','EXPORT','TOLL IN']);
                            })*/
                    ->wherein('ac.customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','OUTLET','EXPORT','TOLL IN'])
                    ->where(function ($query) use($lasttime) {
                            $query->where('hcsua.last_update_date','>=',$lasttime)
                                  ->orwhere('hcas.last_update_date', '>=', $lasttime)
                                  ->orwhere('hps.last_update_date', '>=', $lasttime)
                                  ->orwhere('hl.last_update_date', '>=', $lasttime);
                        })
                    ->select('cust_account_id', 'hcas.cust_acct_site_id as cust_acct_site_id', 'hcas.party_site_id', 'bill_to_flag', 'ship_to_flag', 'ac.orig_system_reference', 'hcas.status as status', 'hcas.org_id as org_id'
                        , 'hcsua.SITE_USE_id as site_use_id'
                        , 'hcsua.site_use_code as site_use_code', 'hcsua.BILL_TO_SITE_USE_ID as bill_to_site_use_id'
                        , 'hcsua.payment_term_id as payment_term_id'
                        , 'hcsua.price_list_id as price_list_id'
                        , 'hcsua.order_type_id as order_type_id'
                        , 'hcsua.tax_code as tax_code'
                        ,  'hl.ADDRESS1', 'hl.address2 as kecamatan','hl.address3 as kelurahan', 'hl.address4 as wilayah'
                        ,  'hl.city', 'hl.province', 'hl.country'
                        , 'hcsua.WAREHOUSE_ID','hl.POSTAL_CODE','hcsua.primary_flag','ac.customer_number','ac.customer_name')
                    ->get();
        if(count($sites))
        {
          $sitesheet[]=['Customer Number','Customer Name','Site Use Code','Address','Province','City','Status'];
          /*echo "<h2>Data Customer Site Oracle</h2>";
          echo "<table><tr><th>Customer Number</th><th>Customer Name</th>";
          echo "<th>Site Use Code</th><th>Address</th><th>Province</th><th>City</th><th>Status</th></tr>";*/
          foreach ($sites as $site)
          {
              //echo "Sites:".$site->cust_account_id."<br>";
              /*echo "<tr>";
              echo "<td>".$site->customer_number."</td>";
              echo "<td>".$site->customer_name."</td>";
              echo "<td>".$site->site_use_code."</td>";
              echo "<td>".$site->address1."</td>";
              echo "<td>".$site->province."</td>";
              echo "<td>".$site->city."</td>";*/

              $province_id=null;
              $city_id=null;
              $desa_id=null;
              $kecamatan_id=null;
              $customer = Customer::where('oracle_customer_id','=',$site->cust_account_id)->first();
              $city =DB::table('regencies')->where('name','=',$site->city)->first() ;
              $provinces = DB::table('provinces')->where('name','=',$site->province)->first() ;
              if($city) $city_id = $city->id;
              if($provinces) $province_id = $provinces->id;
              $kecamatan = DB::table('districts')->whereRaw("upper(name)=upper('".addslashes($site->kecamatan)."') and ifnull('".$city_id."',regency_id)=regency_id")->first();
              if($kecamatan) $kecamatan_id = $kecamatan->id;
              $villages = DB::table('villages')->whereRaw("upper(name)=upper('".addslashes($site->kelurahan)."') and district_id like '".$city_id."%'")->first();
              if($villages)
              {
                $desa_id=$villages->id;
                if(is_null($kecamatan_id))
                {
                  $kecamatan_id = $villages->district_id;
                }
              }

              if($customer)
              {
                /*check apakah ada customer berasal dari register*/
                $oldcustsite = CustomerSite::where('customer_id','=',$site->orig_system_reference)
                              ->whereNull('oracle_customer_id')
                              ->where('primary_flag','=',$site->primary_flag)
                              ->where('site_use_code','=',$site->site_use_code)
                              ->where('city','=',$site->city)
                              ->where('province','=',$site->province)
                              ->first();
                if($oldcustsite)
                {
                  $oldcustsite->oracle_customer_id = $site->cust_account_id;
                  $oldcustsite->cust_acct_site_id = $site->cust_acct_site_id;
                  $oldcustsite->site_use_id = $site->site_use_id;
                  $oldcustsite->status = $site->status;
                  $oldcustsite->bill_to_site_use_id=$site->bill_to_site_use_id;
                  $oldcustsite->payment_term_id=$site->payment_term_id;
                  $oldcustsite->price_list_id=$site->price_list_id;
                  $oldcustsite->order_type_id=$site->order_type_id;
                  $oldcustsite->tax_code=$site->tax_code;
                  $oldcustsite->org_id = $site->org_id;
                  $oldcustsite->warehouse=$site->warehouse_id;
                  $oldcustsite->area=$site->wilayah;
                  $oldcustsite->save();
                  /*update soheader*/
                  if($oldcustsite->site_use_code=="SHIP_TO"){
                    DB::table('so_headers')->where('customer_id','=',$oldcustsite->customer_id)
                    ->where('cust_ship_to','=',$oldcustsite->cust_ship_to)
                    ->update(['price_list_id'=>$site->price_list_id
                              ,'payment_term_id'=>$site->payment_term_id
                              ,'oracle_ship_to'=>$site->site_use_id
                              ,'oracle_customer_id'=>$customer->oracle_customer_id]);
                  }elseif($oldcustsite->site_use_code=="BILL_TO"){
                    DB::table('so_headers')->where('customer_id','=',$oldcustsite->customer_id)
                    ->where('cust_bill_to','=',$oldcustsite->cust_ship_to)
                    ->update(['price_list_id'=>$site->price_list_id
                              ,'payment_term_id'=>$site->payment_term_id
                              ,'oracle_bill_to'=>$site->site_use_id
                              ,'oracle_customer_id'=>$customer->oracle_customer_id]);
                  }
                  $sitestatus = "merge";




              /*  }elseif($oldcustsite->count()>1){
                  $mycustomersite = CustomerSite::updateOrCreate(
                      ['customer_id','=',$site->orig_system_reference,'city'=>$site->city,'province'=>$site->province,'site_use_code'=>$site->site_use_code,'primary_flag'=>$site->primary_flag],
                      ['oracle_customer_id'=>$site->cust_account_id,'cust_acct_site_id'=>$site->cust_acct_site_id,'site_use_id'=>$site->site_use_id
                      ,'status'=>$site->status,'bill_to_site_use_id'=>$site->bill_to_site_use_id
                      ,'payment_term_id'=>$site->payment_term_id,'price_list_id'=>$site->price_list_id
                      ,'order_type_id'=>$site->order_type_id,'tax_code'=>$site->tax_code
                      ,'address1'=>$site->address1,'state'=>$site->kelurahan,'district'=>$site->kecamatan
                      ,'postal_code'=>$site->postal_code,'Country'=>$site->country
                      ,'org_id'=>$site->org_id,'warehouse'=>$site->warehouse_id
                      ,'city_id'=>$city_id,'province_id'=>$province_id,'district_id'=>$kecamatan_id,'state_id'=>$desa_id,'area'=>$site->wilayah
                      ]
                    );*/
                }else{
                  $mycustomersite = CustomerSite::updateOrCreate(
                    ['oracle_customer_id'=>$site->cust_account_id,'cust_acct_site_id'=>$site->cust_acct_site_id,'site_use_id'=>$site->site_use_id],
                    ['site_use_code'=>$site->site_use_code,'primary_flag'=>$site->primary_flag,'status'=>$site->status,'bill_to_site_use_id'=>$site->bill_to_site_use_id
                    ,'payment_term_id'=>$site->payment_term_id,'price_list_id'=>$site->price_list_id
                    ,'order_type_id'=>$site->order_type_id,'tax_code'=>$site->tax_code
                    ,'address1'=>$site->address1,'state'=>$site->kelurahan,'district'=>$site->kecamatan
                    ,'city'=>$site->city,'province'=>$site->province,'postal_code'=>$site->postal_code,'Country'=>$site->country
                    ,'org_id'=>$site->org_id,'warehouse'=>$site->warehouse_id,'customer_id'=>$customer->id
                    ,'city_id'=>$city_id,'province_id'=>$province_id,'district_id'=>$kecamatan_id,'state_id'=>$desa_id,'area'=>$site->wilayah
                    ]
                  );
                  $sitestatus = "tambah/insert";
                }
                $sitesheet[]=[$site->customer_number,$site->customer_name,$site->site_use_code,$site->address1,$site->province,$site->city,$sitestatus];
              //  echo "<td>Sites berhasil ditambah/update</td>";
                //echo "</tr>";
              }

          }
          //echo"</table>";
          return $sitesheet;
        }
      }else{
        return $sitesheet;
      }
    }

    public function getCustomerContacts($lasttime)
    {
      $sheetcontact = [];
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $contacts = $connoracle->table('hz_cust_accounts hca')
                    ->join('hz_parties obj','hca.party_id', '=', 'obj.party_id')
                    ->join('hz_relationships rel','hca.party_id','=','rel.object_id')
                    ->join('hz_contact_points hcp', 'rel.party_id','=','hcp.owner_table_id')
                    ->join('hz_parties sub','rel.subject_id', '=', 'sub.party_id' )
                    ->where('rel.relationship_type','=','CONTACT')
                    ->where('rel.directional_flag','=','F')
                    ->where('hcp.owner_table_name','=','HZ_PARTIES')
                    ->where(function ($query) use($lasttime) {
                            $query->where('hcp.last_update_date','>=',$lasttime)
                                  ->orwhere('rel.last_update_date', '>=', $lasttime);
                        })
                    ->select('sub.party_id','hca.cust_account_id'
                             , 'account_number as customer_number', 'obj.party_name as customer_name'
                             , 'sub.party_name as contact_name' , 'hcp.contact_point_type'
                             ,  DB::raw("DECODE(hcp.contact_point_type, 'EMAIL', hcp.email_address
                                    , 'PHONE', hcp.phone_country_code||hcp.phone_area_code || '-' || hcp.phone_number
                                    , 'WEB'  , hcp.url
                                    , 'Unknow contact Point Type ' || hcp.contact_point_type
                                      ) as Contact")
                             , 'hCP.phone_line_type', 'hcp.CONTACT_POINT_PURPOSE','hcp.contact_point_id')
                    ->get();
        if(count($contacts))
        {
          $sheetcontact[]=['Customer Number','Customer Name','Contact Name','Contact Point Type','Contact','Line type','Status'];
        /*  echo "<h2>Data Customer Contact Oracle</h2>";
          echo "<table><tr><th>Customer Number</th><th>Customer Name</th>";
          echo "<th>Contact Name</th><th>Contact Point Type</th><th>Contact</th><th>Line type</th><th>Status</th></tr>";*/
          foreach ($contacts as $contact)
          {
              //echo "Sites:".$site->cust_account_id."<br>";
              /*echo "<tr>";
              echo "<td>".$contact->customer_number."</td>";
              echo "<td>".$contact->customer_name."</td>";
              echo "<td>".$contact->contact_name."</td>";
              echo "<td>".$contact->contact_point_type."</td>";
              echo "<td>".$contact->contact."</td>";
              echo "<td>".$contact->phone_line_type."</td>";*/

              $customer = Customer::where('oracle_customer_id','=',$contact->cust_account_id)->first();

              if($customer)
              {
                $mycustomersite = CustomerContact::updateOrCreate(
                  ['oracle_customer_id'=>$contact->cust_account_id,'customer_id'=>$customer->id,'contact'=>$contact->contact,'contact_point_id'=>$contact->contact_point_id],
                  ['account_number'=>$contact->customer_number,'contact_name'=>$contact->contact_name,'contact_type'=>$contact->contact_point_type
                  ]
                );
                $sheetcontact[]=[$contact->customer_number,$contact->customer_name,$contact->contact_name,$contact->contact_point_type,$contact->contact,$contact->phone_line_type,'insert/update'];
                /*echo "<td>Contact berhasil ditambah/update</td>";
                echo "</tr>";*/
              }

          }
          //echo"</table>";
          return $sheetcontact;
        }
      }else{
        return $sheetcontact;
      }
    }
    public function getShippingSO($notrx,$lineid,$lasttime, $productid,$headerid)
    {
      $connoracle = DB::connection('oracle');
      DB::enableQueryLog();
      if($connoracle){
        //$lasttime = date_create("2017-07-01");
        $oraship = $connoracle->table('wsh_delivery_Details as wdd')
                  ->join( 'wsh_Delivery_assignments as wda','wdd.delivery_detail_id','=','wda.delivery_detail_id')
                  ->join('wsh_new_deliveries as wnd','wda.delivery_id','=','wnd.delivery_id')
                  ->join('oe_order_lines_all as ola',function($join){
                      $join->on('wdd.source_header_id','=','ola.header_id');
                      $join->on('wdd.source_line_id','=','ola.line_id');
                  })
                  ->where([//['wdd.source_header_id','=',$headerid]
                          //,['wdd.source_line_id','=',$lineid]]
                          //,
                          ['wdd.source_code','=','OE']
                          ,[DB::raw('nvl(ola.attribute1,ola.orig_sys_document_ref)'),'=',$notrx]
                          ,[DB::raw('nvl(ola.attribute2,ola.orig_sys_line_ref)'),'=',strval($lineid)]
                          //,['wdd.last_update_date','>=',$lasttime]
                        ])
                  /*->where(function ($query) {
                              $query->whereNotNull('ola.attribute1')
                                    ->orWhere('ola.order_source_id','=',config('constant.order_source_id'));
                          })*/
                  ->select('wnd.name as delivery_no',  'wdd.source_header_id', 'wdd.source_line_id',  'wdd.delivery_detail_id','wdd.inventory_item_id'
                      , 'wdd.src_requested_quantity_uom', 'wdd.src_Requested_quantity'
                      , 'wdd.requested_quantity_uom as primary_uom', 'wdd.requested_quantity'
                      , 'wdd.picked_quantity'
                      , 'wdd.shipped_quantity'
                      , DB::raw('inv_convert.inv_um_convert(wdd.inventory_item_id,wdd.requested_quantity_uom,wdd.src_requested_quantity_uom) as convert_qty')
                      , 'wdd.lot_number'
                      , 'wdd.transaction_id'
                      , 'wdd.split_from_delivery_detail_id'
                      , DB::raw('(select mmt.transaction_date
                            from mtl_material_transactions mmt
                            where wdd.transaction_id =mmt.transaction_id
                            and wdd.inventory_item_id = mmt.inventory_item_id
                            and mmt.TRANSACTION_TYPE_ID=52) as transaction_date')
                      ,'wdd.inventory_item_id'
                      ,'wnd.waybill'
                      ,'wdd.released_status'
                    )
                  ->get();
            //var_dump($oraship);echo"<br>";
        if($oraship->count()>0)
        {
          //var_dump($oraship->pluck('delivery_no','delivery_detail_id')->toArray());echo"<br>";
          $deletedelivery=$oraship->pluck('delivery_no','delivery_detail_id')
                                  ->map(function ($item, $key) {
                                        return "$key-$item";
                                  })->toArray();
        //  echo "new deletedelivery<br>";
          var_dump($deletedelivery);
          $upd_so_ship = SoShipping::where('product_id','=',$productid)
                        ->where('line_id','=',$lineid)
                        ->where('header_id','=',$headerid)
                        ->whereNotIn(DB::raw("concat(delivery_detail_id,'-',deliveryno)"),$deletedelivery)
                        ->update(['qty_backorder'=>DB::raw('qty_request_primary'),'qty_shipping'=>0,'qty_accept'=>0]);
                      //  dd(DB::getQueryLog());
          foreach($oraship as $ship)
          {
            //echo "delivery detail id-delivery_no".$ship->delivery_detail_id."-".$ship->delivery_no."<br>";
            $my_so_ship = SoShipping::where('delivery_detail_id','=',$ship->delivery_detail_id)
              ->where('deliveryno','=',$ship->delivery_no)
              ->where('product_id','=',$productid)
              ->where('line_id','=',$lineid)
              ->where('header_id','=',$headerid)
              ->first();
            if($my_so_ship){
              if($ship->released_status=="C")/*closing*/
              {echo "update closing<br>";
                $my_so_ship->qty_backorder = intval($my_so_ship->qty_backorder)+$my_so_ship->qty_shipping - $ship->picked_quantity;
                $my_so_ship->qty_shipping = $ship->picked_quantity;
                $my_so_ship->batchno = $ship->lot_number;
                $my_so_ship->qty_accept = $ship->shipped_quantity;
                $my_so_ship->qty_shipconfirm = $ship->shipped_quantity;
                $my_so_ship->waybill=$ship->waybill;
                $my_so_ship->save();
              }elseif(is_null($my_so_ship->qty_accept)){
                //$productid = Product::where('inventory_item_id','=',$ship->inventory_item_id)->select('id')->first();
                $my_so_ship =SoShipping::updateOrCreate(
                  ['delivery_detail_id'=>$ship->delivery_detail_id,'deliveryno'=>$ship->delivery_no],
                  ['source_header_id'=>$ship->source_header_id
                  ,'source_line_id'=>$ship->source_line_id,'product_id'=>$productid
                  ,'uom'=>$ship->src_requested_quantity_uom,'qty_request'=>$ship->src_requested_quantity
                  ,'uom_primary'=>$ship->primary_uom,'qty_request_primary'=>$ship->requested_quantity
                  ,'qty_shipping'=>$ship->picked_quantity
                  ,'batchno'=>$ship->lot_number
                  ,'split_source_id'=>$ship->split_from_delivery_detail_id
                  ,'tgl_kirim'=>$ship->transaction_date
                  ,'conversion_qty'=>$ship->convert_qty
                  ,'header_id' =>$headerid
                  ,'line_id'=>$lineid
                  ,'waybill'=>$ship->waybill
                  ,'qty_shipconfirm'=>$ship->shipped_quantity
                  ]
                );
              }elseif($ship->waybill!=$my_so_ship->waybill){
                $my_so_ship->batchno = $ship->lot_number;
                $my_so_ship->waybill=$ship->waybill;
                $my_so_ship->save();
              }
            } else{
              $newsoship = new SoShipping;
              $newsoship->delivery_detail_id = $ship->delivery_detail_id;
              $newsoship->deliveryno = $ship->delivery_no;
              $newsoship->source_header_id = $ship->source_header_id;
              $newsoship->source_line_id = $ship->source_line_id;
              $newsoship->product_id = $productid;
              $newsoship->uom = $ship->src_requested_quantity_uom;
              $newsoship->qty_request = $ship->src_requested_quantity;
              $newsoship->uom_primary = $ship->primary_uom;
              $newsoship->qty_request_primary = $ship->requested_quantity;
              $newsoship->qty_shipping = $ship->picked_quantity;
              $newsoship->batchno = $ship->lot_number;
              $newsoship->split_source_id = $ship->split_from_delivery_detail_id;
              $newsoship->tgl_kirim = $ship->transaction_date;
              $newsoship->conversion_qty = $ship->convert_qty;
              $newsoship->header_id = $headerid;
              $newsoship->line_id = $lineid;
              $newsoship->waybill=$ship->waybill;
              $newsoship->save();
            }

          }
          //var_dump(DB::getQueryLog());
          return 1;
        }else{/*tidak ada di shipping so karena backorder full*/
          $my_so_ship = SoShipping::where('product_id','=',$productid)
            ->where('line_id','=',$lineid)
            ->where('header_id','=',$headerid)
            ->whereNull('qty_backorder')
            ->get();
          if($my_so_ship->count()>0){
            $upd_so_ship = SoShipping::where('product_id','=',$productid)
                          ->where('line_id','=',$lineid)
                          ->where('header_id','=',$headerid)
                          ->update(['qty_backorder'=>DB::raw('qty_request_primary'),'qty_shipping'=>0,'qty_accept'=>0]);
            return 1;
          }

        }
      }else{
        return 0;
      }
    }

    public function getSalesOrder($notrx, SoLine $line)
    {
      echo "notrx:".$notrx.", uom:".$line->uom."<br>";
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $oraSO=$connoracle->selectone("select ola.inventory_item_id, sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom."')) as ordered_quantity
                  , sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom_primary."')) as ordered_quantity_primary
                  , sum(ordered_quantity*unit_selling_price) as amount
                  , sum(ordered_quantity*unit_list_price) as unit_list_price
                  , sum(tax_value) tax_value
                from oe_order_headers_all oha
                    , oe_order_lines_all ola
                where oha.headeR_id=ola.header_id
                    and nvl(ola.attribute1,oha.orig_sys_document_ref) = '".$notrx."'
                    and nvl(ola.attribute2,ola.orig_sys_line_ref) = '".$line->line_id."'
                    and ola.line_category_code ='ORDER'
                    and nvl(oha.CANCELLED_FLAG,'N')='N'
                    and oha.flow_status_code in ('BOOKED')
                group by ola.inventory_item_id
                ");
                //and ola.inventory_item_id = '".$line->inventory_item_id."'
                //having sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom."')) <> 0

        if($oraSO)
        {
          return $oraSO;
        }else{
          echo "ga masuk oracle<br>";
          return null;
        }
      }
    }

    public function getadjustmentSO($bucket, $notrx, SoLine $line)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        if(is_null($bucket))
        {
          return $connoracle->select("select pricing_group_sequence,sum(adjusted_amount*inv_convert.inv_um_convert(ola.inventory_item_id,'".$line->uom."',ola.ORDER_QUANTITY_UOM)) as adjusted_amount, sum(operand) as operand
                                from oe_price_adjustments opa
                                    , oe_order_lines_all ola
                                    , oe_order_headers_all oha
                                where applied_flag='Y'
                                    and opa.line_id =ola.line_id
                                    and opa.header_id=oha.header_id
                                    and ola.header_id=oha.header_id
                                    and nvl(ola.attribute1,oha.orig_sys_document_ref) = '".$notrx."'
                                    and nvl(ola.attribute2,ola.ORIG_SYS_LINE_REF) = '$line->line_id'
                                    and ola.inventory_item_id = '$line->inventory_item_id'
                                    and list_line_type_code ='DIS'
                                    and modifier_level_code='LINE'
                                    and oha.FLOW_STATUS_CODE='BOOKED'
                                    and ola.line_category_code ='ORDER'
                                    and nvl(ola.CANCELLED_FLAG,'N')='N'
                                group by pricing_group_sequence
                                order by pricing_group_sequence");

        }else{
          return $connoracle->selectone("select pricing_group_sequence,,sum(adjusted_amount*inv_convert.inv_um_convert(ola.inventory_item_id,'".$line->uom."',ola.ORDER_QUANTITY_UOM)) as adjusted_amount, sum(operand) as operand
                                from oe_price_adjustments opa
                                    , oe_order_lines_all ola
                                    , oe_order_headers_all oha
                                where applied_flag='Y'
                                    and opa.line_id =ola.line_id
                                    and opa.header_id=oha.header_id
                                    and ola.header_id=oha.header_id
                                    and nvl(ola.attribute1,oha.orig_sys_document_ref) = '".$notrx."'
                                    and nvl(ola.attribute2,ola.ORIG_SYS_LINE_REF) = '$line->line_id'
                                    and ola.inventory_item_id = '$line->inventory_item_id'
                                    and list_line_type_code ='DIS'
                                    and modifier_level_code='LINE'
                                    and oha.FLOW_STATUS_CODE='BOOKED'
                                    and ola.line_category_code ='ORDER'
                                    and nvl(ola.CANCELLED_FLAG,'N')='N'
                                    and pricing_group_sequence=".$bucket."
                                group by pricing_group_sequence
                                order by pricing_group_sequence ");
        }

      }else{
        return null;
      }
    }

    public function getadjustmentHeaderSO($notrx,$headerid)
    {
      $connoracle = DB::connection('oracle');
      $newadjustment=false;
      if($connoracle){
        $oraSOheader = $connoracle->table('oe_order_lines_all as ola')
                      ->whereRaw( "ola.attribute1 = '".$notrx."'")
                      ->where('ola.booked_flag','=','Y')
                      ->select('ola.header_id')
                      ->groupBy('ola.header_id')
                      ->get();
        foreach($oraSOheader as $soheader)
        {
          echo "header id:".$soheader->header_id."<br>";
          $adjustmentso = $connoracle->table('oe_order_lines_all as ola')
                        ->join('mtl_system_items as msi',function($query1){
                            $query1->on('msi.inventory_item_id','=','ola.inventory_item_id')
                                    ->on('msi.organization_id','=','ola.ship_from_org_id');
                        })
                        ->where('header_id','=',$soheader->header_id)
                        ->where('ola.ORDERED_QUANTITY','!=',0)
                        ->whereNull('ola.attribute1')
                        ->whereNull('ola.attribute2')
                        ->where('ola.line_category_code','=','ORDER')
                      /*  ->whereExists(function($query){
                            $query->select(DB::raw(1))
                                  ->from('oe_price_adjustments as opa')
                                  ->whereRaw(' opa.header_id=ola.headeR_id and opa.line_id = ola.line_id');
                        })*/
                        ->select('ola.headeR_id', 'ola.line_id', 'ola.ORDERED_QUANTITY', 'ola.INVENTORY_ITEM_ID'
                                , 'ola.ORDERED_QUANTITY', 'ola.unit_list_price'
                                , 'ola.ORDER_QUANTITY_UOM', 'ola.unit_selling_price'
                                , DB::raw('inv_convert.inv_um_convert(ola.inventory_item_id,ola.ORDER_QUANTITY_UOM, msi.primary_uom_code ) as conversion')
                                ,'ola.tax_value','msi.primary_uom_code'
                              );

          $adjustmentso=$adjustmentso->get() ;
          foreach($adjustmentso as $soline)
          {
            echo "adjust oracle line id:".$soline->line_id;
            $adj_price = $connoracle->table('oe_price_adjustments as opa')
                        ->where('opa.header_id','=',$soline->header_id)
                        ->where('opa.line_id','=',$soline->line_id)
                        ->where('applied_flag','=','Y')
                        ->select('list_line_id')
                        ->first();
            if($adj_price) $list_line_id = $adj_price->list_line_id;else $list_line_id = -1;

            $product = DB::table('products')->where('inventory_item_id','=',$soline->inventory_item_id)->select('id')->first();
            $newline = SoLine::updateOrCreate(['bonus_list_line_id'=>$list_line_id
                                                ,'product_id'=>$product->id,
                                                'header_id'=>$headerid ],
                        [ 'uom'=> $soline->order_quantity_uom
                        ,'qty_request'=>$soline->ordered_quantity
                        ,'qty_confirm'=>$soline->ordered_quantity*$soline->conversion
                        ,'list_price'=>$soline->unit_list_price
                        , 'unit_price'=>$soline->unit_selling_price
                        ,'amount'=>$soline->unit_list_price*$soline->ordered_quantity
                        ,'tax_amount'=>$soline->tax_value
                        ,'oracle_line_id'=>$soline->line_id
                        ,'conversion_qty'=>$soline->conversion
                        ,'inventory_item_id'=>$soline->inventory_item_id
                        ,'uom_primary'=>$soline->primary_uom_code
                        ,'qty_request_primary'=>$soline->ordered_quantity*$soline->conversion
                        ]
                        );
            $updateoraline = $connoracle->table('oe_order_lines_all as ola')
                            ->where('ola.line_id','=',$soline->line_id)
                            ->update(['attribute1'=>$notrx,'attribute2'=>$newline->line_id]);
            $newadjustment =true;
          }
        }
        return $newadjustment;
      }else return $newadjustment;
    }

    public function getModifierSummary()
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
          echo "Connect to oracle<br>";
          $modifiers = $connoracle->table('qp_modifier_summary_v')
                      ->select(  'list_line_id','list_header_id','list_line_type_code','automatic_flag','modifier_level_code'
                        ,'list_price','list_price_uom_code','primary_uom_flag','inventory_item_id','organization_id'
                        ,'operand','arithmetic_operator','override_flag','print_on_invoice_flag','start_date_active'
                        ,'end_date_active','pricing_group_sequence','incompatibility_grp_code','list_line_no','product_precedence','pricing_phase_id'
                        ,'pricing_attribute_id','product_attribute_context','product_attr','product_attr_val'
                        ,'product_uom_code','comparison_operator_code','pricing_attribute_context','pricing_attr'
                        ,'pricing_attr_value_from','pricing_attr_value_to','pricing_attribute_datatype'
                        ,'product_attribute_datatype')
                      ->get();
          if($modifiers)
          {
            foreach ($modifiers as $m){
              $modifier = qp_modifier_summary::updateOrCreate(['list_line_id'=>$m->list_line_id, 'list_header_id'=>$m->list_header_id],
                ['list_line_type_code'=>$m->list_line_type_code,'automatic_flag'=>$m->automatic_flag,'modifier_level_code'=>$m->modifier_level_code
                ,'list_price'=>$m->list_price,'list_price_uom_code'=>$m->list_price_uom_code,'primary_uom_flag'=>$m->primary_uom_flag
                ,'inventory_item_id'=>$m->inventory_item_id,'organization_id'=>$m->organization_id,'operand'=>$m->operand
                ,'arithmetic_operator'=>$m->arithmetic_operator,'override_flag'=>$m->override_flag
                ,'print_on_invoice_flag'=>$m->print_on_invoice_flag,'start_date_active'=>$m->start_date_active
                ,'end_date_active'=>$m->end_date_active,'pricing_group_sequence'=>$m->pricing_group_sequence,'incompatibility_grp_code'=>$m->incompatibility_grp_code
                ,'list_line_no'=>$m->list_line_no,'product_precedence'=>$m->product_precedence
                ,'pricing_phase_id'=>$m->pricing_phase_id,'pricing_attribute_id'=>$m->pricing_attribute_id
                ,'product_attribute_context'=>$m->product_attribute_context,'product_attr'=>$m->product_attr
                ,'product_attr_val'=>$m->product_attr_val,'product_uom_code'=>$m->product_uom_code
                ,'comparison_operator_code'=>$m->comparison_operator_code,'pricing_attribute_context'=>$m->pricing_attribute_context
                ,'pricing_attr'=>$m->pricing_attr,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                ,'pricing_attr_value_to'=>$m->pricing_attr_value_to,'pricing_attribute_datatype'=>$m->pricing_attribute_datatype
                ,'product_attribute_datatype'=>$m->product_attribute_datatype
                ]
              );
              echo "Modifier: ".$m->list_line_id." berhasil ditambah/update<br>";
            }

          }
      }
    }

    public function getQualifiers()
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $qualifiers = $connoracle->table('qp_qualifiers_v')
                      ->select('qualifier_id','excluder_flag','comparision_operator_code','qualifier_context','qualifier_attribute'
                      ,'qualifier_grouping_no','qualifier_attr_value','list_header_id','list_line_id','start_date_active'
                      ,'end_date_active','qualifier_datatype','qualifier_precedence')
                      ->get();
        if($qualifiers)
        {
          echo "Connect to oracle<br>";
          foreach ($qualifiers as $q){
            $qualifier = qp_qualifiers::updateOrCreate(['qualifier_id'=>$q->qualifier_id],
              ['excluder_flag'=>$q->excluder_flag,'comparision_operator_code'=>$q->comparision_operator_code
              ,'qualifier_context'=>$q->qualifier_context,'qualifier_attribute'=>$q->qualifier_attribute
              ,'qualifier_grouping_no'=>$q->qualifier_grouping_no,'qualifier_attr_value'=>$q->qualifier_attr_value
              ,'list_header_id'=>$q->list_header_id,'list_line_id'=>$q->list_line_id
              ,'start_date_active'=>$q->start_date_active,'end_date_active'=>$q->end_date_active
              ,'qualifier_datatype'=>$q->qualifier_datatype,'qualifier_precedence'=>$q->qualifier_precedence
              ]
            );
            echo "qualifier: ".$q->qualifier_id." berhasil ditambah/update<br>";
          }

        }
      }
    }

    public function updateDiskonTable($tglskrg=null)
    {
      if(is_null($tglskrg))
      {
          $tglskrg =date('Y-m-d');
      }
      $insertprice = $this->getPricelist();
      if($insertprice==1){

        $listheader = QpListHeaders::whereIn('list_type_code',['DLT','PRO'])
                    ->whereRaw("'".$tglskrg."' between ifnull(start_date_active,date('2017-01-01'))
              and ifnull(end_date_active,DATE_ADD('".$tglskrg."',INTERVAL 1 day))")
               //->where('list_header_id','=',22289)
                ->get();

        foreach ($listheader as $priceheader)
        {
          if(is_null($priceheader->start_date_active))
          {
            $tglawheader=date_create('2017-01-01');
          }else{
            $tglawheader = $priceheader->start_date_active;
          }
          if(is_null($priceheader->end_date_active))
          {
            $tglakheader=date_create(date('Y').'-12-31');
          }else{
            $tglakheader = $priceheader->end_date_active;
          }

          $listdiskon = qp_modifier_summary::where('list_header_id','=',$priceheader->list_header_id)
                          ->whereRaw("'".$tglskrg."' between ifnull(start_date_active,date('2017-01-01'))
                                  and ifnull(end_date_active,DATE_ADD('".$tglskrg."',INTERVAL 1 day))")
                          ->where('product_attr', '=','PRICING_ATTRIBUTE1')
                          ->where('list_line_type_code','=','DIS')
                        //  ->where('list_line_id','=',23301)
                          ->orderBy('list_header_id','asc')
                          ->orderBy('list_line_id','asc')
                          ->orderBy('pricing_group_sequence','asc')
                          ->get();
          foreach($listdiskon as $diskon)
          {
            $a=$diskon->list_line_id;
            echo ('line_id:'.$a);
            $qualifierlist = qp_qualifiers::where('list_header_id','=',$diskon->list_header_id)
                            ->where(function ($query) use ($a) {
                                  $query->where('list_line_id', '=', $a)
                                        ->orWhere('list_line_id', '=', -1);
                              })
                              ->whereRaw("'".$tglskrg."' between ifnull(start_date_active,date('2017-01-01'))
                                      and ifnull(end_date_active,DATE_ADD('".$tglskrg."',INTERVAL 1 day))")
                            ;


            if($qualifierlist->get())
            {
              /*customer id condition*/
              $customerlist = $qualifierlist->where('qualifier_context','=','customer')
                ->where('qualifier_attribute','=','QUALIFIER_ATTRIBUTE2')
                ->get();
              if($customerlist)
              {
                foreach ($customerlist as $cust)
                {

                  QpPricingDiskon::updateorCreate(
                    ['list_header_id'=>$diskon->list_header_id
                    , 'list_line_id'=>$diskon->list_line_id
                    ,'list_line_no' =>$diskon->list_line_no
                    , 'item_id'=>$diskon->product_attr_val
                    , 'customer_id'=>$cust->qualifier_attr_value]
                    ,[
                    'list_line_type_code'  =>$diskon->list_line_type_code
                    ,'modifier_level_code'=>$diskon->modifier_level_code
                    ,'operand'=>$diskon->operand
                    ,'arithmetic_operator_code'=>$diskon->arithmetic_operator_code
                    ,'start_date_active'=>$tglawheader
                    ,'end_date_active'=>$tglakheader
                    ,'uom_code'=>$diskon->product_uom_code
                    ,'comparison_operator_code'=>$diskon->comparison_operator_code
                    ,'pricing_attribute_context'=>$diskon->pricing_attribute_context
                    ,'pricing_attr'=>$diskon->pricing_atr
                    ,'pricing_attr_value_from'=>$diskon->pricing_attr_value_from
                    ,'pricing_attr_value_to'=>$diskon->pricing_attr_value_to
                    ]
                  );
                }
              }
            }
          }
        }
      }else{
        echo "Tidak ada data yang baru";
      }


    }

    public function getMasterItem($tglskrg)
    {
      $connoracle = DB::connection('oracle');
      $sheetproduct=[];
      if($connoracle){
        $master_products = $connoracle->table('mtl_system_items as msi')
            ->where('customer_order_enabled_flag','=','Y')
->where('segment1','like','4%')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('mtl_parameters as mp')
                      ->whereRaw(' master_organization_id = msi.organization_id');
            })
            ->where('last_update_date','>=',$tglskrg)
            ->select('inventory_item_id', 'organization_id', 'segment1', 'description',  'primary_uom_code', 'secondary_uom_code'
                      ,DB::raw("inv_convert.inv_um_convert(msi.inventory_item_id,msi.secondary_uom_code, msi.primary_uom_code) as conversion")
                      , 'enabled_flag'
                      , 'attribute1')
            ->orderBy('segment1')->get();

        if($master_products){
          $insert_flag=false;
          $sheetproduct[]=['itemcode','description','satuan_primary','satuan_secondary','conversion','enabled_flag','status'];
            foreach($master_products as $mp)
            {
              //echo ('Product:'.$mp->segment1."<br>");
              $query1 = Product::where('inventory_item_id','=',$mp->inventory_item_id)->first();
              if($query1){//update
                $update = Product::updateOrCreate(
                       ['inventory_item_id'=>$mp->inventory_item_id],
                       ['title'=>$mp->description
                        ,'itemcode'=>$mp->segment1
                        ,'satuan_primary'=>$mp->primary_uom_code
                        ,'satuan_secondary'=>$mp->secondary_uom_code
                        ,'conversion'=>$mp->conversion
                        ,'enabled_flag'=>$mp->enabled_flag
                      ]);
                $sheetproduct[]=[$mp->segment1,$mp->description,$mp->primary_uom_code,$mp->secondary_uom_code,$mp->conversion,$mp->enabled_flag,'update'];
              }else{//insert
                $insert = Product::Create(
                      ['inventory_item_id'=>$mp->inventory_item_id
                        ,'title'=>$mp->description
                        ,'itemcode'=>$mp->segment1
                        ,'satuan_primary'=>$mp->primary_uom_code
                        ,'satuan_secondary'=>$mp->secondary_uom_code
                        ,'conversion'=>$mp->conversion
                        ,'enabled_flag'=>$mp->enabled_flag
                      ]);
                $sheetproduct[]=[$mp->segment1,$mp->description,$mp->primary_uom_code,$mp->secondary_uom_code,$mp->conversion,$mp->enabled_flag,'insert'];
                $insert_flag =true;
              }
            }
		  DB::commit();
            /*if($insert_flag)
            {
              //notif ke sysadmin

            }*/
            return $sheetproduct;
        }

      }
    }

    public function getMasterDiscount($tglskrg=null,$bgprocess=false){
      $sheetarray=[];
      if(is_null($tglskrg))
      {
        $request= DB::table('tbl_request')->where('event','=','synchronize')
                  ->max('created_at');
        if($request)
        {
          $tglskrg = date_create($request);
          //echo"type:".gettype($lasttime);
        }else{
          $tglskrg = date_create("2017-01-01");
        }
      }
      $vprice = $this->getPricelist($tglskrg,true);
      if(count($vprice)>0) $sheetarray = array_merge($sheetarray,$vprice);
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $datadiskon=[];
        $connoracle->enableQueryLog();
	/*delete data diskon yg manual*/
	$listheader2 = $connoracle->table('qp_list_headers')
                    ->where('automatic_flag','=','N')
                    ->select('list_header_id')
                    ->get();
	 if($listheader2->count()>0)
        {
	   $delqlh = QpListHeaders::whereIn('list_header_id',$listheader2->pluck('list_header_id')->toArray())
                    ->delete();
          $delqdiskon = QpPricingDiskon::whereIn('list_header_id',$listheader2->pluck('list_header_id')->toArray())
                    ->delete();
	 }
        /*delete data yg end_date_active berakhir*/
        $listheader = $connoracle->table('qp_list_headers')
                    ->where('list_type_code','!=','PRL')
                    ->whereraw('nvl(end_date_active,sysdate+1) < trunc(sysdate)')
                    ->whereExists(function($query){
                      $query->select(DB::raw(1))
                        ->from('qp_qualifiers as qq')
                        ->whereraw("qq.list_header_id =qp_list_headers.list_header_id and sysdate > nvl(qq.end_date_Active,sysdate+1)");
                    })
                    ->select('list_header_id')
                    ->get();
        if($listheader->count()>0)
        {
          $delqlh = QpListHeaders::whereIn('list_header_id',$listheader->pluck('list_header_id')->toArray())
                    ->delete();
          $delqdiskon = QpPricingDiskon::whereIn('list_header_id',$listheader->pluck('list_header_id')->toArray())
                    ->delete();
        }
	//echo "insert pricing diskon:<br>";
	 /*insert pricing diskon*/
        $modifiers = $connoracle->table('gpl_pricing_diskon_v')
                      ->whereraw('nvl(end_date_active,sysdate+1)>= trunc(sysdate)')//->whereraw('list_header_id=13094')
			->whereNotIn('list_header_id', $listheader->pluck('list_header_id')->toArray())
			->where('automatic_flag','=','Y')
			->whereNotNull('customer_number')
                      ->whereraw("last_update_date>=to_date('".date_format($tglskrg,'Y-m-d')."','rrrr-mm-dd')")
                      ->get();
       // dd($modifiers);
        if($modifiers){
          //echo "<table><caption>Data Diskon</caption>";
          //echo "<tr><th>Price Name</th><th>Product</th><th>Customer</th><th>Operand</th><th>Status</th></tr>";
          $datadiskon[]=['Price Name','Product','Customer','Operand','Status'];
          foreach($modifiers as $m)
          {
            $product_name =null;
            $product = Product::where('inventory_item_id','=',$m->product_attr_val)->first();
            if($product) $product_name = $product->title;
            //echo "<tr>";
            //echo "<td>".$m->name."-".$m->list_header_id."</td>";
            //echo "<td>".$product_name."</td>";
            $customer=collect([]);
            if(!is_null($m->customer_number))
              $customer = Customer::where('oracle_customer_id','=',$m->customer_number)->select('customer_name','customer_number')->first();
            elseif(!is_null($m->ship_to))
              $customer = Customer::join('customer_sites as cs','cs.customer_id','customers.id')
                          ->where('site_use_id','=',$m->ship_to)->select('customer_name','customer_number')->first();
            elseif(!is_null($m->bill_to))
            $customer = Customer::join('customer_sites as cs','cs.customer_id','customers.id')
                        ->where('site_use_id','=',$m->bill_to)->select('customer_name','customer_number')->first();

            if($customer)
            {
              $nmcustomer = $customer->customer_number."-".$customer->customer_name;
              //echo "<td>".$customer->customer_number."-".$customer->customer_name."</td>";
            }else $nmcustomer="";//echo "<td></td>";

            //echo "<td>".$m->operand."</td>";
            $olddiscount =  QpPricingDiskon::where([
              ['list_header_id','=',$m->list_header_id],
              ['list_line_id','=',$m->list_line_id],
              ['item_id','=',$m->product_attr_val]
            ]);
            if(!is_null($m->customer_number)) $olddiscount = $olddiscount->where('customer_id',$m->customer_number);
            if(!is_null($m->ship_to)) $olddiscount = $olddiscount->where('ship_to_id',$m->ship_to);
            if(!is_null($m->bill_to)) $olddiscount = $olddiscount->where('bill_to_id',$m->bill_to);
            //if(!is_null($m->product_attr_val))  $olddiscount = $olddiscount->where('item_id',$m->product_attr_val);

            $old_discount = $olddiscount->get();
            if($old_discount->count()==1)
            {
              $updatediscount = $olddiscount->update([
                'list_line_type_code'  =>$m->list_line_type_code
                ,'list_line_no' =>$m->list_line_no
                ,'modifier_level_code'=>$m->modifier_level_code
                ,'operand'=>$m->operand
                ,'arithmetic_operator_code'=>$m->arithmetic_operator
                ,'start_date_active'=>$m->start_date_active
                ,'end_date_active'=>$m->end_date_active
                ,'uom_code'=>$m->product_uom_code
                ,'comparison_operator_code'=>$m->comparison_operator_code
                ,'pricing_attribute_context'=>$m->pricing_attribute_context
                ,'pricing_attr'=>$m->pricing_attr
                ,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                ,'pricing_attr_value_to'=>$m->pricing_attr_value_to
                ,'pricing_group_sequence'=>$m->pricing_group_sequence
                ,'orig_org_id'=>$m->orig_org_id
                ,'price_break_type_code'=>$m->price_break_type_code
              ]);
              $status="update";
              //echo "<td>update</td>";
            }elseif($old_discount->count()==0){
              /*insert*/
              $updatediscount = QpPricingDiskon::insert([
                'list_header_id'=>$m->list_header_id
                ,'list_line_id'=> $m->list_line_id
                ,'item_id'=> $m->product_attr_val
                ,'customer_id' => $m->customer_number
                ,'ship_to_id'=>$m->ship_to
                ,'bill_to_id'=>$m->bill_to
                ,'list_line_type_code'  =>$m->list_line_type_code
                ,'list_line_no' =>$m->list_line_no
                ,'modifier_level_code'=>$m->modifier_level_code
                ,'operand'=>$m->operand
                ,'arithmetic_operator_code'=>$m->arithmetic_operator
                ,'start_date_active'=>$m->start_date_active
                ,'end_date_active'=>$m->end_date_active
                ,'uom_code'=>$m->product_uom_code
                ,'comparison_operator_code'=>$m->comparison_operator_code
                ,'pricing_attribute_context'=>$m->pricing_attribute_context
                ,'pricing_attr'=>$m->pricing_attr
                ,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                ,'pricing_attr_value_to'=>$m->pricing_attr_value_to
                ,'pricing_group_sequence'=>$m->pricing_group_sequence
                ,'orig_org_id'=>$m->orig_org_id
                ,'price_break_type_code'=>$m->price_break_type_code
              ]);
              //echo "<td>insert</td>";
              $status = "insert";
            }else{
              /*duplicate*/
              //echo "<td>duplicate</td>";
              $status="duplicate";
            }
            $datadiskon[]=[$m->name."-".$m->list_header_id,$product_name,$nmcustomer,$m->operand,$status];
            //echo"</tr>";
          }//echo "</table>";
        }
        if(count($datadiskon)>1) $sheetarray['diskon'] = $datadiskon;
        $del_line_diskon = QpPricingDiskon::whereraw("ifnull(end_date_active,curdate()+interval 1 day) < curdate()")
                          ->delete();
        $getpromo = $this->getPromoBonus($tglskrg);
        if(count($getpromo)>0) $sheetarray = array_merge($sheetarray,$getpromo);
      	DB::commit();
        if($bgprocess) return $sheetarray;
        else{
          if(count($sheetarray)>0){
          $file = Excel::create("sync_diskon".date('Ymd His'),function($excel) use ($sheetarray) {
              $excel->setTitle('Interface Diskon');
              $excel->setCreator('Shanty')
              ->setCompany('Solinda');
              foreach($sheetarray as $key=>$vArray)
              {
                if(count($vArray)>1){
                  $excel->sheet($key, function($sheet) use ($vArray) {
                    $sheet->fromArray($vArray, null, 'A1', true,false);
                    $sheet->row(1, function($row) {
                      $row->setBackground('#6495ED');
                      $row->setFontWeight('bold');
                      $row->setAlignment('center');
                    });
                  });
                }
              }
            })->download('xlsx');
          }else{
            echo "lasttime:".date_format($tglskrg,"Y/m/d H:i:s")."<br>";
            echo "Tidak ada data yang diproses<br>";
          }
        }
      }else {
      	echo "can't connect to oracle";
      	return $sheetarray;
      }
    }

    public function getPromoBonus($tglskrg=null)
    {
      $tglskrg = date_create("2018-01-01");
      $sheetarray=[];
      $databonus=[];
      DB::beginTransaction();
DB::connection()->enableQueryLog();
      try{
        /*getPromo*/
        $connoracle = DB::connection('oracle');
        $connoracle->enableQueryLog();
        $qp_bonus = $connoracle->table('qp_list_headers as qlh')
                    ->join('qp_modifier_summary_v as qms','qlh.list_header_id','qms.list_header_id')
                    ->whereraw("qlh.list_type_code='PRO'")
		      ->where('qlh.automatic_flag','Y')
//->whereraw("qlh.list_header_id in (252153,253119,253132) ")
                    ->whereraw("sysdate between nvl(qlh.start_date_active,'01-jan-2017') and nvl(qlh.end_date_Active,sysdate+1)")
                    ->whereraw("sysdate between nvl(qms.start_date_active,'01-jan-2017') and nvl(qms.end_date_Active,sysdate+1)")
                    ->wherenotexists(function($query){
                      $query->select(DB::raw(1))
                        ->from('qp_qualifiers as qq')
                        ->whereraw("qq.list_header_id =qms.list_header_id
                                and (qq.list_line_id =qms.list_line_id or qq.list_line_id=-1)
                                and sysdate > nvl(qq.end_date_Active,sysdate+1)");
                    });
                    $tmpqpbonus = $qp_bonus;

        $qp_bonus=$qp_bonus->where(function ($query) use($tglskrg) {
                $query->where('qlh.last_update_date','>=',$tglskrg)
                      ->orwhere('qms.last_update_date', '>=', $tglskrg);
                })->select('qlh.name', 'qms.list_header_id', 'qms.list_line_no','qms.list_line_id', 'list_line_type_code', 'modifier_level_code',
                               'qms.product_attr_val', 'qms.operand', 'qms.arithmetic_operator'
                              ,DB::raw("LEAST (NVL (qlh.start_date_active, qms.start_date_active),
                                           NVL (qms.start_date_active, qlh.start_date_active)
                                          ) as start_date_active"),
                                    DB::raw("LEAST (NVL (qlh.end_date_active, qms.end_date_active),
                                           NVL (qms.end_date_active, qlh.end_date_active)
                                          ) as end_date_active"),
                                    'qms.product_uom_code', 'qms.comparison_operator_code',
                                    'qms.pricing_attribute_context', 'qms.pricing_attr',
                                    'qms.pricing_attr_value_from', 'qms.pricing_attr_value_to',
                                    'qms.pricing_group_sequence', 'qlh.orig_org_id',
                                    'qms.price_break_type_code',
                                    DB::raw("GREATEST (qlh.last_update_date,
                                              qms.last_update_date
                                             ) AS last_update_date")
                              )
                    ->orderBy('qms.list_header_id','qms.list_line_id')->get();
                    /*delete pricing diskon yang sudah tidak berlaku*/
                    $exclude =  $tmpqpbonus->select( 'qms.list_header_id','qms.list_line_id')->groupBy('qms.list_header_id','qms.list_line_id')->get();
                    //dd($exclude);
                    if($exclude->count()>0) {
                      $deletelist=$exclude->pluck('list_header_id','list_line_id')
                                              ->map(function ($item, $key) {
                                                    return "$item-$key";
                                              })->toArray();
                      //var_dump($deletelist);
                      $delete = QpPricingDiskon::where('list_line_type_code','=','PRG')
                      ->whereNotIn(DB::raw("concat(list_header_id,'-',list_line_id)"),$deletelist)
                      ->delete();
                    }
        if($qp_bonus->count()>0)
        {
          $databonus[]=['Price Name','Product','Customer','Operand','Status'];
          //echo "<table><caption>Data Bonus</caption>";
          //echo "<tr><th>Price Name</th><th>Product</th><th>Customer</th><th>Operand</th><th>Status</th></tr>";
          /*kondisi untuk qualifier header terlebih dahulu*/
          $customer = Customer::where('status','A')->whereNotNull('oracle_customer_id');
          //dd($qp_bonus->groupBy('list_header_id'));
          foreach($qp_bonus->groupBy('list_header_id') as $headerkey=>$header)
          {
            $getqualifier =$connoracle->table("qp_qualifiers as qq")
                            ->where('list_header_id','=',$headerkey)
                            ->whereraw("list_line_id =-1")
                            ->whereraw("active_flag = 'Y' and qualifier_context ='CUSTOMER'")
                            ->select('qualifier_grouping_no', 'qualifier_attribute', 'qualifier_attr_value'
                              , 'comparison_operator_code' ,'qual_attr_value_from_number', 'qual_attr_value_to_number')
                            ->orderBy('qualifier_grouping_no')
                            ->get();
                  //  dd($connoracle->getQueryLog());
            if($getqualifier->count()>0){
              $mustcondition = $getqualifier->where('qualifier_grouping_no',-1);
              foreach($mustcondition  as $mc)
              {
                $customer =$customer->where(function($query) use($mc){
                  $query = $this->getCondition($mc,$query,'and');
                });
              }
              $getqualifier = $getqualifier->where('qualifier_grouping_no','!=',-1);
              $customer =$customer->where(function($query1) use($getqualifier){
                  foreach($getqualifier->groupBy('qualifier_grouping_no') as $key=>$grouping_no){
                    /*setiap grouping no dipisahkan dengan or*/
                      $query1 = $query1->orwhere(function($query2) use ($grouping_no){
                        foreach($grouping_no as $group){/*untuk sama grouping no pake and */
                          $query2 = $this->getCondition($group,$query2,'and');
                        }
                      });
                  }
              });

            }
            /*setiap item bonus*/
            foreach($qp_bonus->where('list_header_id',$headerkey) as $bonus)
            {
              $product_name =null;
              $product = Product::where('inventory_item_id','=',$bonus->product_attr_val)->first();
              if($product) $product_name = $product->title;
              $customerlines =$customer;
                /*getQualifier lines */
              $getlinequalifier =$connoracle->table("qp_qualifiers as qq")
                              ->where('list_header_id','=',$bonus->list_header_id)
                              ->where("list_line_id",'=',$bonus->list_line_id)
                              ->whereraw("active_flag = 'Y' and qualifier_context ='CUSTOMER'")
                              ->select('qualifier_grouping_no', 'qualifier_attribute', 'qualifier_attr_value'
                                , 'comparison_operator_code' ,'qual_attr_value_from_number', 'qual_attr_value_to_number')
                              ->orderBy('qualifier_grouping_no')
                              ->get();
              if($getlinequalifier->count()>0)
              {
                $mustcondition = $getqualifier->where('qualifier_grouping_no',-1);
                foreach($mustcondition  as $mc)
                {
                  $customer =$customerlines->where(function($query) use($mc){
                    $query = $this->getCondition($mc,$query,'and');
                  });
                }
                $getqualifier = $getqualifier->where('qualifier_grouping_no','!=',-1);
                $customerlines =$customerlines->where(function($query1) use($getqualifier){
                    foreach($getqualifier->groupBy('qualifier_grouping_no') as $key=>$grouping_no){
                      /*setiap grouping no dipisahkan dengan or*/
                        $query1 = $query1->orwhere(function($query2) use ($grouping_no){
                          foreach($grouping_no as $group){/*untuk sama grouping no pake and */
                            $query2 = $this->getCondition($group,$query2,'and');
                          }
                        });
                    }
                });
              }
              $customerlines=$customerlines->select('oracle_customer_id','customer_name','customer_number','id')
                      ->get();
              //print_r($customer->getBindings() );
//print_r($connoracle->getQueryLog());

              //dd(DB::getQueryLog());
              //dd($customerlines);
              if($customerlines->count()>0){
                foreach($customerlines as $cl)
                {
                  /*echo "<tr>";
                  echo "<td>".$bonus->name."-".$bonus->list_header_id."</td>";
                  echo "<td>".$product_name."</td>";
                  echo "<td>".$cl->customer_name."</td>";
                  echo "<td>".$bonus->pricing_attr_value_from." ".$bonus->product_uom_code."(".$bonus->price_break_type_code.")</td>";*/

                  $olddiscount =  QpPricingDiskon::where([
                    ['list_header_id','=',$bonus->list_header_id],
                    ['list_line_id','=',$bonus->list_line_id],
                    ['item_id','=',$bonus->product_attr_val],
                    ['customer_id','=',$cl->oracle_customer_id]
                  ]);
                  $old_discount = $olddiscount->get();
                  if($old_discount->count()==1)
                  {
                    $updatediscount = $olddiscount->update([
                      'list_line_type_code'  =>$bonus->list_line_type_code
                      ,'list_line_no' =>$bonus->list_line_no
                      ,'modifier_level_code'=>$bonus->modifier_level_code
                      ,'operand'=>$bonus->operand
                      ,'arithmetic_operator_code'=>$bonus->arithmetic_operator
                      ,'start_date_active'=>$bonus->start_date_active
                      ,'end_date_active'=>$bonus->end_date_active
                      ,'uom_code'=>$bonus->product_uom_code
                      ,'comparison_operator_code'=>$bonus->comparison_operator_code
                      ,'pricing_attribute_context'=>$bonus->pricing_attribute_context
                      ,'pricing_attr'=>$bonus->pricing_attr
                      ,'pricing_attr_value_from'=>$bonus->pricing_attr_value_from
                      ,'pricing_attr_value_to'=>$bonus->pricing_attr_value_to
                      ,'pricing_group_sequence'=>$bonus->pricing_group_sequence
                      ,'orig_org_id'=>$bonus->orig_org_id
                      ,'price_break_type_code'=>$bonus->price_break_type_code
                    ]);
                    $status="update";
                  }elseif($old_discount->count()==0){
                    /*insert*/
                    $updatediscount = QpPricingDiskon::insert([
                      'list_header_id'=>$bonus->list_header_id
                      ,'list_line_id'=> $bonus->list_line_id
                      ,'item_id'=> $bonus->product_attr_val
                      ,'customer_id' => $cl->oracle_customer_id
                      ,'ship_to_id'=>null
                      ,'bill_to_id'=>null
                      ,'list_line_type_code'  =>$bonus->list_line_type_code
                      ,'list_line_no' =>$bonus->list_line_no
                      ,'modifier_level_code'=>$bonus->modifier_level_code
                      ,'operand'=>$bonus->operand
                      ,'arithmetic_operator_code'=>$bonus->arithmetic_operator
                      ,'start_date_active'=>$bonus->start_date_active
                      ,'end_date_active'=>$bonus->end_date_active
                      ,'uom_code'=>$bonus->product_uom_code
                      ,'comparison_operator_code'=>$bonus->comparison_operator_code
                      ,'pricing_attribute_context'=>$bonus->pricing_attribute_context
                      ,'pricing_attr'=>$bonus->pricing_attr
                      ,'pricing_attr_value_from'=>$bonus->pricing_attr_value_from
                      ,'pricing_attr_value_to'=>$bonus->pricing_attr_value_to
                      ,'pricing_group_sequence'=>$bonus->pricing_group_sequence
                      ,'orig_org_id'=>$bonus->orig_org_id
                      ,'price_break_type_code'=>$bonus->price_break_type_code
                    ]);
                    //echo "<td>insert</td>";
                    $status="insert";
                  }
                  //echo "</tr>";
                  $databonus[]=[$bonus->list_header_id,$bonus->name,$product_name,$cl->customer_name,$bonus->pricing_attr_value_from." ".$bonus->product_uom_code."(".$bonus->price_break_type_code.")",$status];
                }

                /*delete pricing yang todal ada dalam kondisi*/
                $delete =QpPricingDiskon::where([
                  ['list_header_id','=',$bonus->list_header_id],
                  ['list_line_id','=',$bonus->list_line_id],
                  ['item_id','=',$bonus->product_attr_val]
                ])->whereNotIn('customer_id',$customerlines->pluck('oracle_customer_id')->toArray())
                ->delete();
              }

            }
            //echo "</table>";

          }
        }
        if(count($databonus)>0) $sheetarray['bonus']=$databonus;
        //$attrarray=[];
        /*insert qp_pricing_attr_get_v*/
        $priceheader = QpListHeaders::whereraw("ifnull(end_date_active,curdate()+interval 1 day) > curdate()")
                    ->whereraw("list_type_code='PRO'")
                    ->select('list_header_id')->get();
        if($priceheader->count()>0)
        {
          $pricing_attr = $connoracle->table('qp_pricing_attr_get_v')
                        ->whereraw("last_update_date>=to_date('".date_format($tglskrg,'Y-m-d')."','rrrr-mm-dd')")
                        ->whereIn('list_header_id',$priceheader->pluck('list_header_id')->toArray())
                        ->select('pricing_attribute_id', 'creation_date', 'last_update_date', 'list_line_id'
                        , 'excluder_flag', 'product_attribute_context', 'product_attribute', 'product_attr_value', 'product_uom_code'
                        , 'pricing_attribute_datatype', 'product_attribute_datatype', 'list_header_id', 'list_line_no', 'list_line_type_code', 'arithmetic_operator', 'operand', 'benefit_limit'
                        , 'benefit_uom_code', 'automatic_flag', 'modifier_level_code', 'pricing_phase_id', 'benefit_price_list_line_id', 'benefit_qty', 'override_flag', 'rltd_modifier_grp_type'
                        , 'rltd_modifier_id', 'rltd_modifier_grp_no',  'parent_list_line_id', 'to_rltd_modifier_id')
                        ->get();
          if($pricing_attr->count()>0){
            //echo "insert pricing attribute:<br>";
            //$attrarray[]=['Pricing Attr ID','list_header_id',''];
            foreach ($pricing_attr as $attr)
            {
              $updateattr = DB::table('qp_pricing_attr_get_v')
                            ->where('pricing_attribute_id',$attr->pricing_attribute_id)
                            ->first();
              if($updateattr)
              {
                $updateattr = DB::table('qp_pricing_attr_get_v')
                ->where('pricing_attribute_id',$attr->pricing_attribute_id)
                ->update([
                'created_at' =>$attr->creation_date
                ,'updated_at'=>$attr->last_update_date
                ,'list_line_id'=>$attr->list_line_id
                ,'excluder_flag'=>$attr->excluder_flag
                ,'product_attribute_context'=>$attr->product_attribute_context
                ,'product_attribute'=>$attr->product_attribute
                ,'product_attr_value'=>$attr->product_attr_value
                ,'product_uom_code'=>$attr->product_uom_code
                ,'pricing_attribute_datatype'=>$attr->pricing_attribute_datatype
                ,'product_attribute_datatype'=>$attr->product_attribute_datatype
                ,'list_header_id'=>$attr->list_header_id
                ,'list_line_no'=>$attr->list_line_no
                ,'list_line_type_code'=>$attr->list_line_type_code
                ,'arithmetic_operator'=>$attr->arithmetic_operator
                ,'operand'=>$attr->operand
                ,'benefit_limit'=>$attr->benefit_limit
                ,'benefit_uom_code'=>$attr->benefit_uom_code
                ,'automatic_flag'=>$attr->automatic_flag
                ,'modifier_level_code'=>$attr->modifier_level_code
                ,'pricing_phase_id'=>$attr->pricing_phase_id
                ,'benefit_price_list_line_id'=>$attr->benefit_price_list_line_id
                ,'benefit_qty'=>$attr->benefit_qty
                ,'override_flag'=>$attr->override_flag
                ,'rltd_modifier_grp_type'=>$attr->rltd_modifier_grp_type
                ,'rltd_modifier_id'=>$attr->rltd_modifier_id
                ,'rltd_modifier_grp_no'=>$attr->rltd_modifier_grp_no
                ,'parent_list_line_id'=>$attr->parent_list_line_id
                ,'to_rltd_modifier_id'=>$attr->to_rltd_modifier_id
                ]);
                //echo "update pricing attribute id:".$attr->pricing_attribute_id."<br>";
              }
              else{
                $insertattr = DB::table('qp_pricing_attr_get_v')
                          ->insert([
                            'pricing_attribute_id'=>$attr->pricing_attribute_id
                            ,'created_at' =>$attr->creation_date
                            ,'updated_at'=>$attr->last_update_date
                            ,'list_line_id'=>$attr->list_line_id
                            ,'excluder_flag'=>$attr->excluder_flag
                            ,'product_attribute_context'=>$attr->product_attribute_context
                            ,'product_attribute'=>$attr->product_attribute
                            ,'product_attr_value'=>$attr->product_attr_value
                            ,'product_uom_code'=>$attr->product_uom_code
                            ,'pricing_attribute_datatype'=>$attr->pricing_attribute_datatype
                            ,'product_attribute_datatype'=>$attr->product_attribute_datatype
                            ,'list_header_id'=>$attr->list_header_id
                            ,'list_line_no'=>$attr->list_line_no
                            ,'list_line_type_code'=>$attr->list_line_type_code
                            ,'arithmetic_operator'=>$attr->arithmetic_operator
                            ,'operand'=>$attr->operand
                            ,'benefit_limit'=>$attr->benefit_limit
                            ,'benefit_uom_code'=>$attr->benefit_uom_code
                            ,'automatic_flag'=>$attr->automatic_flag
                            ,'modifier_level_code'=>$attr->modifier_level_code
                            ,'pricing_phase_id'=>$attr->pricing_phase_id
                            ,'benefit_price_list_line_id'=>$attr->benefit_price_list_line_id
                            ,'benefit_qty'=>$attr->benefit_qty
                            ,'override_flag'=>$attr->override_flag
                            ,'rltd_modifier_grp_type'=>$attr->rltd_modifier_grp_type
                            ,'rltd_modifier_id'=>$attr->rltd_modifier_id
                            ,'rltd_modifier_grp_no'=>$attr->rltd_modifier_grp_no
                            ,'parent_list_line_id'=>$attr->parent_list_line_id
                            ,'to_rltd_modifier_id'=>$attr->to_rltd_modifier_id
                          ]);
                //echo "insert pricing attribute id:".$attr->pricing_attribute_id."<br>";
              }
            }
          }
        }
        $del_line_attr = DB::table('qp_pricing_attr_get_v')->whereExists(function($query){
                              $query->select(DB::raw(1))
                                  ->from('qp_list_headers as qlh')
                                  ->where('qlh.list_header_id','=','qp_pricing_attr_get_v.list_header_id')
                                  ->whereraw("list_type_code = 'PRO'")
                                  ->whereraw("ifnull(end_date_active,curdate()+interval 1 day) < curdate()");
                          })->delete();
        DB::commit();
        return $sheetarray;
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
        return $sheetarray;
      }
    }

    public function getcondition($qualifier,$query,$cond)
    {
      $kondisi="";
      if($qualifier->comparison_operator_code=="="){
        $kondisi = "='".$qualifier->qualifier_attr_value."'";
      }elseif($qualifier->comparison_operator_code=="NOT ="){
        $kondisi = "!='".$qualifier->qualifier_attr_value."'";
      }elseif($qualifier->comparison_operator_code=="BETWEEN"){
        $kondisi = "between '".$qualifier->qual_attr_value_from_number."' and '".$qualifier->qual_attr_value_to_number."'";
      }
      if($cond=='and'){
        if ($qualifier->qualifier_attribute=="QUALIFIER_ATTRIBUTE1")
          $query->whereraw("customer_class_code ".$kondisi);
        elseif($qualifier->qualifier_attribute=="QUALIFIER_ATTRIBUTE2")
          $query->whereraw("oracle_customer_id ".$kondisi);
	 elseif($qualifier->qualifier_attribute=="QUALIFIER_ATTRIBUTE11")
	   $query->whereraw("oracle_customer_id in (select oracle_customer_id from customer_sites as cs where cs.customer_id = customers.id and cs.site_use_id ".$kondisi.")");
      }else{
        if ($qualifier->qualifier_attribute=="QUALIFIER_ATTRIBUTE1")
          $query->orwhereraw("customer_class_code ".$kondisi);
        elseif($qualifier->qualifier_attribute=="QUALIFIER_ATTRIBUTE2")
          $query->orwhereraw("oracle_customer_id ".$kondisi);
	 elseif($qualifier->qualifier_attribute=="QUALIFIER_ATTRIBUTE11")
	   $query->orwhereraw("oracle_customer_id in (select oracle_customer_id from customer_sites as cs where cs.customer_id = customers.id and cs.site_use_id ".$kondisi.")");	 
      }
      return $query;
    }

    public function getConversionItem($tglskrg){
      $sheetarray = [];
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $conversions = $connoracle->table('mtl_uom_conversions as muc')
                    ->join('mtl_system_items as msi','muc.INVENTORY_ITEM_ID' ,'=','msi.inventory_item_id')
                    ->join('MTL_UNITS_OF_MEASURE_VL as mum','muc.uom_class','=','mum.uom_class')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                              ->from('mtl_parameters as mp')
                              ->whereRaw(' master_organization_id = msi.organization_id');
                    })
                    ->where('mum.BASE_UOM_FLAG','=','Y')
                    ->where('msi.CUSTOMER_ORDER_FLAG','=','Y')
                    ->where('muc.last_update_date','>=',$tglskrg)
                    ->select('msi.inventory_item_id','msi.segment1','msi.description','muc.uom_code','muc.uom_class'
                              ,DB::raw("INV_CONVERT.inv_um_convert(muc.inventory_item_id, muc.UOM_CODE, mum.uom_code) as conversion_rate")
                              ,'mum.uom_code as base_uom','muc.width','muc.height','muc.dimension_uom')
                    ->get();
        //dd($conversions->toSQL());
        if($conversions->count()>0){
          $sheetarray[]=['Item Code','description','from uom','to uom','konversi','status'];
          foreach($conversions as $c){
            //echo ('konversi'.$c->inventory_item_id.'dari '.$c->uom_code.' ke '.$c->base_uom."<br>");
            $mysqlproduct = Product::where('inventory_item_id','=',$c->inventory_item_id)
                        ->select('id')
                        ->first();
            if($mysqlproduct) {
              //echo"Product id :".$mysqlproduct->id."<br>";
              $mysqlconversion =DB::table('uom_conversions')->where(['product_id'=>$mysqlproduct->id,
                'uom_code'=>$c->uom_code,
                'base_uom'=>$c->base_uom])->first();
              if($mysqlconversion)
              {
                DB::table('uom_conversions')
                ->where(['product_id'=>$mysqlproduct->id,
                  'uom_code'=>$c->uom_code,
                  'base_uom'=>$c->base_uom
                  ,'rate'=>$c->conversion_rate
                  ,'width'=>$c->width
                  ,'height'=>$c->height
                  ,'dimension_uom'=>$c->dimension_uom
                ]);
                $sheetarray[]=[$mysqlproduct->itemcode,$mysqlproduct->title,$c->uom_code,$c->base_uom,$c->conversion_rate,'update'];
              } else{
                DB::table('uom_conversions')->insert([
                    'product_id'=>$mysqlproduct->id,
                    'uom_code'=>$c->uom_code,
                    'base_uom'=>$c->base_uom,
                    'uom_class'=>$c->uom_class
                    ,'rate'=>$c->conversion_rate
                    ,'width'=>$c->width
                    ,'height'=>$c->height
                    ,'dimension_uom'=>$c->dimension_uom
                ]);
                $sheetarray[]=[$mysqlproduct->itemcode,$mysqlproduct->title,$c->uom_code,$c->base_uom,$c->conversion_rate,'insert'];
              }

            }

          }
        }
		    DB::commit();
       }
       return $sheetarray;
    }

    public function sendEmailInterface($template, LaravelExcelWriter $file)
    {
      $userit = User::whereexists(function($query){
        $query->select(DB::raw(1))
              ->from('role_user as ru')
              ->join('roles as r','ru.role_id','r.id')
              ->whereraw('ru.user_id = users.id')
              ->wherein('r.name',['IT Galenium']);
      })->select('email','name','id')->get();
      foreach($userit as $u){
        \Mail::send($template,["user"=>$u],function($m) use($file,$u){
            $m->to(trim($u->email), $u->name)->subject('Customer Interface gOrder');
            $m->attach($file->store("xls",false,true)['full']);
        });
      }
    }

}
