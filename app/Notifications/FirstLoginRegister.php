<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\User;

class FirstLoginRegister extends Notification
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
      $message->subject('Success Register')
              ->greeting('Hai '.$this->user->name.',')
              ->line('Data registrasi outlet anda sudah berhasil diverifikasi. Silahkan masukkan alamat email dan password anda dengan mengklik button dibawah ini.')
              ->action('Login', url(route('verification', $this->user->api_token)))
              ->success();
      $message->line('Email ini dibuat secara otomatis. Mohon tidak mengirimkan balasan ke email ini.');

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
