<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\EntrustPermission;


class Permission extends EntrustPermission
{
  protected $fillable=['name','description','display_name','created_by','last_update_by'];
}
