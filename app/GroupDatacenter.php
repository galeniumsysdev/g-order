<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupDatacenter extends Model
{
     protected $fillable=['name','display_name','enabled_flag','created_by','last_update_by'];
     public function subgroupdatacenter()
     {
         return $this->hasMany('App\SubgroupDatacenter','group_id');
     }
}
