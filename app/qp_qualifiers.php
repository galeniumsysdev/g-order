<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class qp_qualifiers extends Model
{
  protected $table = 'qp_qualifiers_v';
  protected $primaryKey = 'qualifier_id';
  protected $fillable = [
    'qualifier_id','excluder_flag','comparision_operator_code','qualifier_context','qualifier_attribute'
    ,'qualifier_grouping_no','qualifier_attr_value','list_header_id','list_line_id','start_date_active'
    ,'end_date_active','qualifier_datatype','qualifier_precedence'
  ];
}
