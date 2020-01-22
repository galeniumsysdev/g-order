<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductFlexfield extends Model
{
  protected $table = 'product_flexfield';
  protected $fillable=['flex_value_id','flex_value','description','enabled_flag','summary_flag'];
  protected $hidden = [
      'flex_value_set_id'
  ];
  protected $primaryKey = ['flex_value_set_id', 'flex_value_id'];
  public $incrementing = false;

  public function products()
  {
      return $this->belongsToMany('App\Product','product_categories','flex_value_id','product_id');
  }
}
