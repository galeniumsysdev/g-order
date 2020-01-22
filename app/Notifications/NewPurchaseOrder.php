<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Customer;
use App\User;
use App\SoHeader;
use DB;
class NewPurchaseOrder extends Notification implements ShouldQueue
{
    use Queueable;
    public $user;
    public $customer;
    public $so_header_id;
    public $lines;
    public $total;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Array $data)
    {
        $this->customer = Customer::find($data['customer']);
        $this->user = User::find($data['user']);
        $this->so_header_id =SoHeader::where('id','=',$data['so_header_id'])->select('id','distributor_id','customer_id','customer_po','tgl_order','notrx')->first();
        $this->lines = DB::table('so_Lines_v')->where('header_id','=',$data['so_header_id'])->get();
        $this->total = DB::table('so_lines')
                ->where('header_id','=',$data['so_header_id'])
                ->sum(DB::raw('(IFNULL(amount,0)+IFNULL(tax_amount,0))'));

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];//,'broadcast'
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
      /*$message = new MailMessage;
      $message->subject('New PO From '.$this->customer->customer_name)
              ->greeting('Hai '.$this->user->name.',')
              ->line('Anda mendapatkan pesanan baru dari '.$this->customer->customer_name.' melalui aplikasi g-Order dengan nomor transaksi: <strong>'.$this->so_header_id->notrx.'</strong>. Silahkan buka aplikasi g-Order atau login ke web g-Order untuk melihat pesanan tersebut.');

      return $message;*/
      return (new MailMessage)
                ->subject('New PO From '.$this->customer->customer_name)
                ->markdown('emails.orders.create', ['so_headers' => $this->so_header_id,'lines'=>$this->lines,'total'=>$this->total,'customer'=>$this->customer->customer_name]);
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
          'tipe'=>'New PO',
          'subject'=>'New PO From '.$this->customer->customer_name,
          'from' => $this->customer->customer_name,
          'so_header_id' => $this->so_header_id
        ];
    }

    /*public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
          'tipe'=>'New PO',
          'subject'=>'New PO From '.$this->customer->customer_name,
          'from' => $this->customer->customer_name,
          'so_header_id' => $this->so_header_id
        ]);
    }*/
}
