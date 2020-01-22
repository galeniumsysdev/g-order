@extends('layouts.navbar_product')

@section('content')

<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet">

<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-primary">
				<div class="panel-contact panel-blue"><strong style="font:28px 'Lobster', cursive; color: #1e90ff;">Contact Kami</strong>
					<div>
						&nbsp;<legend style="border-bottom:6px solid #eee; margin-top:-10px;"></legend>
						<img class="view-contact" src="{{ URL::to('img/g1.png') }}"/>
						<div class="address-contact">
							<p><strong>KANTOR PUSAT</strong></P>
							<p>Jl. Adityawarman No. 67 Kebayoran Baru, Kota Jakarta Selatan, No. Telp : 021-7228601</p>
							<legend></legend>
						</div>
					</div>
					<div>
						<img class="view-contact" src="{{ URL::to('img/g2.png') }}"/>
						<div class="address-contact">
							<p><strong>PEMASARAN</strong></P>
							<p>Jl. Raya Kebayoran Lama No. 21 Kota Jakarta Selatan 12210 Telp : 021 - 5323308 / 021 - 53678180 Fax : 021 - 5360162</p>
							<legend></legend>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection