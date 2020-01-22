<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UomConversion extends Model
{
  protected $table = 'mtl_uom_conversions_v';
  protected $fillable = [
    'product_id','uom_code','rate'
  ];
  /*protected $table = 'uom_conversions';
  protected $primaryKey = ['product_id','uom_code','base_uom'];

  protected $fillable = [
    'product_id','uom_code','rate','uom_class','base_uom','width','height','dimension_uom'
  ];*/


}
