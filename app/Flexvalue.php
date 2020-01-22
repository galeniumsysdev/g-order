<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flexvalue extends Model
{
  public $incrementing = false;
  protected $table = 'flexvalue';
  protected $primaryKey = ['master','id'];
  protected $fillable = [
      'master','id','name','enabled_flag','created_at','updated_at'
  ];
}
