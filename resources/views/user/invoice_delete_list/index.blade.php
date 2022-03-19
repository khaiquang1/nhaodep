@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="details">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="material-icons">event_seat</i>
                            {{ $title }}
                        </h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="data" class="table table-bordered">
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
            </div>
        </div>
    </div>
@stop

@section('scripts')
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
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop