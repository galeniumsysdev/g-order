<a href="{{route('customer.show',[$notification->data['user']['id'],$notification->id] )}}">
<span class="item">
New register: <strong>{{$notification->data['user']['name']}}</strong>
</span>
</a>
