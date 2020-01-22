<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\SoHeader;
use App\SoLine;
use App\SoShipping;
use Auth;
use File;
use Storage;
use Carbon\Carbon;
use PDO;
use PDF;
use App\Customer;

use App\Notifications\RejectPoByDistributor;
use App\Notifications\ReceiveItemsPo;
use App\Notifications\ShippingOrderOracle;
use Illuminate\Support\Facades\Input;
use Excel;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;
use App\DPLSuggestNo;
use App\DPLNo;
use App\OutletStock;

class OrderController extends Controller
{
    public function checkOrder($id)
    {
      $header = DB::table('so_header_v as sh')
              ->where('id','=',$id)
              //->where('status','!=',-99)
              ->where(function ($query) {
                $query->orWhere('distributor_id','=',Auth::user()->customer_id)
                      ->orWhere('customer_id','=',Auth::user()->customer_id);
                  })->first();
      /*$header = SoHeader::where('id','=',$id)
        ->where(function ($query) {
          $query->orWhere('distributor_id','=',Auth::user()->customer_id)
                ->orWhere('customer_id','=',Auth::user()->customer_id);
            })->first();*/
      if(!$header)
      {
            return view('errors.403');
      }

      $lines =DB::table('so_lines_v')->where('header_id','=',$id)->get();
      $deliveryno = SoShipping::where('header_id','=',$id)->get();
      $deliveryno = $deliveryno->groupBy('deliveryno','tgl_kirim');

      $user_dist = User::where('customer_id','=',$header->distributor_id)->first();
      if($header->status==-99 and $header->fill_in==1 and $header->customer_id = Auth::user()->customer_id)
        return view('shop.dplorderupdate',compact('header','lines'));
      $print=Input::get('print','');
      if ($print=="yes"){
        $pdf = PDF::loadView('shop.pdf_po',compact('header','lines','deliveryno'));
        $pdf->setPaper('a4','portrait');
        //return $pdf->stream();
        return $pdf->download($header->notrx.'.pdf');
            //return view('shop.pdf_po',compact('header','lines'));
      }  
      if ($user_dist->hasRole('Principal') )    {
        return view('shop.checkOrder1',compact('header','lines','deliveryno'));
      }else {
        return view('shop.checkOrder',compact('header','lines','deliveryno'));
      }
    }

    public function listOrder(Request $request)
    {
      $liststatus = DB::table('flexvalue')->where([['master','=','status_po'],['enabled_flag','=','Y']])->orderBy('id','asc')->get();
      //if(Auth::user()->hasRole('Distributor') || Auth::user()->hasRole('Outlet') || Auth::user()->hasRole('Apotik/Klinik'))
      if(Auth::user()->can('CheckStatusPO'))
      {
          $trx = DB::table('so_header_v as sh')->where('customer_id','=',Auth::user()->customer_id)->orderBy('tgl_order','desc');
      }else{
        abort(403, 'Unauthorized action.');
      }
      $request->jns=1;//listpo

      /*
      if($request->jns==1) //listpo
      {
        $trx = $trx->where('customer_id','=',Auth::user()->customer_id);
      }elseif($request->jns==0){//listso
        $trx = $trx->where('distributor_id','=',Auth::user()->customer_id);
      }*/

      if(is_null($request->search)) //$request->status=="" and $request->tgl_aw=="" and $request->tgl_ak=="" and $request->criteria=="" and
      {
        $request->status=0;
        $trx =$trx->where('status','=',0);
      }else{
        if(isset($request->status))
        {
          $trx =$trx->where('status','=',$request->status);
        }else{
          $request->status=-3;
        }
        if(isset($request->criteria))
        {
          $search = $request->criteria;
          $trx =$trx->Where(function ($query) use($search) {
            $query->orWhere('notrx','LIKE',"%$search%")
                  ->orWhere('customer_po','LIKE',"%$search%")
                  ->orWhere('distributor_name','LIKE',"%$search%")    ;
              });
        }
        if(isset($request->tglaw) )
        {
          $trx =$trx->wheredate('tgl_order','>=',$request->tglaw);
        }
        if(isset($request->tglak) )
        {
          $trx =$trx->wheredate('tgl_order','<=',$request->tglak);
        }
      }
      //var_dump($trx->toSql());
      $trx = $trx->orderBy('tgl_order','desc');
      $trx = $trx->orderBy('notrx','desc');
      $trx = $trx->get();

      return view('shop.listpo',compact('liststatus','trx','request'));
    }

    public function listSO(Request $request)
    {
      $liststatus = DB::table('flexvalue')->where([['master','=','status_po']
                                                ,['enabled_flag','=','Y']
                                              ])
                    ->whereNotIn('id',[-99,-98])
                    ->orderBy('id','asc')->get();
      if(Auth::user()->can('CheckStatusSO'))
      {
          $trx = DB::table('so_header_v as sh')->where('distributor_id','=',Auth::user()->customer_id);
      }else{
        abort(403, 'Unauthorized action.');
      }
      $request->jns=2;//listso
      if ($request->method()=='GET')
      {
        $request->status=0;
        $trx =$trx->where('status','=',0);
        $request->tglak = date_format(Carbon::now(),'Y-m-d');
        $request->tglaw = date_format(Carbon::now()->addDay(-7),'Y-m-d');
      }
      if($request->excel == "Create Excel" and $request->status=="x")
      {
        $this->createExcel($request,null);
      }

      if($request->status=="" and $request->tglaw=="" and $request->tglak=="" and $request->criteria=="")
      {
        $request->status=0;
        $trx =$trx->where('status','=',0);
      }else{
        if(isset($request->status))
        {
          if($request->status=="x")
          {
              $trx =$trx->where([['status','=',0],['approve','=',1]]);
              //$this->createExcel($request,null);
          }elseif($request->status=="0"){
              $trx =$trx->where([['status','=',0],['approve','=',0]]);
          }else{
              $trx =$trx->where('status','=',$request->status);
          }

        }
        if(isset($request->criteria))
        {
          $search = $request->criteria;
          $trx =$trx->Where(function ($query) use($search) {
            $query->orwhere('customer_name','LIKE',"%$search%")
                  ->orWhere('notrx','LIKE',"%$search%")
                  ->orWhere('customer_po','LIKE',"%$search%")   ;
              });
        }
        if(isset($request->tglaw) )
        {
          $trx =$trx->wheredate('tgl_order','>=',$request->tglaw);
        }
        if(isset($request->tglak) )
        {
          $trx =$trx->wheredate('tgl_order','<=',$request->tglak);
        }
      }
      $trx = $trx->orderBy('tgl_order','desc');
      $trx = $trx->orderBy('notrx','desc');
      $trx = $trx->get();

      return view('shop.listpo',compact('liststatus','trx','request'));
    }

    public function approvalSO(Request $request)
    {

      $header = SoHeader::where([
        ['id','=',$request->header_id],
        ['distributor_id','=',Auth::user()->customer_id]
      ])->first();
      //echo($header.','.Auth::user()->customer_id);
      if(!$header)
      {
        return view('errors.403');
      }
      //dd('test');
      DB::beginTransaction();
      try{
      if($request->approve=="approve")
      {
        $solines = DB::table('so_lines')->where('header_id','=',$request->header_id)->get();
        $i=0;
        /*validation qty*/
        foreach($solines as $soline)
        {
          if($soline->qty_request_primary+$soline->bonus_gpl<$request->qtyshipping[$soline->line_id])
          {
            return redirect()->back()->withError("Gagal simpan! Qty melebihi order")->withInput();
          }
        }
        /*update qty*/
        foreach($solines as $soline)
        {
          $i+=1;
          if( $request->uom[$soline->line_id]==$soline->uom){
            $qty = $request->qtyshipping[$soline->line_id]*$soline->conversion_qty;
          }elseif( $request->uom[$soline->line_id]==$soline->uom_primary){
            $qty = $request->qtyshipping[$soline->line_id];
          }

          $update = DB::table('so_lines')
            ->where([
              ['header_id','=',$request->header_id],
              ['line_id','=',$soline->line_id]
            ])
            ->update(['qty_confirm' => $qty,'last_update_by'=> Auth::user()->id]);

            /*if(!is_null($header->oracle_customer_id))
          {
          if($oraheader){
              $oraline = $connoracle->table('oe_lines_iface_all')->insert([
                'order_source_id'=>config('constant.order_source_id')
                ,'orig_sys_document_ref' => $header->notrx
                ,'orig_sys_line_ref'=>$soline->line_id
                ,'line_number'=>$i
                ,'inventory_item_id'=>$soline->inventory_item_id
                ,'ordered_quantity'=>$request->qtyshipping[$soline->line_id]
                ,'order_quantity_uom'=>$soline->uom
                //,'ship_from_org_id'=>$soline->qty_shipping
                ,'org_id'=>$header->org_id
                //,'pricing_quantity'
                //,'unit_selling_price'
                //,'unit_list_price'
                //,'price_list_id'
                //,'payment_term_id'
                //,'schedule_ship_date'
                ,'request_date'=>$header->tgl_order
                ,'created_by'=>-1
                ,'creation_date'=>Carbon::now()
                ,'last_updated_by'=>-1
                ,'last_update_date'=>Carbon::now()
                //,'line_type_id'
                ,'calculate_price_flag'=>'Y'
              ]);
              $ordertype=config('constant.order_source_id');
              $orgid = $header->org_id;
              $notrx =$header->notrx;
            //}
          }*/
        }
        if(isset($header->dpl_no)){
          $checkconfirm = DB::table('so_lines')
            ->where(
              'header_id','=',$request->header_id
            )->whereRaw('qty_request_primary+bonus_gpl != qty_confirm')
            ->where(function($query){
                $query->whereNotNull('discount')
                  ->orwhereNotNull('discount_gpl')
                  ->orwhereNotNull('bonus_gpl');
            })->get() ;
            if($checkconfirm->count())//jika ada qty
            {
              //notfullconfirm
              $dpl = DPLSuggestNo::where('suggest_no', $header->suggest_no)
                ->update(array('approved_by' => '', 'next_approver' => '', 'fill_in' => 1));
              //$nodpl= DPLNo::where('suggest_no', $header->suggest_no)->delete();
              $header->status=-99;
              $header->save();
              $notified_users = app('App\Http\Controllers\DPLController')->getArrayNotifiedEmail($header->suggest_no);
        			if(!empty($notified_users)){
        				$data = [
        					'title' => 'Resetting DPL',
        					'message' => 'Stock utk DPL #'.$header->dpl_no.' tdk mencukupi',
        					'id' => $header->suggest_no,
        					'href' => route('dpl.readNotifApproval'),
        					'mail' => [
        						'greeting'=>'',
        						'content'=> ''
        					]
        				];
        				foreach ($notified_users as $key => $email) {
        					foreach ($email as $key => $mail) {
        						$data['email'] = $mail;
        						$apps_user = User::where('email',$mail)->first();
        						if(!empty($apps_user))
        							$apps_user->notify(new PushNotif($data));
        					}
        				}
        			}
              DB::commit();
              return redirect()->route('order.listSO')->withMessage('Qty Konfirmasi tidak full 1 SO. PO menunggu konfirmasi principal Galenium kembali');
            }
        }

        if(!is_null($header->oracle_customer_id))
        {
            $header->status=0;
        }else{
          $header->status=1;
          /*notify outlet*/
          $customer = Customer::where('id','=',$header->customer_id)->first();
          $content ='PO Anda nomor '.$header->notrx.' telah dikonfirmasi oleh '.$header->distributor->customer_name.'. Silahkan check PO anda kembali.<br>';
          $content .= 'Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
          $data = [
           'title' => 'Konfirmasi PO',
           'message' => 'Konfirmasi PO '.$header->customer_po.' oleh distributor.',
           'id' => $header->id,
           'href' => route('order.notifnewpo'),
           'mail' => [
             'greeting'=>'Konfirmasi PO '.$header->customer_po.' oleh distributor.',
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

        $header->approve=1;
      //  $header->status=1;
        $header->tgl_approve=Carbon::now();
        $header->save();
        DB::commit();
        //$this->createExcel(null,$h->id);
        return redirect()->route('order.listSO')->withMessage(trans('pesan.approveSO_msg',['notrx'=>$header->notrx]));
      }elseif($request->approve=="reject"){
        if(isset($header->dpl_no))
        {
          $dpl = DPLSuggestNo::where('suggest_no', $header->suggest_no)
            ->update(array('approved_by' => '', 'next_approver' => '', 'fill_in' => 1));
          app('App\Http\Controllers\DPLController')->dpllog($header->suggest_no,'PO ditolak Distributor '.Auth::user()->name,$request->alasan);
          //$nodpl= DPLNo::where('suggest_no', $header->suggest_no)->delete();
          $header->status=-99;
          $header->save();
          $notified_users = app('App\Http\Controllers\DPLController')->getArrayNotifiedEmail($header->suggest_no);
          if(!empty($notified_users)){
            $pesan = 'Penolakan PO utk DPL #'.$header->dpl_no;
            if(isset($request->alasan)) $pesan .=', alasan:'.$request->alasan;
            $data = [
              'title' => 'Resetting DPL',
              'message' => $pesan,
              'id' => $header->suggest_no,
              'href' => route('dpl.readNotifApproval'),
              'mail' => [
                'greeting'=>'Penolakan PO utk DPL #'.$header->dpl_no,
                'content'=> 'Kami informasikan untuk DPL #'.$header->dpl_no.' dan no.trx:'.$header->notrx.', ditolak oleh distributor.<br>Harap setting ulang pengajuan dpl kembali.'
              ]
            ];
            foreach ($notified_users as $key => $email) {
              foreach ($email as $key => $mail) {
                $data['email'] = $mail;
                $apps_user = User::where('email',$mail)->first();
                if(!empty($apps_user))
                  $apps_user->notify(new PushNotif($data));
              }
            }
          }
          DB::commit();
          return redirect()->route('order.listSO')->withMessage('PO '.$header->customer_po.' ditolak.');
        }else{
          $update = DB::table('so_lines')
            ->where('header_id','=',$request->header_id)
            ->update(['qty_confirm' => 0,'last_update_by'=>Auth::user()->id]);
          $header->status=-2;
          $header->alasan_tolak=$request->alasan;
          $header->tgl_approve=Carbon::now();
          $header->last_update_by = Auth::user()->id;
          $header->save();
          $customer = Customer::where('id','=',$header->customer_id)->first();
          $content = "Mohon maaf, bersama ini kami informasikan bahwa PO anda No:".$header->customer_po." telah dibatalkan.";
          $content .="<br>Silahkan konfirmasi ke Distributor untuk penjelasan lebih detail.";
          $data=[
            'title' => 'Penolakan PO',
            'message' => 'Penolakan PO #'.$header->customer_po.'dari distributor',
            'id' => $header->id,
            'href' => route('order.notifnewpo'),
            'mail' => [
              'greeting'=>'Penolakan PO #'.$header->customer_po.'.',
              'content' =>$content,
            ]
          ];
          foreach($customer->users as $u)
          {
            $data['email'] = $u->email;
            //$u->notify(new RejectPoByDistributor($header,1, $request->alasan));
            //event(new PusherBroadcaster($data, $u->email));
            $u->notify(new PushNotif($data));
          }
          DB::commit();
          return redirect()->route('order.listSO')->withMessage(trans('pesan.rejectSO_msg',['notrx'=>$header->notrx]));
        }
      }elseif($request->kirim=="kirim"){
        if($header->status<3 and $header->status>0 and Auth::user()->customer_id ==$header->distributor_id)
        {
          $this->validate($request, [
          'deliveryno' => 'required',
          ]);

          $solines = DB::table('so_lines')->where('header_id','=',$request->header_id)
                    ->whereIn('line_id',array_keys($request->qtyshipping))
                    ->get();
          /*validation qty*/
          foreach($solines as $soline)
          {
            if($soline->qty_confirm<intval($soline->qty_shipping)+$request->qtyshipping[$soline->line_id])
            {
              return redirect()->back()->withError("Gagal simpan! Qty kirim melebihi order")->withInput();
            }
            $checkshipping= SoShipping::where(['deliveryno'=>$request->deliveryno
                            ,'header_id'=>$soline->header_id
                            ,'line_id'=>$soline->line_id])->first();
            if($checkshipping){
              return redirect()->back()->withError("Gagal simpan! Delivery No sudah ada")->withInput();
            }
          }
          /*update shipping*/
          foreach($solines as $soline)
          {
            if($request->qtyshipping[$soline->line_id]>0){
              $insertkirim = SoShipping::create([
                'deliveryno'=>$request->deliveryno
                ,'product_id'=>$soline->product_id
                ,'uom'=>$soline->uom
                ,'qty_request'=>$soline->qty_request
                ,'uom_primary'=>$soline->uom_primary
                ,'qty_request_primary'=>$soline->qty_request_primary
                ,'conversion_qty'=>$soline->conversion_qty
                ,'qty_shipping'=>$request->qtyshipping[$soline->line_id]
                ,'tgl_kirim'=>Carbon::now()
                ,'header_id'=>$soline->header_id
                ,'line_id'=>$soline->line_id
              ]);
              $update = DB::table('so_lines')
                ->where([
                  ['header_id','=',$request->header_id],
                  ['line_id','=',$soline->line_id]
                ])
                ->update(['qty_shipping' => intval($soline->qty_shipping)+$request->qtyshipping[$soline->line_id]
                          ,'last_update_by'=>Auth::user()->id]);
            }

          }
          $header->tgl_kirim =Carbon::now();
          $afterheader = DB::table('so_lines_sum_v')->where('header_id','=',$request->header_id)->first();

          if($afterheader->qty_shipping_primary==$afterheader->qty_confirm_primary
          /*or $afterheader->qty_shipping_primary==$afterheader->qty_request_primary*/)
          {
            $header->status=3; /*full shipping*/
          }else{
            $header->status=2; /*partial shipping*/
          }

          $customer = Customer::where('id','=',$header->customer_id)->first();
          $content = 'PO Anda nomor '.$header->customer_po.' telah dikirimkan oleh '.$header->distributor->customer_name.'. ';
          $content .='Silahkan check PO anda kembali.<br>' ;
          $content .='Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
          $data=[
            'title' => 'Pengiriman PO',
            'message' => 'PO #'.$header->customer_po.' telah dikirim dgn SJ#'.$request->deliveryno,
            'id' => $header->id,
            'href' => route('order.notifnewpo'),
            'mail' => [
              'greeting'=>'Pengiriman Barang PO #'.$header->customer_po.', SJ#'.$request->deliveryno,
              'content' =>$content,
            ]
          ];
          foreach($customer->users as $u)
          {
            $data['email']= $u->email;
            //$u->notify(new ShippingOrderOracle($header,$customer->customer_name));
            //event(new PusherBroadcaster($data, $u->email));
            $u->notify(new PushNotif($data));
          }
          $header->save();
          DB::commit();
          return redirect()->route('order.listSO')->withMessage(trans('pesan.sendSO_msg',['notrx'=>$header->notrx]));
        }else{
          return view('errors.403');
        }

      }elseif($request->createExcel=="Create Excel"){
          $this->createExcel($request, $request->header_id);
        //  return redirect()->route('order.listPO');
      }
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }
    }

    public function readnotifnewpo($id,$notifid)
    {
       Auth::User()->notifications()
                  ->where('id','=',$notifid)
                    ->update(['read_at' => Carbon::now()]);
       return redirect()->route('order.checkPO',$id);
    }

    public function batalPO(Request $request) /*untuk membatalkan PO atau receive item*/
    {
      DB::beginTransaction();
      DB::enableQueryLog();
      try{
      if($request->batal=="batal"){
        $header = SoHeader::where([
          ['id','=',$request->header_id],
          ['customer_id','=',Auth::user()->customer_id]
        ])->first();
        if(!$header)
        {
          return view('errors.403');
        }
        if($header->status==0)
        {
          $header->status = -1;

          $customer = Customer::where('id','=',$header->distributor_id)->first();
          $content = "Bersama ini kami informaskikan bahwa PO dengan no.transaksi#".$header->notrx." telah dibatalkan oleh customer.";
          $content .="<br>Silahkan konfirmasi ke customer untuk penjelasan lebih detail.";
          $data=[
            'title' => 'Pembatalan PO',
            'message' => 'Pembatalan PO #'.$header->customer_po.'oleh '.$header->outlet->customer_name,
            'id' => $header->id,
            'href' => route('order.notifnewpo'),
            'mail' => [
              'greeting'=>'Pembatalan PO #'.$header->customer_po.'.',
              'content' =>$content,
            ]
          ];
          foreach($customer->users as $u)
          {
            $data['email']= $u->email;
            //$u->notify(new RejectPoByDistributor($header,0,""));
            //event(new PusherBroadcaster($data, $u->email));
            $u->notify(new PushNotif($data));
          }
          $header->save();
          DB::commit();
          return redirect()->route('order.listPO')->withMessage(trans('pesan.cancelPO_msg',['notrx'=>$header->notrx]));
        }else{
          return redirect()->route('order.checkPO',$request->header_id)->withMessage(trans('pesan.cantcancelPO',['notrx'=>$header->notrx]));
        }
      }elseif($request->terima=="terima")
      {
        $this->validate($request, [
        'deliveryno' => 'required',
        ]);
        //dd($request->all());
        $header = SoHeader::where([
          ['id','=',$request->header_id],
          ['customer_id','=',Auth::user()->customer_id]
        ])->first();

        if(!$header)
        {
          return view('errors.403');
        }
        if($header->status>=0 and $header->status<4)
        {
          /*$soshipping = DB::table('so_shipping')->where([
            ['header_id','=',$request->header_id],
            ['deliveryno','=',$request->deliveryno]
          ])->get();*/
          $solines = SoLine::where('header_id','=',$request->header_id)
          /*$solines = DB::table('so_lines')
                    ->where('header_id','=',$request->header_id)*/
                    ->whereIn('line_id',array_keys($request->qtyreceive))
                    ->select('line_id',DB::raw("ifnull(qty_accept,0) as qty_accept")
                      ,DB::raw("ifnull(qty_confirm,qty_request_primary) as qty_confirm")
                      ,'product_id','uom','qty_request','qty_request_primary','uom_primary','conversion_qty')
                    ->get();
          foreach($solines as $soline)
          {
            $qtyterima =0;
            if(is_array($request->qtyreceive[$soline->line_id]))
            {
              foreach($request->qtyreceive[$soline->line_id] as $key => $qty)
              {
                  $qtyterima+=$qty;
                  $insshipping =SoShipping::updateorCreate(
                    ['header_id'=>$request->header_id,'line_id'=>$soline->line_id,'deliveryno'=>$request->deliveryno,'id'=>$key]
                    ,['qty_accept'=>$qty,'product_id'=>$soline->product_id
                      ,'qty_backorder'=>DB::raw("coalesce(qty_shipping-".$qty.",null)")
                      ,'qty_shipping'=>DB::raw("if(qty_shipping is null,null,".$qty.")")
                      ,'uom'=>$soline->uom,'qty_request'=>$soline->qty_request,'qty_request_primary'=>$soline->qty_request_primary
                      ,'uom_primary'=>$soline->uom_primary,'conversion_qty'=>$soline->conversion_qty
                      ,'tgl_terima'=>Carbon::now(), 'keterangan'=>$request->note
                     ]
                  )  ;
                  //dd(DB::getQueryLog());
              }
            }else{
              $qtyterima=$request->qtyreceive[$soline->line_id];
              $insshipping =SoShipping::updateorCreate(
                ['header_id'=>$request->header_id,'line_id'=>$soline->line_id,'deliveryno'=>$request->deliveryno]
                  ,['qty_accept'=>$request->qtyreceive[$soline->line_id],'product_id'=>$soline->product_id
                  ,'qty_shipping'=>DB::raw("if(qty_shipping is null,null,".$request->qtyreceive[$soline->line_id].")")
                  ,'qty_backorder'=>DB::raw("coalesce(qty_shipping-".$request->qtyreceive[$soline->line_id].",null)")
                  ,'uom'=>$soline->uom,'qty_request'=>$soline->qty_request,'qty_request_primary'=>$soline->qty_request_primary
                  ,'uom_primary'=>$soline->uom_primary,'conversion_qty'=>$soline->conversion_qty
                  ,'tgl_terima'=>Carbon::now(), 'keterangan'=>$request->note
                 ]
              )  ;
            }

            /*if (Auth::user()->hasRole('Apotik/Klinik') ){
              $instock = new OutletStock;
              $instock->product_id = $soline->product_id;
              $instock->outlet_id = Auth::user()->customer_id;
              $instock->trx_date = date('Y-m-d',Carbon::now());
              $instock->event = 'trx_in';
              $instock->qty = $qtyterima;
              $instock->deliveryorder_no = $request->deliveryno;
              $instock->save();
            }*/
              $soline->qty_shipping = $soline->shippings->sum('qty_shipping');
              $soline->qty_accept = $soline->shippings->sum('qty_accept');
              $soline->save();


              //$qtyterima=$soline->qty_accept+$request->qtyreceive[$soline->line_id];
            /*$update = DB::table('so_lines')
              ->where([
                ['header_id','=',$request->header_id],
                ['line_id','=',$soline->line_id]
              ])
              ->update(['qty_accept' => $qtyterima]);*/
          }
          if(is_null($header->tgl_terima))
          {
            $header->tgl_terima= Carbon::now();
          }
          $afterheader = DB::table('so_lines_sum_v')->where('header_id','=',$request->header_id)->first();

          if($afterheader->qty_accept_primary==$afterheader->qty_confirm_primary
          or ($afterheader->qty_confirm_primary==0 and $afterheader->qty_accept_primary==$afterheader->qty_request_primary))
          {
              $header->status = 4;
          }elseif(($afterheader->qty_shipping_primary==$afterheader->qty_confirm_primary and $afterheader->qty_shipping_primary!=0)
          or ($afterheader->qty_confirm_primary==0 and $afterheader->qty_shipping_primary==$afterheader->qty_request_primary)){
              $header->status = 3;
          }else{ $header->status = 2;}
          $header->save();
          $dist=Customer::where('id','=',$header->distributor_id)->first();
          $content = 'Pesanan Anda dengan PO nomor: <strong>'.$header->customer_po.'</strong> dan SJ: '.$request->deliveryno.' telah diterima customer pada tanggal: '.Carbon::now().'.<br>';
          $content .='Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
          $data=[
            'title' => 'PO diterima customer',
            'message' => 'SJ #'.$request->deliveryno.' telah diterima customer ,tgl:'.Carbon::now(),
            'id' => $header->id,
            'href' => route('order.notifnewpo'),
            'mail' => [
              'greeting'=>'SJ: '.$request->deliveryno.' telah diterima customer',
              'content' =>$content,
            ]
          ];
          foreach($dist->users as $d)
          {
            $data['email']= $d->email;
            //$d->notify(new ReceiveItemsPo($header,$request->deliveryno));
            //event(new PusherBroadcaster($data, $d->email));
            $d->notify(new PushNotif($data));
          }
          DB::commit();
          return redirect()->route('order.listPO')->withMessage(trans('pesan.receivePO_msg',['notrx'=>$header->notrx]));
        }else{
          return redirect()->route('order.checkPO',$request->header_id)->withMessage(trans('pesan.cantreceivePO',['notrx'=>$header->notrx]));
        }
      }
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }
    }

    public function createExcel(Request $request,$id)
    {
      $header = DB::table('so_headers as sh')
                ->join('customers as c','sh.customer_id','=','c.id')
                ->leftjoin('oe_transaction_types as ot','sh.order_type_id','=','ot.transaction_type_id')
                ->leftjoin('qp_list_headers as ql','ql.list_header_id','=','sh.price_list_id')
                ->where([
                        ['sh.status','=','0'],
                        ['sh.approve','=','1'],
                        ['sh.distributor_id','=',Auth::user()->customer_id],
                        //['sh.id','=',$id]
                      ]);
        if(isset($request->criteria))
        {
          $search = $request->criteria;
          $header =$header->Where(function ($query) use($search) {
            $query->orwhere('customer_name','LIKE',"%$search%")
                  ->orWhere('notrx','LIKE',"%$search%")
                  ->orWhere('customer_po','LIKE',"%$search%")   ;
              });
        }
        if(isset($request->tglaw) )
        {
          $header =$header->wheredate('tgl_order','>=',$request->tglaw);
        }
        if(isset($request->tglak) )
        {
          $header =$header->wheredate('tgl_order','<=',$request->tglak);
        }
        if(isset($id))
        {
          $header =$header->where('sh.id','=',$id);
        }
        $header =$header->select('c.customer_name','sh.customer_po', DB::raw('date_format(tgl_order,"%d-%b-%Y %H:%i:%s") as tgl_order')
                           , 'sh.oracle_ship_to','sh.currency', 'sh.oracle_bill_to',DB::raw('"ENT" as ent')
                           ,DB::raw('"*NB" as nb'),'sh.id', 'ot.name as transaction_name','ql.name as price_name', 'c.customer_number','sh.warehouse','sh.notrx'
                         );

        $header =$header->get();

        //$header=$header->toArray();
        //$data= json_decode( json_encode($header), true);
        //$i=0;            //  dd($header->toArray());
      $create= Excel::create('template_SO_Oracle', function($excel) use ($header ) {
        $excel->setTitle('Interface sales order');
        $excel->setCreator('Shanty')
          ->setCompany('Solinda');
        $excel->sheet('order', function($sheet) use ($header)
        {

          foreach($header as $h)
          {
            //$i++;
            if (Auth::user()->name=='YASA MITRA PERDANA, PT.')
            {
              //if($h->)
              $warehouse = config('constant.def_warehouse_YMP');
            }elseif (Auth::user()->name=='GALENIUM PHARMASIA LABORATORIES, PT.'){
              $warehouse = config('constant.def_warehouse_GPL');
            }
            $connoracle = DB::connection('oracle');
            if($connoracle){
              $oraheader = $connoracle->table('oe_order_headers_all oha')
                          //->where(DB::raw('nvl(oha.attribute1,oha.orig_sys_document_ref)'),'=',$h->notrx)
                          ->whereExists(function ($query) use($h) {
                                $query->select(DB::raw(1))
                                      ->from('oe_order_lines_all ola')
                                      ->whereRaw("ola.header_id = oha.header_id and nvl(ola.attribute1,ola.orig_sys_document_ref) = '".$h->notrx."'");
                            })
                          ->where('oha.cancelled_flag','!=','Y')
                          ->get();
              //dd($oraheader);
              if($oraheader->isEmpty())
              {
                $oraheader=null;
              }
            }else{
              $oraheader=null;
            }
            if(is_null($oraheader)){
              //$data = json_decode( json_encode($h), true);
                //$sheet->fromArray($h, null, 'A1', false, false);
                //$sheet->row($i,json_decode( json_encode($h), true));
                //$sheet->appendRow(json_decode( json_encode($h), true));
                $sheet->appendRow(array('',$h->customer_number,$h->transaction_name,$h->customer_po,$h->tgl_order
                                  ,$h->price_name,$h->oracle_ship_to,'','','',$h->currency,$h->oracle_bill_to
                                  ,'','','ENT','','','','','','','*NB'));
                $line = DB::table('so_lines as sl')
                      ->join('products as p','sl.product_id','=','p.id')
                      ->wherenull('bonus_list_line_id')
                      ->where([
                        ['sl.header_id','=',$h->id]
                      ])->select('p.itemcode',DB::raw('sl.qty_confirm as qty_confirm'),'sl.uom_primary','sl.line_id')
                      ->get();
                foreach($line as $l)
                {
                  $sheet->appendRow(array($l->itemcode,'',$l->qty_confirm,$l->uom_primary,'','','','',$h->tgl_order,'','','','','','',$h->notrx,$l->line_id,'ENT','*DN','ENT','*UP','*DN'));

                }
                $sheet->appendRow(array('*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*PB','*IR'));
            }


          }
          $sheet->protect('GPLJanganDiub4h');
        });
      })->export("xlsx");
      return redirect()->route('order.listPO');
      //return 1;
    }

    public function changeOrderUom(Request $request)
    {
      $line = DB::table('So_Lines_v')->where('line_id','=',$request->id)->first();
      $header = SoHeader::where('id','=',$line->header_id)->select('status','approve')->first();

      if ($request->satuan ==$line->uom)
      {
        $price = $line->unit_price;
        $qtyorder = $line->qty_request;
        if($header->status==0)
        {
            $qtyconfirm =$qtyorder;
            $qtykirim = $qtyorder;
            $qtyterima=$qtyorder;
        }elseif($header->status==1){
            $qtyconfirm =$line->qty_confirm;
            $qtykirim = $qtyconfirm;
            $qtyterima=$qtyconfirm;
        }


      }elseif ($request->satuan ==$line->uom_primary){
        $price = $line->unit_price/$line->conversion_qty;
        $qtyorder = $line->qty_request_primary;
        $qtyconfirm =$line->qty_confirm_primary;
        $qtykirim = $line->qty_shipping_primary;
        $qtyterima=$line->qty_accept_primary;
      }

      return response()->json([
                      'result' => 'success',
                      'price' => (float)$price,
                      'qtyorder' => (float)$qtyorder,
                      'qtyconfirm' => (float)$qtyconfirm,
                      'qtyshipping' =>$qtykirim,
                      'qtyaccept' =>$qtyterima
                    ],200);
    }

    public function shippingKurir(){
      return view('shop.kurir',['ship'=>null,'sjnumber'=>null]);
    }

    public function searchShipping(Request $request){
      $this->validate($request, [
      'nosj' => 'required',
      ]);
      $sjnumber=$request->nosj;

      $ship = SoShipping::whereNotNull('source_header_id')
              ->join('so_header_v as sh','sh.id','=','so_shipping.header_id')
              ->join('so_lines_v as sl','sl.line_id','=','so_shipping.line_id')
              ->where(function($query) use ($sjnumber) {
                          $query->where('deliveryno','=',$sjnumber)
                          ->orWhere('waybill','=',$sjnumber);
                        })
              ->select('sh.notrx','sh.customer_name','sh.distributor_name',
                    'sh.tgl_order','sh.ship_to_addr','sh.status', 'sl.title'
                    ,'so_shipping.qty_shipping'
                    ,'so_shipping.qty_backorder'
                    ,'so_shipping.deliveryno'
                    ,'so_shipping.waybill'
                    ,'so_shipping.header_id'
                    ,'so_shipping.line_id'
                    ,'so_shipping.uom_primary'
                    ,'so_shipping.id','so_shipping.tgl_terima_kurir'
                      )->get();

      //dd($ship);
      return view('shop.kurir',compact('ship','sjnumber'));
    }

    public function shipconfirmcourier(Request $request)
    {
      $this->validate($request, [
        'nosj' => 'required',
      ]);
      if($request->btnterima == "confirm")
      {
          $updshipping = SoShipping::where(['deliveryno'=>$request->nosj,'waybill'=>$request->airwayno])
                        ->update(['tgl_terima_kurir'=>Carbon::now(), 'userid_kurir'=>Auth::user()->id]);
          $nosj=$request->nosj;
          $soheaders = SoHeader::whereExists(function ($query) use($nosj){
                $query->select(DB::raw(1))
                      ->from('so_shipping as ss')
                      ->whereRaw("ss.header_id = so_headers.id  and ss.deliveryno = '".$nosj."'");
            })->get();
            //dd($soheaders);
            foreach($soheaders as $sh)
            {
                $content ='SJ #'.$nosj.' telah selesai diantar oleh '.Auth::user()->name.' pada tanggal:'.Carbon::now();
                $content .= 'Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
                $data = [
                 'title' => 'Konfirmasi Pengiriman oleh Kurir',
                 'message' => 'SJ #'.$nosj.' telah selesai dikirim.',
                 'id' => $sh->id,
                 'href' => route('order.notifnewpo'),
                 'mail' => [
                   'greeting'=>'Konfirmasi Pengiriman #'.$sh->notrx.' oleh Kurir',
                   'content' =>$content,
                 ]
               ];
               $users = User::where('customer_id','=',$sh->distributor_id)->get();
               foreach($users as $u)
               {
                $data['email']= $u->email;
                 $u->notify(new PushNotif($data));
               }
            }


          return redirect()->route('order.shippingSO')->withMessage(trans('pesan.update'));
      }else return redirect()->back();

    }

    public function rptOrderForm(Request $request)
    {
      if ($request->method()=='GET')
      {
        $provinces = DB::table('provinces')->get();
        $channels = DB::table('subgroup_datacenters')
                  ->join('group_datacenters','group_datacenters.id','=','subgroup_datacenters.group_id')
                  ->select('subgroup_datacenters.id',DB::raw("concat(group_datacenters.display_name,'-',subgroup_datacenters.display_name) as name"))
                  ->orderBy('group_datacenters.id','asc')
                  ->orderBy('subgroup_datacenters.id','asc')
                  ->get();
        return view('admin.report.order',compact('provinces','request','channels'));
      }else{
        //echo ("tglaw:".$request->tglaw."tgl_akhir:".$request->tglak);
        DB::enableQueryLog();
        $namapropinsi=null;
        $nmchannel=null;
        $datalist = DB::table('so_header_v as sh')
                      ->join('customers as c','sh.customer_id','c.id')
                    ->leftjoin('subgroup_datacenters as sdc','c.subgroup_dc_id','sdc.id')
                    ->leftjoin('group_datacenters as gdc','sdc.group_id','gdc.id')
                    ->leftjoin('so_lines_v as sl','sh.id','sl.header_id')
                    ->leftjoin('so_shipping as ss','sl.line_id','ss.line_id')
                    ->wheredate('sh.tgl_order','>=',$request->tglaw)
                    ->wheredate('sh.tgl_order','<=',$request->tglak)
                    ->select('sh.notrx','sh.customer_name','sh.tgl_order','sh.distributor_name','ship_to_addr as alamat','status_name'
                              , DB::raw("case
                                        		when c.psc_flag='1' and c.pharma_flag='1' then 'PSC/PHARMA'
                                        	  when c.psc_flag='1' and c.pharma_flag='0' then 'PSC'
                                        	  else 'PHARMA'
                                        	end as divisi")
                              , DB::raw("concat(gdc.display_name,'-',sdc.display_name) as channel")
                              , 'sh.tgl_approve','sh.id'
                              , 'sl.line_id','sl.product_id', 'sl.title','sl.amount'
                              ,'sl.qty_request_primary',DB::raw("sl.unit_price/sl.conversion_qty as unit_price_primary")
                              , DB::raw("sum(ss.qty_shipping) as qty_shipping")
                              ,'ss.deliveryno','ss.tgl_terima','ss.tgl_kirim'
                              ,DB::raw("datediff(ss.tgl_kirim,sh.tgl_order) as service_level")
														  , DB::raw("datediff(ss.tgl_terima,sh.tgl_order) as lead_time")
                            );

          if(isset($request->dist_id))
          {
            $datalist=$datalist->where('sh.distributor_id','=',$request->dist_id);
          }
          if(isset($request->outlet_id))
          {
            $datalist=$datalist->where('sh.customer_id','=',$request->outlet_id);
          }

          if(isset($request->psc_flag) and isset($request->pharma_flag))
          {
            $datalist=$datalist->where(function($query){
              $query->where('psc_flag','=',"1")
                    ->orWhere('pharma_flag','=',"1");
            });
          }elseif(isset($request->psc_flag)){
            $datalist=$datalist->where('psc_flag','=',"1");
          }elseif(isset($request->pharma_flag)){
            $datalist=$datalist->where('pharma_flag','=',"1");
          }

          if($request->channel)
          {
            $datalist=$datalist->where('subgroup_dc_id','=',$request->channel);
            $nmchannel=DB::table('subgroup_datacenters as sdc')
                      ->join('group_datacenters as gd','sdc.group_id','gd.id')
                      ->select(DB::raw("concat(gd.display_name,'-',sdc.display_name) as channel"))
                      ->where('sdc.id','=',$request->channel)
                      ->first();
            $nmchannel = $nmchannel->channel;
          }

          if(isset($request->propinsi))
          {
            $datalist=$datalist->where('sh.province_id','=',$request->propinsi);
            $namapropinsi = DB::table('provinces')->where('id','=',$request->propinsi)->first();
            $namapropinsi = $namapropinsi->name;
          }

          $datalist =$datalist->groupBy('notrx', 'customer_name', 'tgl_order', 'distributor_name', 'alamat', 'status_name', 'divisi', 'channel', 'tgl_approve'
	                                     , 'id', 'line_id', 'product_id', 'title', 'amount', 'qty_request_primary', 'unit_price_primary', 'deliveryno', 'tgl_terima', 'tgl_kirim');
          $datalist =$datalist->get();
          //dd(DB::getQueryLog() );
        Excel::create('Order-'.Carbon::now(), function($excel) use($datalist,$request,$namapropinsi,$nmchannel) {
            $excel->sheet('order', function($sheet) use($datalist,$request,$namapropinsi,$nmchannel) {
                $sheet->loadView('admin.report.orderview',array('datalist'=>$datalist,'request'=>$request,'namapropinsi'=>$namapropinsi,'nmchannel'=>$nmchannel));
                 $sheet->setWidth(array(
                                    'I'     =>  50,
                                    'K'     =>  10,
                                    'L'     =>  10,
                                    'Q'     =>  10,
                                    'S'     =>  10,
                                    'T'     =>  10,
                                ));
                $sheet->getStyle('U')->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');

      }
    }

    public function updatePO(Request $request)
    {
      if ($request->update =="update")
      {
        //dd($request->all());
        DB::beginTransaction();
        try{
          $soheader=SoHeader::select('so_headers.*','dsn.fill_in')
                    ->leftjoin("dpl_suggest_no as dsn",'dsn.suggest_no','=','so_headers.suggest_no')
                    ->where('so_headers.id','=',$request->header_id)->first();
          if($soheader->fill_in==1){
            $solines = SoLine::where('header_id','=',$request->header_id)
                      ->whereIn('line_id',array_keys($request->qtyorder))
                      ->get();
            $ubah=false;
            foreach ($solines as $line)
            {
              if($line->uom_primary==$request->uom[$line->line_id])
              {
                if($line->qty_request_primary != $request->qtyorder[$line->line_id]){
                  $ubah=true;
                  $line->qty_request_primary = $request->qtyorder[$line->line_id];
                  $line->qty_request =  $request->qtyorder[$line->line_id]/$line->conversion_qty;
                }
              }elseif($line->uom==$uom[$line->line_id]){
                if($line->qty_request != $request->qtyorder[$line->line_id]){
                  $ubah=true;
                  $line->qty_request = $request->qtyorder[$line->line_id];
                  $line->qty_request_primary = $request->qtyorder[$line->line_id]*$line->conversion_qty;
                }
              }
              $line->amount = $line->qty_request*$line->unit_price;
              if(($line->tax_type)=="10%")
              {
                $line->tax_amount = 0.1 * $line->amount;
              }
              $line->save();
            }
            if($ubah){
              app('App\Http\Controllers\DPLController')->dpllog($soheader->suggest_no,"Update PO Pengajuan DPL");
            //if(isset($soheader->suggestno)){
              $notified_users = app('App\Http\Controllers\DPLController')->getArrayNotifiedEmail($soheader->suggest_no);
              //dd($notified_users);
              if(!empty($notified_users)){
                $data = [
                  'title' => 'Update PO Pengajuan DPL',
                  'message' => 'Trx '.$soheader->notrx.' Pengajuan DPL #'.$soheader->suggest_no.' telah diubah',
                  'id' => $soheader->suggest_no,
                  'href' => route('dpl.readNotifApproval'),
                  'mail' => [
                    'greeting'=>'',
                    'content'=> ''
                  ]
                ];
                foreach ($notified_users as $key => $email) {
                  foreach ($email as $key => $mail) {
                    $data['email'] = $mail;
                    $data['sendmail'] = 0;
                    $apps_user = User::where('email',$mail)->first();
                    if(!empty($apps_user))
                      $apps_user->notify(new PushNotif($data));
                  }
                }
              }
            }
            DB::commit();
            return redirect()->route('order.listPO')->withMessage(trans('pesan.update'));
          }else{
            return redirect()->back()->withError("Gagal simpan! PO telah diproses Galenium")->withInput();
          }

        }catch(\Exception $e) {
          DB::rollback();
          throw $e;
        }
      }
    }

}
