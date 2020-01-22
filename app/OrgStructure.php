<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OrgStructure extends Model
{
    use Notifiable;
    protected $table = 'org_structure';

    public function routeNotificationForPusherPushNotifications() {
		// return $this->email;
		return 'shanty29.dewi@solinda.co.id';
	}
}
