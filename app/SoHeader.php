<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class SoHeader extends Model
{
  protected $fillable = [
      'id','distributor_id','customer_id','cust_ship_to','cust_bill_to','customer_po','file_po','approve','tgl_order','currency','payment_term_id','price_list_id','order_type_id','oracle_ship_to','oracle_bill_to','oracle_header_id','oracle_customer_id','tgl_kirim','status','notrx','tgl_terima'
      ,'org_id','warehouse','interface_flag','alasan_tolak','suggest_no','dpl_no','split_from_id','npwp','created_by','last_update_by'
  ];

  public function line()
  {
      return $this->hasMany('App\SoLine','header_id');
  }

  public function shippings()
  {
    return $this->hasMany('App\SoShipping','header_id');
  }

  public function distributor()
  {
    return $this->belongsTo('App\Customer','distributor_id');
  }

  public function outlet()
  {
    return $this->belongsTo('App\Customer','customer_id');
  }
}
