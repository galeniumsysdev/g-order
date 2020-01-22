<a href="{{route('order.notifnewpo',['notifid'=>$notification->id,'id'=>$notification->data['so_header_id']['id']])}}">
<span class="item">
   <span class="item-left">
    <span class="item-info">
      <span>@lang('label.newpo') <strong>({{$notification->data['so_header_id']['notrx']}})</strong>
            @lang('label.from') {{$notification->data['from']}}
      </span>
      <span>@lang('label.tglorder'):{{date('d-m-Y H:i:s',strtotime($notification->data['so_header_id']['tgl_order']))}}</span>
    </span>
  </span>
</span>
</a>
