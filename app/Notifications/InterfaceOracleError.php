<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\SoHeader;

class InterfaceOracleError extends Notification
{
    use Queueable;
    public $header;
    public $distributor;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(SoHeader $soheader, $distrname)
    {
        $this->header = $soheader;
        $this->distributor  = $distrname;
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
      $message = new MailMessage;
      $message->subject('Trx '.$this->header->notrx.' gagal terinterface ke Oracle')
              ->greeting('Hai '.$this->distributor)
              ->line('Kami informasikan bahwa interface gOrder ke oracle untuk no: '.$this->header->notrx.' gagal terinterface ke Oracle.')
              ->line('Silahkan login ke aplikasi oracle untuk memperbaiki data yang dibutuhkan.') ;
      $message->line('Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order'));

      return $message;
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
        'tipe'=>'Interface oracle error',
        'subject'=>'No Trx: '.$this->header->notrx.' gagal terinterface ke oracle.',
        'order'=>$this->header
      ];
    }
}
