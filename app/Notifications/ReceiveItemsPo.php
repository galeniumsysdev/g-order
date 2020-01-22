<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Customer;
use App\SoHeader;


class ReceiveItemsPo extends Notification
{
    use Queueable;
    public $header;
    public $nosuratjalan;
    public $distributor;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(SoHeader $h, $no_sj)
    {
        $this->header=$h;
        $this->nosuratjalan = $no_sj;
        $this->distributor = Customer::find($h->distributor_id)->select('customer_name')->first();
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
                    ->subject('SJ: '.$this->nosuratjalan.' telah diterima customer')
                    ->greeting('Hai '.$this->distributor->customer_name)
                    ->line('Pesanan Anda dengan PO nomor: <strong>'.$this->header->customer_po.'</strong> dan SJ: '.$this->nosuratjalan.' telah diterime customer.')
                    ->line('Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order').'.') ;
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
        'tipe'=>'PO diterima customer',
        'subject'=>'PO: '.$this->header->customer_po.' telah diterima customer.',
        'order'=>$this->header
      ];
    }
}
