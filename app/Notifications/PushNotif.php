<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\PusherPushNotifications\PusherChannel;
use NotificationChannels\PusherPushNotifications\PusherMessage;
use Illuminate\Notifications\Messages\MailMessage;
use \App\Events\PusherBroadcaster;

class PushNotif extends Notification {
	public function __construct($data) {
		$this->data = $data;
	}

	public function via($notifiable) {
		if(array_key_exists('sendmail', $this->data)){
			if($this->data['sendmail']){
				return ['broadcast', PusherChannel::class, 'database', 'mail'];
			}
			else{
				return ['broadcast', PusherChannel::class, 'database'];
			}
		}
		else{
			return ['broadcast', PusherChannel::class, 'database', 'mail'];
		}
	}

	public function toBroadcast($notifiable) {
		$this->data['href'] = $this->data['href'].'/'.$this->data['id'].'/'.$this->id;
		return event(new PusherBroadcaster($this->data, $this->data['email']));
	}

	public function toPushNotification($notifiable) {
		$this->data['href'] = $this->data['href'].'/'.$this->data['id'].'/'.$this->id;
		return PusherMessage::create()
			->android()
			->title($this->data['title'])
			->setOption('custom', $this->data)
			->body($this->data['message'])
			->withiOS(PusherMessage::create()
				->title($this->data['message'])
				->setOption('custom', $this->data));
	}

	public function toMail($notifiable)
	{
		if(array_key_exists('markdown', $this->data['mail'])){
			return (new MailMessage)
			          ->subject($this->data['title'])
			          ->markdown($this->data['mail']['markdown'], $this->data['mail']['attribute']);
		}
		else{
			return (new MailMessage)
				->subject($this->data['title'])
				->greeting($this->data['mail']['greeting'])
				->line($this->data['mail']['content']);
		}
	}

	/**
     * Get the array representation of the notification.
     *pa
     * @param  mixed  $notifiable
     * @return array
     */
	public function toDatabase($notifiable)
	{
		$this->data['href'] = $this->data['href'].'/'.$this->data['id'].'/'.$this->id;
		return [
			'tipe'=> $this->data['title'],
			'subject'=> $this->data['message'],
			'id'=> $this->data['id'],
			'href'=> $this->data['href']
		];
	}
}
