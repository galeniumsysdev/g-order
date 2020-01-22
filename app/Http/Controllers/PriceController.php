<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PriceController extends Controller
{
    public function index(){
      $menu="pricelist";
      $data = DB::table('qp_list_lines_v as qpl')
            ->join('qp_list_headers as qph','qph.list_header_id','qpl.list_header_id')
            ->leftjoin('products as p','qpl.product_attr_value','=','p.inventory_item_id')
            ->select('qph.name as price_name', 'p.title as nm_product', 'qpl.product_uom_code', 'qpl.operand' ,
                'qpl.start_date_active', 'qpl.end_date_active', 'qpl.enabled_flag')
            ->orderby('qph.list_header_id','p.title')
            ->get();
      return view('admin.oracle.priceindex',compact('data','menu'));
    }

    public function diskonIndex(Request $request){
      DB::enableQueryLog();
       $menu="diskon";
       $data = DB::table('qp_list_headers as qlh')
              ->join('qp_pricing_discount as qpd','qpd.list_header_id','qlh.list_header_id')
              ->join('products as p','p.inventory_item_id','qpd.item_id')
              ->whereraw("qpd.list_line_type_code = 'DIS'")
              ->whereRaw("now() between ifnull(qlh.start_date_active,date('2017-01-01'))
        and ifnull(qlh.end_date_active,DATE_ADD(now(),INTERVAL 1 day))")
              ->whereRaw("now() <= ifnull(qpd.end_date_active,DATE_ADD(now(),INTERVAL 1 day))")
              ->whereraw("ifnull(qpd.orig_org_id,'".config('constant.org_id')."')= '".config('constant.org_id')."'")
              ->select('qlh.name as price_name','p.title as itemdesc','p.itemcode','p.id','qpd.list_header_id','qpd.list_line_id','qpd.operand'
                      ,DB::raw("ifnull((select customer_name from customers as c where c.oracle_customer_id = qpd.customer_id ),qpd.customer_id) as cust_name")
                      , 'qpd.start_date_active','qpd.end_date_active'
                      );

        if(isset($request->cust_id))
        {
          $custid=$request->cust_id;
          $data = $data->whereExists(function($cond) use($custid) {
              $cond->select(DB::raw(1))
                  ->from('customers')
                  ->whereraw("customers.oracle_customer_id=qpd.customer_id and customers.id='".$custid."'");
          });
        }
        if(isset($request->product_id))
        {
          $data = $data->where('p.id','=',$request->product_id);
        }
        if(isset($request->price_headers_id))
        {
          $data = $data->where('qpd.list_header_id','=',$request->price_headers_id);
        }

        $data=$data->orderBy('qlh.name','asc')->orderBy('p.title','asc')->get();
        //var_dump(DB::getQueryLog());
          return view('admin.oracle.diskonindex',compact('data','menu'));
    }

    public function searchDiskon(){
       $menu="diskon";
       $price = DB::table('qp_list_headers as qlh')->where('list_type_code','=','DLT')
        ->where('active_flag','=','Y')
        ->whereraw("ifnull(orig_org_id,'".config('constant.org_id')."')= '".config('constant.org_id')."'")
        ->whereRaw("now() between ifnull(start_date_active,date('2017-01-01'))
  and ifnull(end_date_active,DATE_ADD(now(),INTERVAL 1 day))")
        ->select('list_header_id','name')
        ->orderBy('qlh.name')
        ->get();
       return view('admin.oracle.searchdiskon',compact('price','menu'));
    }


    public function ajaxSearchProduct(Request $request)
    {
      $data = DB::table('products')->where("title","LIKE",$request->input('query')."%")
            ->where('Enabled_flag','=','Y');

      $data = $data->select('id','title','itemcode');
      $data = $data->orderBy('title','asc')->get();
      return response()->json($data);
    }

    public function ajaxPriceList()
    {
      $price = DB::table('qp_list_headers as qlh')->where('list_type_code','=','PRL')
       ->where('active_flag','=','Y')
      // ->whereraw("ifnull(orig_org_id,'".config('constant.org_id')."')= '".config('constant.org_id')."'")
       /*->whereRaw("now() between ifnull(start_date_active,date('2017-01-01'))
 and ifnull(end_date_active,DATE_ADD(now(),INTERVAL 1 day))")*/
       ->select('list_header_id','name')
       ->orderBy('qlh.name')
       ->get();
       return response()->json($price);
    }
}
