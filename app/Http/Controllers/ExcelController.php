<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;
use DB;
use File;

class ExcelController extends Controller
{
    public function ExportClients(Request $request)
    {
    //  DB::enableQueryLog();
      $distname=$request->distributor;
    	$customer = DB::table('customers as c')
    				->join('customer_sites as cs','c.id', 'cs.customer_id')
    				->leftjoin('subgroup_datacenters as sd','c.subgroup_dc_id','sd.id')
            ->leftjoin('asps_regencies as a','cs.city_id','a.regency_id')
            ->leftjoin('org_structure_psc as osp', function($join)
                         {
                             $join->on('a.kode_asps', '=', 'osp.kode')
                                  ->on('osp.Jabatan','=',DB::raw("'ASPS'"))
                                  ->on('c.psc_flag','=',DB::raw("'1'"));
                         })
           ->leftjoin('org_structure_psc as aspm', function($join)
                        {
                            $join->on('osp.parent', '=', 'aspm.kode')
                                 ->on('aspm.Jabatan','=',DB::raw("'ASPM'"))
                                 ->on('c.psc_flag','=',DB::raw("'1'"));
                        })
            ->leftjoin('org_structure_psc as rsm', function($join)
                         {
                             $join->on('aspm.parent', '=', 'rsm.kode')
                                  ->on('rsm.Jabatan','=',DB::raw("'RSPM'"))
                                  ->on('c.psc_flag','=',DB::raw("'1'"));
                         })
    				->whereNull('c.oracle_customer_id')
            ->where('cs.primary_flag','=','Y');
      if($request->distributor)
      {
        /*$customer = $customer->whereExists(function ($query) use($distname) {
                $query->select(DB::raw(1))
                  ->from('outlet_distributor as od1')
                  ->join('customers as dist1','dist1.id','=','od1.distributor_id')
                  ->where('od1.outlet_id','=','c.id')
                  ->whereraw("upper(dist1.customer_name) like upper('%".$distname."%')");
            });*/
          $customer = $customer->whereRaw("exists (select 1 from outlet_distributor as od1, customers as dist1 where dist1.id = od1.distributor_id and od1.outlet_id =c.id and upper(dist1.customer_name) like upper('%".$distname."%') )")  ;
      }
    	if($request->psc_flag=="1" and $request->pharma_flag!="1")
        {
            $customer = $customer->where('psc_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag!="1")
        {
            $customer = $customer->where('pharma_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag=="1")
        {
            $customer = $customer->where(function ($query) {
              $query->where('pharma_flag','=','1')
                    ->orWhere('psc_flag','=','1');
            });
        }
        //dd($request->subgroupdc);
        if($request->subgroupdc)
        {
          $customer = $customer->whereIn('subgroup_dc_id',$request->subgroupdc);
        }
        if($request->city)
        {
          $customer = $customer->whereraw("upper(cs.city) like upper('".$request->city."')");
        }

		//---//
		if($request->subgroupdc)
        {
          $customer = $customer->whereIn('subgroup_dc_id',$request->subgroupdc);
        }
        if($request->province)
        {
          $customer = $customer->whereraw("upper(cs.province) like upper('".$request->province."')");
        }

    	$customer = $customer->select('c.id','c.customer_name','cs.province','cs.city','c.longitude','c.langitude','c.created_at','c.Status','c.psc_flag','c.pharma_flag','c.subgroup_dc_id','sd.name as subgroup_name'
                                    ,'osp.kode as asps_kode','aspm.kode as aspm_kode','rsm.kode as rspm_kode');
    	$customer = $customer->get();
      //var_dump(DB::getQueryLog());
      //dd($customer);
      foreach($customer as $c){
        $dist = DB::table('outlet_distributor as od')
          ->join('customers as dist','od.distributor_id','dist.id')
          ->where('od.outlet_id','=',$c->id);
        if($request->distributor)
        {
            $dist=$dist->whereraw("upper(dist.customer_name) like upper('%".$request->distributor."%')");
        }
        $dist=$dist->select('dist.customer_name')->get();
        $c->distributor = $dist;
      }

    	Excel::create('Report_NOO', function($excel) use($customer,$request) {
    		$excel->sheet('Report_NOO', function($sheet) use ($customer,$request) {
    			$sheet->loadView('ExportClients',array('customers'=>$customer,'request'=>$request));
    		});
    	})->export('xlsx');

    }

    /*public function checkImageProduct()
    {
      $produk = DB::table('Products')
                ->whereRaw("ifnull(imagePath,'')!=''")
                ->get();
      $create= Excel::create('dataproduk', function($excel) use ($produk ) {
        $excel->setTitle('Produk Galenium');
        $excel->setCreator('Shanty')
          ->setCompany('Solinda');
        $excel->sheet('produk', function($sheet) use ($produk)
        {
          foreach ($produk as $p)
          {
            if (!File::exists(public_path('img\\'.$p->imagePath))){
              $sheet->appendRow(array($p->itemcode,$p->title,$p->imagePath));
            }
          }
        });
      })->export("xlsx");
    }*/
}
