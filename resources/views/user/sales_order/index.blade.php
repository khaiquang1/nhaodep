@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="clearfix">
    {!! Form::open(['url' => 'sales_order', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('status', $statusList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('name',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                    <div class="controls">
                        {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
                    </div>
                </div>
            </div>
            
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('function') ? 'has-error' : '' }}">
                    {!! Form::label('function', trans('Đến từ'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('function', $sourceList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('tags') ? 'has-error' : '' }}">
                    {!! Form::label('tags',  trans('lead.tags'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('tags', $leadGroupSource, null, ['id'=>'tags', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('product') ? 'has-error' : '' }}">
                    {!! Form::label('product',  trans('product.products'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('product_id', $productList, null, ['id'=>'product_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                    {!! Form::label('grouplead',  trans('lead.group_name'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('group_id', $groupLead, null, ['id'=>'group_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('group_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <div class="page-header clearfix">
        <div class="pull-right">
             <a href="{{ url($type.'/kanban') }}" class="btn btn-primary m-b-10">{{trans('sales_order.view_kanban')}}</a>
            <a href="{{ url($type.'/draft_salesorders') }}" class="btn btn-primary m-b-10">{{trans('sales_order.draft_salesorders')}}</a>
            <a href="{{ url('salesorder_invoice_list') }}" class="btn btn-primary m-b-10">{{ trans('sales_order.invoice_list') }}</a>
            <a href="{{ url('salesorder_delete_list') }}" class="btn btn-primary m-b-10">{{ trans('sales_order.delete_list') }}</a>
            @if($user_data->hasAccess(['sales_orders.write']) || $user_data->inRole('admin'))
                <a href="{{ 'sales_order/create' }}" class="btn btn-primary m-b-10">
                    <i class="fa fa-plus-circle"></i> {{ trans('sales_order.create') }}</a>
            @endif
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">attach_money</i>
                {{ $title }} (Tổng số @if($totalOrder) {{$totalOrder}} @endif)
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table  table-bordered" style="width:1600px">
                    <thead>
                    <tr>
                        <th>{{ trans('sales_order.stt') }}</th>
                        <th>{{ trans('sales_order.sale_number') }}</th>
                        <th>{{ trans('sales_order.customer') }}</th>
                        <th>{{ trans('sales_order.date') }}</th>
                        <th>{{ trans('sales_order.exp_date') }}</th>
                        <th>{{ trans('sales_order.total') }}</th>
                        <th>{{ trans('sales_order.shipping_fee') }}</th>
                        <th>{{ trans('quotation.tax_amount') }}</th>
                        <th>{{ trans('quotation.grand_total') }}</th>
                        <th>{{ trans('quotation.discount') }}</th>
                        <th>{{ trans('quotation.final_price') }}</th>
                        <th>{{ trans('sales_order.payment') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @if($salesorderList)
                        @php $i=0; @endphp
                        @foreach($salesorderList as $orderData)
                        @php $i++; @endphp
                            <tr>
                                <td>{{$i}}</td>
                                <td><a href="{{ url('sales_order/' .  $orderData['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">{{ $orderData["sale_number"] }}</a></td>
                                <td><a href="{{ url('lead/' .  $orderData['lead_id'] . '/edit' ) }}">{{ $orderData["customer_name"] }}</a> <a href="/sales_order/create?lead_id={{$orderData["lead_id"]}}" style="font-weight: bold; color: green;font-size: 140%;" target="_blank" title="Tạo đơn hàng">+</a></td>
                                <td>{{date("d/m/Y",strtotime($orderData["date_ship"]))}}</td>
                                <td>{{date("d/m/Y",strtotime($orderData["date_exp"]))}}</td>
                                <td class="number">{{ number_format($orderData["total"]) }}</td>
                                <td class="number">{{ number_format($orderData["shipping_fee"]) }} @if($orderData["shipping_term"]!="")({{$orderData["shipping_term"]}}) @endif</td>
                                <td class="number">{{ number_format($orderData["tax_amount"]) }}</td>
                                <td class="number">{{ number_format($orderData["grand_total"]) }}</td>
                                <td class="number">{{ number_format($orderData["discount"]) }}</td>
                                <td class="number">{{ number_format($orderData["final_price"]) }}</td>
                                <td>{{ $orderData["is_invoice_list"] }}</td>
                                <td>
                                @if(Sentinel::getUser()->hasAccess(['sales_orders.write']) || Sentinel::inRole('admin'))
									<a href="{{ url('sales_order/' .  $orderData['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
										<i class="fa fa-fw fa-pencil text-warning"></i> </a>
								@endif
								@if(Sentinel::getUser()->hasAccess(['sales_orders.read']) || Sentinel::inRole('admin'))
								<a href="{{ url('sales_order/' .  $orderData['id'] . '/show' ) }}" title="{{ trans('table.details') }}" >
										<i class="fa fa-fw fa-eye text-primary"></i> </a>
								@endif
								@if(Sentinel::getUser()->hasAccess(['sales_orders.delete']) || Sentinel::inRole('admin'))
								<a href="{{ url('sales_order/' .  $orderData['id'] . '/delete' ) }}" title="{{ trans('table.delete') }}">
										<i class="fa fa-fw fa-trash text-danger"></i> </a>
                                @endif
                                </td>
                               
                            </tr>
                        @endforeach
                    @endif

                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="dataTables_info">
                    @include('layouts.paging', ['paginator' => $salesorderPage])
                </div>
            </div>
        </div>
    </div>

@stop
