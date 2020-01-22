<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable {
	use Notifiable;
	use EntrustUserTrait;
	//use Uuids;

  public $incrementing = false;

	public function routeNotificationForPusherPushNotifications() {
		return $this->email;
	}

	/**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'id','name', 'email', 'password','validate_flag','register_flag','customer_id','api_token','code_verifikasi','first_login','created_by','last_update_by',
  ];
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token'
	];

	/*public function hasAnyRole($roles)
		    {
		      if (is_array($roles)){
		        foreach $roles as $role{
		          if($this->hasRole($role))
		          {
		            return true;
		          }
		        }
		      }else{
		        if($this->hasRole($role))
		        {
		          return true;
		        }
		      }
		      return false;
	*/

	public function hasRole($role) {
		if ($this->roles()->where('name', $role)->first()) {
			return true;
		} else {
			return false;
		}
	}

	public function roles() {
		return $this->belongsToMany('App\Role');
	}

	public function customer() {
		return $this->belongsTo('App\Customer');
		//->select(array('id','customer_name','pharma_flag','psc_flag','tax_reference','outlet_type_id','subgroup_dc_id'));
	}
}
