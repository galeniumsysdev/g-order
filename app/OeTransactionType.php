<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OeTransactionType extends Model
{
  protected $table = 'oe_transaction_types';
  protected $primaryKey = 'transaction_type_id';
  protected $fillable = [
      'transaction_type_id','name','description','start_date_active'
        ,'end_date_active','currency_code','price_list_id','warehouse_id','org_id'
  ];
}
