<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\EntrustRole;


class Role extends EntrustRole
{
     protected $fillable=['name','display_name','description','created_by','last_update_by'];
     public function users() {
        return $this->belongsToMany('App\User');
    }
}
