<a href="{{route('order.notifnewpo',['notifid'=>$notification->id,'id'=>$notification->data['order']['id']])}}">
<span class="item">
   <span class="item-left">
    <span class="item-info">
      {{$notification->data['subject']}}
    </span>
  </span>
</span>
</a>
