<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerSite extends Model
{
  protected $fillable = [
      'oracle_customer_id','cust_acct_site_id','site_use_id','site_use_code','status','payment_term_id','price_list_id','order_type_id','tax_code','address1','state','city','province','Country','org_id','langitude','longitude','customer_id','warehouse','district','primary_flag'
      ,'province_id','city_id','district_id','state_id','area','postalcode','created_by','last_update_by'
  ];

  public function customer()
  {
    return $this->belongsTo('App\Customer');
  }
}
