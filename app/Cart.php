<?php

namespace App;
use Auth;

//use Illuminate\Database\Eloquent\Model;

class Cart
{
  public $items = null;
  public $totalQty = 0;
  public $totalPrice = 0;
  public $totalTax = 0;
  public $totalDiscount = 0;
  public $totalAmount = 0;


  public function __construct($oldCart)
  {
    if ($oldCart){
      $this->items = $oldCart->items;
      $this->totalQty = $oldCart->totalQty;
      $this->totalPrice = $oldCart->totalPrice;
      $this->totalTax = $oldCart->totalTax;
      $this->totalDiscount = $oldCart->totalDiscount;
      $this->totalAmount = $oldCart->totalAmount;
    }
  }

  public function add($item,$id,$qty,$uom,$price,$disc){
      $storedItem = ['qty'=>0, 'uom'=>'', 'price'=>$price,'disc'=>$disc, 'amount'=>0, 'item'=>$item,'jns'=>null];
      if ($this->items){//jika ada array
        if(array_key_exists($id.'-'.$uom, $this->items)){
          $storedItem = $this->items[$id.'-'.$uom];
          $this->totalQty--;
        }
      }
        $storedItem['qty']+=$qty; //total product
        $storedItem['uom']= $uom;
        $storedItem['price']=$price;
        $storedItem['disc']=$price-$disc;
        $storedItem['jns']=$item->jns;
        $storedItem['amount']=$price* $storedItem['qty'];
        $this->items[$id.'-'.$uom] = $storedItem;
        $this->totalQty++;
        $this->totalPrice += $price*$qty;
        $this->totalDiscount += ($price-$disc)*$qty;
        //if(Auth::user()->customer->customer_category_code=="PKP")
        if(Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->Country=="ID"
        and Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->city!="KOTA B A T A M")
        {
          $this->totalTax =($this->totalPrice-$this->totalDiscount)*0.1;
        }else{
            $this->totalTax =0;
        }

        $this->totalAmount=$this->totalPrice-$this->totalDiscount+$this->totalTax;
  }

  public function removeItem($id){
      $this->totalQty-=1;
      $this->totalPrice -= $this->items[$id]['amount'] ;
      $this->totalDiscount -= ($this->items[$id]['disc']*$this->items[$id]['qty']);
      //if(Auth::user()->customer->customer_category_code=="PKP")
      if(Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->Country=="ID"
      and Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->city!="KOTA B A T A M")
      {
        $this->totalTax =($this->totalPrice-$this->totalDiscount)*0.1;
      }else{
          $this->totalTax =0;
      }

      $this->totalAmount=$this->totalPrice-$this->totalDiscount+$this->totalTax;
      unset($this->items[$id]);
  }

  public function editItem($id,$qty,$uom,$itemid,$harga,$disc){
      if ($this->items){//jika ada array
        if(array_key_exists($id, $this->items)){
          $storedItem = $this->items[$id];

          if($storedItem['qty']!=$qty or $storedItem['uom']!=$uom or $storedItem['price']!=$harga )
          {
            $this->totalPrice -= $this->items[$id]['amount'];
            $this->totalDiscount -= ($this->items[$id]['disc']*$this->items[$id]['qty']);
            $storedItem['qty']=$qty; //total product
            $storedItem['uom']= $uom;
            $storedItem['price']=$harga;
            $storedItem['disc']=$harga-$disc;
            $storedItem['amount']=$harga* $qty;
            $this->items[$id] = $storedItem;
            $this->totalPrice += $storedItem['amount'];
            $this->totalDiscount += ($harga-$disc)*$qty;
            //if(Auth::user()->customer->customer_category_code=="PKP")
            if(Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->Country=="ID"
            and Auth::user()->customer->sites->where('primary_flag','=','Y')->first()->city!="KOTA B A T A M")
            {
              $this->totalTax =($this->totalPrice-$this->totalDiscount)*0.1;
            }else{
                $this->totalTax =0;
            }

            $this->totalAmount=$this->totalPrice-$this->totalDiscount+$this->totalTax;

          }
        }
      }
  }
  

}
