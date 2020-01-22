<a href="{{route('order.notifnewpo',[$notification->id,$notification->data['order']['id']])}}">
<span class="item">
   <span class="item-left">
    <span class="item-info">
      <span>Po:<strong>{{$notification->data['order']['customer_po']}}</strong> telah dikirim oleh distributor
      </span>
    </span>
  </span>
</span>
</a>
