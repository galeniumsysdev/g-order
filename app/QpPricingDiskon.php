<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QpPricingDiskon extends Model
{
  protected $table = 'qp_pricing_discount';
  protected $primaryKey  = null;// ['list_header_id','list_line_id','customer_id','ship_to_id','bill_to_id'];
  public $incrementing = false;
  protected $fillable = [
    'list_line_id','list_header_id','list_line_no','list_line_type_code','modifier_level_code'
    ,'item_id', 'operand','arithmetic_operator_code'
    , 'customer_id','ship_to_id','bill_to_id'
    ,'start_date_active'
    ,'end_date_active'
    ,'comparison_operator_code'
    ,'pricing_attribute_context','pricing_attr'
    ,'pricing_attr_value_from','pricing_attr_value_to'
    ,'qualifier_grouping_no','product_attr'
  ];

}
