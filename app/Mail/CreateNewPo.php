<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\SoHeader;
use DB;

class CreateNewPo extends Mailable
{
    use Queueable, SerializesModels;
    public $so_headers;
    public $lines;
    public $total;
    public $customer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SoHeader $header)
    {
        $this->so_headers =$header;
        $this->lines = DB::table('so_Lines_v')->where('header_id','=',$this->so_headers->id)->get();
        $this->total = DB::table('so_lines')
                ->where('header_id','=',$this->so_headers->id)
                ->sum(DB::raw('(IFNULL(amount,0)+IFNULL(tax_amount,0))'));
        $this->customer="";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.orders.create');
    }
}
