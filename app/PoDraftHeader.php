<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoDraftHeader extends Model
{
  protected $fillable = [
      'id','distributor_id','customer_id','currency','psc_flag','psc_flag','pharma_flag','export_flag','tollin_flag','subtotal','discount','Tax','amount'      
  ];

  public function lines()
  {
      return $this->hasMany('App\PoDraftLine','po_header_id');
  }
}
