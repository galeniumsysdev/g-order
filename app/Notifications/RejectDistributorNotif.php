<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\User;
use App\Customer;
use App\OutletDistributor;

class RejectDistributorNotif extends Notification
{
    use Queueable;
    public $outletdistributor;
    public $user;
    public $outletname;
    public $distname;
    public $useroutlet;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(OutletDistributor $outletdist,User $user)
    {
        $this->outletdistributor = $outletdist;
        $this->useroutlet = User::where([
          ['customer_id','=',$outletdist->outlet_id],
          ['register_flag','=',false]
        ])->select('id','name')->first();
        $this->outletname = Customer::where('id','=',$outletdist->outlet_id)->select('customer_name')->first();
        $this->distname = Customer::where('id','=',$outletdist->distributor_id)->select('customer_name')->first();
        //dd($this->outletname."-".$this->distname."-".$this->outletname->customer_name."-".$this->distname->customer_name);
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
        $outlet = Customer::find($this->outletdistributor->outlet_id);
        if($outlet)
        {
          return (new MailMessage)
                      ->Subject('Reject Outlet')
                      ->line('Outlet '.$this->outletname->customer_name.' telah di reject oleh Distributor '.$this->distname->customer_name.' dengan alasan: '. $this->outletdistributor->keterangan.'.')
                      ->line('Silahkan buka aplikasi gStore untuk menambahkan distributor lain.');
        }

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
          'tipe'=>'Reject Outlet',
          'subject'=>'Reject Outlet '.$this->outletname->customer_name.' oleh '.$this->distname->customer_name,
          'user'=>$this->useroutlet,
          'outletdistributor'=>$this->outletdistributor,
          'outletname'=>$this->outletname->customer_name,
          'distname'=>$this->distname->customer_name
      ];
    }
}
