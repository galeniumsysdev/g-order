<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\User;

class InvitationUser extends Notification
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
      $message = new MailMessage;
      $message->subject('Account Baru gOrder')
              ->greeting('Hai '.$this->user->name.',')
              ->line('Kami dari Principal Galenium Pharmashia Laboratories mengundang Anda untuk dapat menggunakan Aplikasi gOrder yang merupakan aplikasi untuk menerima dan membuat PO untuk product-product kami. Untuk menggunakan aplikasi ini, Silahkan login pertama kali dengan mengklik tombol dibawah ini.')
              ->action('Verifikasi email', url(route('confirmation', $this->user->api_token)))
              ->success();
      $message->line('Email ini asli dan bukan penipuan. Silahkan konfirmasi ke Sales kami jika ada keraguan.');

      return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
