<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\User;

class MarketingGaleniumNotif extends Notification
{
    use Queueable;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
              ->subject('New Registration Outlet/Distributor')
              ->markdown('emails.marketinggaleniumnotif',['user'=>$this->user]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'tipe'=>'Register Outlet',
            'subject'=>'Register Outlet '.$this->user->name,
            'user'=>$this->user
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'tipe'=>'Register Outlet',
            'subject'=>'Register Outlet '.$this->user->name,
            'user'=>$this->user
        ]);
    }
}
