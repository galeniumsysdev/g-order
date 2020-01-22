<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
  protected $fillable = [
      'contact_name','contact_type','contact','customer_id','contact_point_id','created_by','last_update_by'
  ];
  protected $hidden = [
      'oracle_customer_id', 'account_number',
  ];

  public function customer()
  {
    return $this->belongsTo('App\Customer');
  }
}
