<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoDraftLine extends Model
{
  protected $fillable = [
      'id','po_header_id','product_id','uom','qty_request','qty_request_primary'
      ,'primary_uom','conversion_qty','inventory_item_id','list_price','unit_price','amount'
      ,'discount','jns','tax_amount'
  ];
  public function header()
  {
    return $this->belongsTo('App\PoDraftHeader','po_header_id');
  }
  public function item()
  {
    return $this->belongsTo('App\Product','product_id');
  }
}
