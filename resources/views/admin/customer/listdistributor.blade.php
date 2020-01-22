
<div class="table-responsive">
  <table id="alamat-table" class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th width="50%">@lang('label.distributor')</th>        
        <!--<th width="10%">@lang('label.address')</th>
        <th width="10%">@lang('label.city')</th>
        <th width="5%">@lang('label.state')</th>-->
      </tr>
    </thead>
    <tbody>
      @forelse($customers as $customer)
        <tr>
          <td><a id="add_data" onclick="add_dist_table('{{$customer->id}}','{{$customer->customer_name}}')" ><span class="fa fa-plus"></span></a>
            </td>
          <td><a id="add_data" onclick="add_dist_table('{{$customer->id}}','{{$customer->customer_name}}')" >{{$customer->customer_name}}</a></td>

        </tr>
      @empty
        <tr><td colspan="5">No data</td></tr>
      @endforelse
    </tbody>
  </table>

  <!--
  <div class="pull-right">
     <a href="#" class="btn btn-success">@lang('label.addaddress')</a>
   </div>-->

</div>
