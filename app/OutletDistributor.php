<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutletDistributor extends Model
{
  protected $table = 'outlet_distributor';
  protected $fillable=['outlet_id','distributor_id','approval','keterangan','tgl_approve','inactive','end_date_active'];
  protected $primaryKey = ['outlet_id', 'distributor_id'];
  public $incrementing = false;
}
