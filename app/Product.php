<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class Product extends Model
{
  use Uuids;

    public $incrementing = false;
    protected $fillable = ['imagePath','title','description','description_en','price','satuan_primary','satuan_secondary','inventory_item_id','itemcode','Enabled_Flag','pareto','tipe_dot','long_description','conversion'];

    public function categories()
    {
       return $this->belongsToMany('App\Category','category_products','product_id','flex_value')->withTimestamps()->withPivot('created_by','last_update_by');
      /*return $this->hasManyThrough(
            'App\Category',
            'App\CategoryProduct',
            'product_id', // Foreign key on users table...
            'flex_value', // Foreign key on posts table...
            'id', // Local key on countries table...
            'flex_Value' // Local key on users table...
        );*/
    }

    public function uom()
    {
      return $this->hasMany('App\UomConversion','product_id');
    }

    public function soshippings()
    {
        return $this->hasMany('App\SoShipping','product_id');
    }
    public function solines()
    {
        return $this->hasMany('App\SoLine','product_id');
    }

    public function podraftlines()
    {
        return $this->hasMany('App\PoDraftLine','product_id');
    }

    public function getPrice($id,$uom)
    {
      $hargadiskon = DB::select("select getDiskonPrice ( :cust, :prod, :uom, 1 ) AS harga from dual", ['cust'=>$id,'prod'=>$this->id,'uom'=>$uom]);
      if($hargadiskon)
      {
          return $hargadiskon[0]->harga;
      }else{
          return $this->getRealPrice($id,$uom);
      }

    }


        // public function getPriceByKode($kode,$uom)
        // {
        //   $rsproduct = DB::select("select id from products where itemcode = '$kode'");
        //
        //       return $this->getPrice($rsproduct[0]->id,$uom);
        //
        //
        // }


    public function getRealPrice($id,$uom)
    {
      $harga = DB::select("select getProductPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$id,'prod'=>$this->id,'uom'=>$uom]);
      if($harga) return $harga[0]->harga;else return 0;
    }

    public function getConversion($uom)
    {
      $rate = DB::select("select getItemRate ( :primary_uom, :uom, :id ) AS rate from dual", ['id'=>$this->id,'primary_uom'=>$this->satuan_primary,'uom'=>$uom]);
      if($rate)
      {
        return $rate[0]->rate;
      }else{
        return 0;
      }
    }

    public function getRateDiskon()
    {
      $diskon = DB::table('list_discount_v')
              ->where([
              ['product_id','=',$this->inventory_item_id]
                ,['customer_id','=',Auth::user()->customer->oracle_customer_id]
              ])->select('pricing_group_sequence',DB::raw("sum(operand) as operand"))
              ->groupBy('pricing_group_sequence')
              ->get();
      return $diskon;
    }

    public function getPromo()
    {
      $prg=null;
      if(Auth::user()->customer->oracle_customer_id){
      $prg =DB::table('qp_pricing_discount as qpd')
          ->join('qp_pricing_attr_get_v as qpa','qpd.list_line_id','=' , 'qpa.parent_list_line_id')
          ->where('qpd.list_line_type_code', '=','PRG')
          ->where('customer_id','=',Auth::user()->customer->oracle_customer_id)
          ->where('item_id','=',$this->inventory_item_id)
          ->whereraw("current_date between ifnull(start_date_active,date('2017-01-01')) and ifnull(end_date_active,DATE_ADD(CURRENT_DATE,INTERVAL 1 day))")
          ->select('qpd.item_id',  'qpd.ship_to_id', 'qpd.bill_to_id','qpd.pricing_attr_value_from', 'qpd.price_break_type_code','qpa.product_attr_value', 'qpa.benefit_qty', 'qpa.benefit_uom_code', 'qpa.benefit_limit')
          ->orderBy('pricing_group_sequence','asc')
          ->first();
      }
      return $prg;

    }



}
