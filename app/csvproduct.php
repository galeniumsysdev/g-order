<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class csvproduct extends Model
{
    protected $table = 'csv_product';

    protected $fillable = ['customer_id', 'kode_product', 'nama_product', 'quantity', 'uom', 'created_at', 'updated_at'];
}
