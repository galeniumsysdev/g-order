<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\FileCMO;

class RejectCmo extends Notification
{
    use Queueable;
    public $filescmo;
    public $customer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FileCMO $files)
    {
        $this->filescmo = $files;
        $this->customer = $files->getDistributor->customer_name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','Database'];
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
                  ->subject('File CMO period: '.$this->filescmo->period.' telah ditolak.')
                  ->greeting('File CMO period: '.$this->filescmo->period.' telah ditolak.')
                  ->line('Mohon maaf, Harap upload kembali file CMO Anda untuk period: '.$this->filescmo->period.'.')
                  ->line('Silahkan konfirmasi ke Yasa Mitra Perdana untuk penjelasan lebih detail.');
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
          'tipe'=>'Penolakan CMO',
          'subject'=>'Penolakan CMO '.$this->customer.' untuk period '.$this->filescmo->period,
          'cmo'=>$this->filescmo,
      ];
    }
}
