<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\User;
use App\Customer;

class NewoutletDistributionNotif extends Notification
{
    use Queueable;
    public $user;
    public $customer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Customer $customer)
    {
          $this->user = $user;
          $this->customer=$customer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
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
          'subject'=>'Penunjukan Outlet '.$this->user->name.' ke Distributor '.$this->customer->customer_name,
          'distributor'=>$this->customer->id,
          'outlet'=>$this->user,
      ];
    }
}
