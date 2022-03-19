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
    <div class="row">
        <div class="col-md-6">
            <h4>Invoice Details for current month</h4>
            <hr>
            <div id="invoice-chart" class="index-invo"></div>
        </div>
        <div class="col-md-6">
            <ul class="list-inline invoice-list">
                <li>
                    <div class="txt-info">{{trans('invoice.invoices_total')}}</div>
                    <h5 class="number c-red">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$invoices_total_collection:
                        $invoices_total_collection.' '.Settings::get('currency') }} </h5>
                </li>
                <li>
                    <div class="txt">{{trans('invoice.open_invoice')}}</div>
                    <h5 class="number c-green">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$open_invoice_total:
                        $open_invoice_total.' '.Settings::get('currency') }} </h5>
                </li>
                <li>
                    <div class="txt-dang">{{trans('invoice.overdue_invoice')}}</div>
                    <h5 class="number c-red">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$overdue_invoices_total:
                        $overdue_invoices_total.' '.Settings::get('currency')}} </h5>
                </li>
                <li>
                    <div class="txt-succ">{{trans('invoice.paid_invoice')}}</div>
                    <h5 class="number c-blue">{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').' '.$paid_invoices_total:
                        $paid_invoices_total.' '.Settings::get('currency') }} </h5>
                </li>
            </ul>
        </div>
    </div>
    <div class="panel panel-default m-t-10">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">web</i>
                {{ $title }}
            </h4>
                                <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
        <table id="data" class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>{{ trans('invoice.invoice_number') }}</th>
                <th>{{ trans('invoice.invoice_date') }}</th>
                <th>{{ trans('invoice.due_date') }}</th>
                <th>{{ trans('invoice.agent_name') }}</th>
                <th>{{ trans('invoice.total') }}</th>
                <th>{{ trans('invoice.unpaid_amount') }}</th>
                <th>{{ trans('invoice.status') }}</th>
                <th>{{ trans('invoice.expired') }}</th>
                <th>{{ trans('table.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
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
        var chart = c3.generate({
            bindto: '#invoice-chart',
            data: {
                columns: [
                    ['Open invoice', {{$open_invoice_total}}],
                    ['Overdue invoice', {{$overdue_invoices_total}}],
                    ['Paid invoice', {{$paid_invoices_total}}]
                ],
                type : 'donut',
                colors: {
                    'Open invoice': '#4FC1E9',
                    'Overdue invoice': '#FD9883',
                    'Paid invoice': '#A0D468'
                }
            }

        });
        $(".sidebar-toggle").on("click",function () {
            setTimeout(function () {
                chart.resize();
            },200)
        });
    </script>
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    "columns":[
                        {"data":"invoice_number"},
                        {"data":"invoice_date"},
                        {"data":"due_date"},
                        {"data":"customer"},
                        {"data":"final_price"},
                        {"data":"unpaid_amount"},
                        {"data":"status"},
                        {"data":"expired"},
                        {"data":"actions"}
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop