<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable=['product_id','flex_value_id','enabled_flag'];
    protected $primaryKey = ['product_id', 'flex_value_id'];
    public $incrementing = false;
}
