<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QpListHeaders extends Model
{
  protected $table = 'Qp_list_headers';
  protected $primaryKey = 'list_header_id';
  protected $fillable = [
      'list_header_id','name','description','version_no','currency_code','start_date_active'
        ,'end_date_active','automatic_flag','list_type_code','terms_id','rounding_factor','discount_lines_flag'
        ,'active_flag','orig_org_id','global_flag'
  ];
}
