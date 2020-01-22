<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QpListLine extends Model
{
  protected $table = 'Qp_list_lines_v';
  protected $primaryKey = 'list_line_id';
  protected $fillable = [
      'list_header_id','list_line_id','product_attribute_context','product_attr_value'
      ,'product_uom_code','start_date_active','end_date_active','revision_date','operand'
      ,'currency_code','enabled_flag'
  ];
}
