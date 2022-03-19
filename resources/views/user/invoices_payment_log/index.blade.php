@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            @if($user_data->hasAccess(['invoices.write']) || $user_data->inRole('admin'))
            <a href="{{ $type.'/create' }}" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> {{ trans('invoices_payment_log.create_invoice_payment') }}</a>
                @endif
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">archive</i>
                {{ $title }}
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>{{ trans('invoices_payment_log.payment_number') }}</th>
                        <th>{{ trans('invoices_payment_log.amount') }}</th>
                        <th>{{ trans('invoices_payment_log.invoice_number') }}</th>
                        <th>{{ trans('invoices_payment_log.payment_method') }}</th>
                        <th>{{ trans('invoices_payment_log.payment_date') }}</th>
                        <th>{{ trans('invoices_payment_log.agent_name') }}</th>
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
    <!-- Scripts -->
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    "columns":[
                        {"data":"payment_number"},
                        {"data":"payment_received"},
                        {"data":"invoice_number"},
                        {"data":"payment_method"},
                        {"data":"payment_date"},
                        {"data":"customer"},
                        {"data":"actions"}
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop
