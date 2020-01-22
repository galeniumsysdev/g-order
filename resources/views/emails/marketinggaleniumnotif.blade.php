@component('mail::message')
# Pendaftaran Baru di {{ config('app.name') }}

<strong>{{$user->name}}</strong> telah mendaftar di aplikasi {{ config('app.name') }}. Harap verifikasi segera data outlet/distributor tersebut melalui aplikasi {{ config('app.name') }}.

Thanks,<br>
Admin g-Order
@endcomponent
