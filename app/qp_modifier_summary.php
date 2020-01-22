<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class qp_modifier_summary extends Model
{
  protected $table = 'qp_modifier_summary_v';
  protected $primaryKey = 'list_line_id';
  protected $fillable = [
    'list_line_id','list_header_id','list_line_type_code','automatic_flag','modifier_level_code'
    ,'list_price','list_price_uom_code','primary_uom_flag','inventory_item_id','organization_id'
    ,'operand','arithmetic_operator','override_flag','print_on_invoice_flag','start_date_active'
    ,'end_date_active','pricing_group_sequence','incompatibility_grp_code','list_line_no','product_precedence','pricing_phase_id'
    ,'pricing_attribute_id','product_attribute_context','product_attr','product_attr_val'
    ,'product_uom_code','comparison_operator_code','pricing_attribute_context','pricing_attr'
    ,'pricing_attr_value_from','pricing_attr_value_to','pricing_attribute_datatype'
    ,'product_attribute_datatype'
  ];
}
