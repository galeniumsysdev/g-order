<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Category;
use App\Customer;
use Auth;
use Session;
use App\Banner;
use DB;
use App\Observers\UserActionsObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      \App\User::observe(new UserActionsObserver);
      \App\Customer::observe(new UserActionsObserver);
      \App\CustomerSite::observe(new UserActionsObserver);
      \App\CustomerContact::observe(new UserActionsObserver);
      \App\OutletStock::observe(new UserActionsObserver);
      \App\SoShipping::observe(new UserActionsObserver);
      \App\DPLSuggestNo::observe(new UserActionsObserver);
      \App\SoHeader::observe(new UserActionsObserver);
      \App\SoLine::observe(new UserActionsObserver);
      \App\SoShipping::observe(new UserActionsObserver);
      \App\Product::observe(new UserActionsObserver);
      \App\DPLNo::observe(new UserActionsObserver);
      \App\OrgStructure::observe(new UserActionsObserver);
      \App\Role::observe(new UserActionsObserver);
      \App\Permission::observe(new UserActionsObserver);
      \App\Banner::observe(new UserActionsObserver);
      \App\Category::observe(new UserActionsObserver);
      \App\CategoryOutlet::observe(new UserActionsObserver);
      \App\OutletProducts::observe(new UserActionsObserver);
      \App\FileCMO::observe(new UserActionsObserver);
      \App\GroupDatacenter::observe(new UserActionsObserver);
      \App\SubgroupDatacenter::observe(new UserActionsObserver);
      view()->composer(['layouts.navbar_product','shop.product','swipe'], function($view)
     {
        $product_flexfields = Category::where([//ProductFlexfield::where([
                                        ['enabled_flag','=','Y']
                                        ,['summary_flag','=','N']
                                        //,['flex_value_set_id','=',config('constant.flex_value_set_id')]
                              ]);
        if(isset(Auth::user()->customer_id)){
          $customer = Customer::find(Auth::user()->customer_id);

            if($customer->psc_flag!="1" )
            {
              $product_flexfields =$product_flexfields->where('parent','not like','PSC');
            }
            if($customer->pharma_flag!="1")
            {
              $product_flexfields =$product_flexfields->where('parent','not like','PHARMA');
            }
            if($customer->export_flag!="1" )
            {
              $product_flexfields =$product_flexfields->where('parent','not like','INTERNATIONAL');
            }

          if($customer->tollin_flag==1)
          {
            $product_flexfields =$product_flexfields->whereRaw("parent='TollIn'
                                  and exists (select 1 from category_products as cp, products as p, qp_list_lines_v as qll
                                              where cp.flex_value=categories.flex_Value
                                              and cp.product_id = p.id
                                              and p.inventory_item_id = qll.product_attr_value
                                              and qll.list_header_id = '".$customer->price_list_id."')");

          }else{
            $product_flexfields =$product_flexfields->where('parent','not like','TollIn');
          }
          if(Auth::user()->hasRole('Apotik/Klinik') or Auth::user()->hasRole('Outlet'))
          {
            $product_flexfields = $product_flexfields->where('description','<>','BPJS');
          }

        }
        //dd($product_flexfields->toSQL());
        $product_flexfields =$product_flexfields->orderBy('flex_value')->get() ;
        $countbrg=null;
        $polineexist=collect([]);
        if(Auth::check()){
          if(Auth::user()->can('Create PO'))
          {
            DB::table('po_draft_lines')->whereraw("exists (select 1
                      from po_draft_headers
                      where po_draft_headers.id = po_draft_lines.po_header_id
                      and date_format(created_at,'%Y-%m-%d') < date_format(now(),'%Y-%m-%d'))"
                    )->delete();
            DB::table('po_draft_headers')->whereraw("date_format(created_at,'%Y-%m-%d') < date_format(now(),'%Y-%m-%d')")->delete();
            $polineexist = DB::table('po_draft_lines')
                    ->join('po_draft_headers as pdh', 'po_draft_lines.po_header_id','=','pdh.id')
                    ->where('pdh.customer_id','=',Auth::user()->customer_id)
                    ->select('product_id','qty_request','uom')
                    ->get();
            //dd($polineexist->where('product_id','07357b00-d597-11e7-b583-532b9b50c290'))->sum('qty_request')        ;
            $jmlbrg =   $polineexist->count();
            $countbrg = $jmlbrg;

          }
        }
      // dd(DB::getQueryLog());
        //View::share('product_flexfields', $product_flexfields);
        $view->with(['product_flexfields'=> $product_flexfields,'countbrg'=>$countbrg,'polineexist'=>$polineexist]);
      });
     view()->composer('shop.carausel', function($view)
     {
       $banners = Banner::where('publish_flag','=','Y')->orderBy('id')->get();
       $view->with('banners', $banners);
     });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
