@extends('layouts.home')

@section('content')
<div class="container">
    <div class="row">
            <div class="panel panel-primary">
                <div class="panel-heading">Customer</div>

                <div class="panel-body">
                      <form action="#" class="form-horizontal" method="post" role="form">
                          {{method_field('PATCH')}}
                          {{csrf_field()}}
                          <input type="hidden" value="{{url('/')}}" id="baseurl">
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="name">@lang('label.outlet') :</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control mb-8 mr-sm-8 mb-sm-4" name="name" id="name" value="{{ old('name')?old('name'):$customer->customer_name }}" readonly>
                              @if ($errors->has('name'))
                                  <span class="help-block">
                                      <strong>{{ $errors->first('name') }}</strong>
                                  </span>
                              @endif
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="email">@lang('label.email') :</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control mb-8 mr-sm-8 mb-sm-4" name="email" id="name" value="{{ old('email')?old('email'):$customer_email->contact }}" readonly>
                              @if ($errors->has('email'))
                                  <span class="help-block">
                                      <strong>{{ $errors->first('email') }}</strong>
                                  </span>
                              @endif
                            </div>
                          </div>
                          <div class="tabcard">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#personal" aria-controls="Personal" role="tab" data-toggle="tab">@lang('label.personal')</a></li>
                                <li role="presentation"><a href="#address" aria-controls="Address" role="tab" data-toggle="tab">@lang('label.address')</a></li>
                                <li role="presentation"><a href="#contact" aria-controls="Contact" role="tab" data-toggle="tab">@lang('label.contact')</a></li>
                                <li role="presentation"><a href="#distributor" aria-controls="distributor" role="tab" data-toggle="tab">@lang('label.distributor')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="personal">
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="npwp">@lang('label.npwp') :</label>
                                    <div class="col-sm-10">
                                      <input type="text" class="form-control mb-8 mr-sm-8 mb-sm-4" name="npwp" id="npwp" value="{{ old('npwp')?old('npwp'):$customer->tax_reference }}" readonly>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="status">@lang('label.needproduct') :</label>
                                    <div class="col-sm-10">
                                         @if($customer->pharma_flag=="1")
                                         <input type="checkbox"  name="pharma_flag" value="1" checked="checked"> Non PSC/Pharma<br>
                                         @else
                                         <input type="checkbox"  name="pharma_flag" value="1"> Non PSC/Pharma<br>
                                         @endif
                                         @if($customer->psc_flag=="1")
                                          <input type="checkbox"  name="psc_flag" value="1" checked="checked"> PSC<br>
                                          @else
                                          <input type="checkbox"  name="psc_flag" value="1"> PSC<br>
                                          @endif
                                    </div>
                                  </div>
                                  <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                                      <label for="kategori" class="control-label col-sm-2">@lang('label.category') :</label>

                                      <div class="col-sm-10">
                                        <select class="form-control" name="category" required>
                                          @forelse($categories as $category)
                                            @if($customer->outlet_type_id==$category->name)
                                              <option selected='selected' value="{{$category->id}}">{{$category->name}}</option>
                                            @else
                                              <option value="{{$category->id}}">{{$category->name}}</option>
                                            @endif
                                          @empty
                                          <tr><td colspan="4">No Category</td></tr>
                                          @endforelse
                                        </select>
                                          @if ($errors->has('kategori'))
                                              <span class="help-block">
                                                  <strong>{{ $errors->first('kategori') }}</strong>
                                              </span>
                                          @endif
                                      </div>
                                  </div>
                                  <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                                      <label for="kategori" class="control-label col-sm-2">@lang('label.categorydc') :</label>

                                      <div class="col-sm-4">
                                        <select class="form-control" name="groupdc" id="groupdc" onchange="ubah()" required >
                                          @forelse($groupdcs as $groupdc)
                                            @if($groupdc->id==$customer->group_dc_id)
                                              <option selected='selected' value={{$groupdc->id}}>{{$groupdc->display_name}}</option>
                                            @else
                                              <option value={{$groupdc->id}}>{{$groupdc->display_name}}</option>
                                            @endif
                                          @empty
                                          <tr><td colspan="4">...</td></tr>
                                          @endforelse
                                        </select>
                                          @if ($errors->has('groupdc'))
                                              <span class="help-block">
                                                  <strong>{{ $errors->first('groupdc') }}</strong>
                                              </span>
                                          @endif
                                      </div>
                                      <div class="col-sm-6">
                                        <select class="form-control" name="subgroupdc" id="subgroupdc">

                                        </select>
                                          @if ($errors->has('subgroupdc'))
                                              <span class="help-block">
                                                  <strong>{{ $errors->first('subgroupdc') }}</strong>
                                              </span>
                                          @endif
                                      </div>
                                  </div>
                                  <div class="form-group{{ $errors->has('role') ? ' has-error' : '' }}">
                                      <label for="role" class="control-label col-sm-2">Role :</label>

                                      <div class="col-sm-10">
                                        <select class="form-control" name="role" id="role" required>
                                          @forelse($roles as $role)

                                              <option value="{{$role->id}}">{{$role->display_name}}</option>

                                          @empty
                                          <tr><td colspan="4">No Role</td></tr>
                                          @endforelse
                                        </select>
                                          @if ($errors->has('role'))
                                              <span class="help-block">
                                                  <strong>{{ $errors->first('role') }}</strong>
                                              </span>
                                          @endif
                                      </div>
                                  </div>

                                  <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                      <button type="submit" class="btn btn-primary">@lang('label.save')</button>
                                    </div>
                                  </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="address">
                                  <div class="table-responsive">
                                    <table id="alamat-table" class="table table-striped">
                                      <thead>
                                        <tr>
                                          <th width="60%">@lang('label.address')</th>
                                          <th width="10%">@lang('label.city')</th>
                                          <th width="5%">@lang('label.state')</th>
                                          <th width="5%">@lang('label.postalcode')</th>
                                          <th width="10%">@lang('label.action')</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @forelse($customer_sites as $customer_site)
                                          <tr>
                                            <td>{{$customer_site->address1}}</td>
                                            <td>{{$customer_site->city}}</td>
                                            <td>{{$customer_site->state}}</td>
                                            <td>{{$customer_site->postalcode}}</td>
                                            <td></td>
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
                                </div>
                                <div role="tabpanel" class="tab-pane" id="contact">
                                  <div class="table-responsive">
                                    <table id="contact-table" class="table table-striped">
                                      <thead>
                                        <tr>
                                          <th width="30%">@lang('label.cp')</th>
                                          <th width="20%">Type</th>
                                          <th width="30%">Data</th>
                                          <th width="10%">@lang('label.action')</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @forelse($customer_contacts as $cc)
                                        <tr>
                                          <td>{{$cc->contact_name}}</td>
                                          <td>{{$cc->contact_type}}</td>
                                          <td>{{$cc->contact}}</td>
                                          <td></td>
                                        </tr>
                                        @empty
                                          <tr><td colspan="4">No data</td></tr>
                                        @endforelse
                                      </tbody>
                                    </table>
                                    <!--
                                     <div class="pull-right">
                                        <a href="#" class="btn btn-success">@lang('label.addcontact')</a>
                                      </div>-->
                                  </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="distributor">
                                  <div class="form-group" id="divdistributor">
                                      <div class="col-sm-12">
                                          <table id="listdistributor" class="table table-bordered">
                                            <thead>
                                              <tr>                                                
                                                <th width="50%">@lang('label.outlet')</th>
                                                <th width="10%">@lang('label.address')</th>
                                                <th width="10%">@lang('label.city')</th>
                                                <th width="5%">@lang('label.state')</th>
                                              </tr>
                                            </thead>
                                          </table>
                                      </div>
                                  </div>
                                  <div class="form-group" id="divdistributor">
                                      <label for="distributor" class="control-label col-sm-2">@lang('label.distributor') :</label>
                                      <div class="col-sm-10">
                                          <input type="text" name="searchdistributor" id="search_text" class="form-control mb-8 mr-sm-8 mb-sm-4" placeholder="Search distributor">
                                      </div>
                                  </div>
                                  <div class="form-group">
                                    <div class="col-sm-12" id="add_distributor">

                                    </div>
                                  </div>
                                </div>

                            </div>
                          </div>
                      </form>
                </div>
            </div>
        </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/customer.js') }}"></script>
@endsection
