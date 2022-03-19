@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
{{-- Content --}}
@section('content')
<div class="clearfix">
        {!! Form::open(['url' => 'invoice', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('status', $invoice_status, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
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
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
</div>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <h4>Hóa đơn tháng này</h4>
                    <hr>
                    <div id="invoice-chart" class="index-invo"></div>
                </div>
                <div class="col-md-6">
                    <ul class="list-inline invoice-list">
                        <li>
                            <div class="txt-info">{{trans('invoice.invoices_total')}}</div>
                            <h5 class="numberprice c-red">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$invoices_total_collection:
                        $invoices_total_collection.' '.Settings::get('currency') }} </h5>
                        </li>
                        <li>
                            <div class="txt">{{trans('invoice.open_invoice')}}</div>
                            <h5 class="numberprice c-green">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$open_invoice_total:
                        $open_invoice_total.' '.Settings::get('currency') }} </h5>
                        </li>
                        <li>
                            <div class="txt-dang">{{trans('invoice.overdue_invoice')}}</div>
                            <h5 class="numberprice c-red">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$overdue_invoices_total:
                        $overdue_invoices_total.' '.Settings::get('currency')}} </h5>
                        </li>
                        <li>
                            <div class="txt-succ">{{trans('invoice.paid_invoice')}}</div>
                            <h5 class="numberprice c-blue">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$paid_invoices_total:
                        $paid_invoices_total.' '.Settings::get('currency') }} </h5>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="page-header clearfix">
                <div class="pull-right">
                    <a href="{{ url('invoice_delete_list') }}" class="btn btn-primary m-b-10">{{ trans('invoice.delete_list') }}</a>
                    <a href="{{ url('paid_invoice') }}" class="btn btn-primary m-b-10">{{ trans('invoice.paid_invoice') }}</a>
                    @if($user_data->hasAccess(['invoices.write']) || $user_data->inRole('admin'))
                        <a href="{{ $type.'/create' }}" class="btn btn-primary m-b-10">
                            <i class="fa fa-plus-circle"></i> {{ trans('invoice.new') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>


     <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">receipt</i>
                {{ $title }}
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table  table-bordered" style="width:1600px">
                    <thead>
                    <tr>
                                <th>{{ trans('invoice.STT') }}</th>
                                <th>{{ trans('invoice.invoice_number') }}</th>
                                <th>{{ trans('invoice.invoice_date') }}</th>
                                <th>{{ trans('invoice.due_date') }}</th>
                                <th>{{ trans('invoice.agent_name') }}</th>
                                <th class="number">{{ trans('invoice.total') }}</th>
                                <th>{{ trans('invoice.unpaid_amount') }}</th>
                                <th>{{ trans('invoice.status') }}</th>
                                <th>{{ trans('invoice.expired') }}</th>
                                <th>{{ trans('table.actions') }}</th>
                            </tr>
                    </thead>
                    <tbody>

                    @if($invoicesList)
                        @php $i=0; @endphp
                        @foreach($invoicesList as $orderData)
                        @php $i++; @endphp
                            <tr>
                                <td  class="number">{{$i}}</td>
                                <td  class="number">{{ $orderData["invoice_number"] }}</td>
                                <td>{{date("d/m/Y",strtotime($orderData["invoice_date"]))}}</td>
                                <td>{{date("d/m/Y",strtotime($orderData["invoice_deadline_date"]))}}</td>
                                <td><a href="/lead/{{$orderData["lead_id"]}}/edit" target="_blank">{{$orderData["customer"]}}</a></td>
                                <td class="number">{{ number_format($orderData["final_price"]) }}</td>
                                <td class="number">{{ number_format($orderData["unpaid_amount"]) }}</td>
                                <td>{{$orderData["status"]}}</td>
                                <td>
                                @if(date("Y-m-d")>$orderData["invoice_deadline_date"])
                                        <i class="fa fa-bell-slash text-danger" title="{{trans('invoice.invoice_expired')}}"></i> 
                                 @else
                                      <i class="fa fa-bell text-warning" title="{{trans('invoice.invoice_will_expire')}}"></i> 
                                @endif
                                
                                </td>
                                <td>
                                @if(Sentinel::getUser()->hasAccess(['invoices.write']) || Sentinel::inRole('admin'))
									<a href="{{ url('invoice/' .  $orderData['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
										<i class="fa fa-fw fa-pencil text-warning"></i> </a>
								@endif
								@if(Sentinel::getUser()->hasAccess(['invoices.read']) || Sentinel::inRole('admin'))
								<a href="{{ url('invoice/' .  $orderData['id'] . '/show' ) }}" title="{{ trans('table.details') }}" >
										<i class="fa fa-fw fa-eye text-primary"></i> </a>
								@endif
								@if(Sentinel::getUser()->hasAccess(['invoices.delete']) || Sentinel::inRole('admin'))
								<a href="{{ url('invoice/' .  $orderData['id'] . '/delete' ) }}" title="{{ trans('table.delete') }}">
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
                    @include('layouts.paging', ['paginator' => $invoicesPage])
                </div>
            </div>
        </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/d3.v3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/c3.min.js')}}"></script>
    <script>

        /*invoice chart*/

        var chart = c3.generate({
            bindto: '#invoice-chart',
            data: {
                columns: [
                    ['Hóa đơn mới', {{$open_invoice_total}}],
                    ['Qúa hạn', {{$overdue_invoices_total}}],
                    ['Đã thanh toán', {{$paid_invoices_total}}]
                ],
                type : 'donut',
                colors: {
                    'Hóa đơn mới': '#4FC1E9',
                    'Qúa hạn': '#FD9883',
                    'Đã thanh toán': '#A0D468'
                }
            }

        });
        $(".sidebar-toggle").on("click",function () {
            setTimeout(function () {
                chart.resize();
            },200)
        });
        //c3 customisation

        /* invoice chart end*/
    </script>
    @if(isset($type))
        <script type="text/javascript">
        /*
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    "columns":[
                        {"data":"invoice_number"},
                        {"data":"invoice_date"},
                        {"data":"invoice_deadline_date"},
                        {"data":"customer"},
                        {"data":"final_price", "className": 'number'},
                        {"data":"unpaid_amount", "className": 'number'},
                        {"data":"status"},
                        {"data":"expired"},
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data?starting_date="+ $('#starting_date').val()+"&sales_id="+ $('#sales_id').val()+"&status="+ $('#status').val()+"&keyword="+ $('#keyword').val())
                });
            }); */
        </script>
    @endif
@stop