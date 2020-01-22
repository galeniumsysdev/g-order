<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PusherBroadcaster implements ShouldBroadcast {
	use SerializesModels;

	/**
	 * Only (!) Public members will be serialized to JSON and sent to Pusher
	 **/
	public $message;
	public $title;
	public $href;
	private $email;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($data, $email) {
		$this->message = $data['message'];
		$this->title = $data['title'];
		$this->href = $data['href'];
		$this->email = $email;
	}

	public function broadcastAs() {
		return 'notif';
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn() {
		return $this->email;
	}
}
