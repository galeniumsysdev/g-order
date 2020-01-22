<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
  use Uuids;

  public $incrementing = false;
  protected $fillable = [
      'id','customer_number', 'customer_name','oracle_customer_id', 'status', 'customer_category_code','customer_class_code','primary_salesrep_id','tax_reference','tax_code','price_list_id','order_type_id','customer_name_phonetic','pharma_flag','psc_flag'
      ,'outlet_type_id','subgroup_dc_id','longitude','langitude','id_approval_psc','id_approval_pharma','date_approval_psc','date_approval_pharma','keterangan','export_flag','tollin_flag','parent_dist','payment_term_name','created_by','last_update_by','ijin_pbf','no_ijin','masa_berlaku'
  ];

  public function users()
  {
      return $this->hasMany('App\User','customer_id');
  }
  public function user()
  {
     return $this->hasOne('App\User','customer_id')->orderBy('created_at','desc');
  }

  public function sites()
  {
      return $this->hasMany('App\CustomerSite');
  }

  public function contact()
  {
      return $this->hasMany('App\CustomerContact');
  }

  public function hasDistributor()
  {
    return $this->belongsToMany('App\Customer','outlet_distributor','outlet_id','distributor_id')->withTimestamps()->withPivot('created_by','last_update_by');
  }

  public function hasOutlet()
  {
    return $this->belongsToMany('App\Customer','outlet_distributor','distributor_id','outlet_id')->withTimestamps()->withPivot('created_by','last_update_by');
  }

  public function subgroupdc()
  {
    return $this->belongsTo('App\SubgroupDatacenter','subgroup_dc_id');
  }

  public function categoryOutlet()
  {
    return $this->belongsTo('App\CategoryOutlet','outlet_type_id');
  }

  public function cmo()
  {
      return $this->hasMany('App\FileCMO');
  }

  public function cabang()
  {
      return $this->hasMany('App\Customer','parent_dist');
  }

  public function pusat()
  {
      return $this->belongsTo('App\Customer','parent_dist');
  }

  public function purchaseorders()
  {
    return $this->hasMany('App\SoHeader','customer_id');
  }

  public function salesorders()
  {
    return $this->hasMany('App\SoHeader','distributor_id');
  }


}
