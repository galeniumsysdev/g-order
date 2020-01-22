<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Customer;
use App\SoHeader;

class ShippingOrderOracle extends Notification
{
    use Queueable;
    public $header;
    public $distr;
    public $customer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
     public function __construct(SoHeader $h, $custname)
     {
         $this->header = $h;
         $this->distr = Customer::where('id','=',$h->distributor_id)->select('customer_name')->first();
         $this->customer = $custname;
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
      $message->subject('PO '.$this->header->customer_po.' telah dikirim')
              ->greeting('Hai '.$this->customer)
            ->line('PO Anda nomor '.$this->header->customer_po.' telah dikirimkan oleh '.$this->distr->customer_name.'.')
              ->line('Silahkan check PO anda kembali.') ;
      $message->line('Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order'));

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
        'tipe'=>'Pengiriman PO',
        'subject'=>'PO '.$this->header->customer_po.' telah dikirim.',
        'order'=>$this->header
      ];
    }
}
