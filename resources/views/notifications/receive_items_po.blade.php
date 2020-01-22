<a href="{{route('order.notifnewpo',['notifid'=>$notification->id,'id'=>$notification->data['order']['id']])}}">
<span class="item">
   <span class="item-left">
    <span class="item-info">
      PO:<strong>{{$notification->data['order']['customer_po']}}</strong> telah diterima customer
    </span>
  </span>
</span>
</a>
