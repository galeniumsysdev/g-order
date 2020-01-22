<a href="{{route('order.notifnewpo',[$notification->id,$notification->data['order']['id']])}}">
<span class="item">
   <span class="item-left">
    <span class="item-info">
      <span>Pembatalan PO <strong>{{$notification->data['order']['customer_po']}}</strong>
      </span>
    </span>
  </span>
</span>
</a>
