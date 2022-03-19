@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
    </div>
    <div id="sendby_ajax" class="center-edit">
        @if(session('success_message'))
            <div class="alert alert-success">
                {{session('success_message')}}
            </div>
        @endif
            @if(session('quotation_rejected'))
                <div class="alert alert-danger">
                    {{session('quotation_rejected')}}
                </div>
            @endif
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">assignment</i>
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
                        <th>{{ trans('quotation.quotations_number') }}</th>
                        <th>{{ trans('quotation.agent_name') }}</th>
{{--                        <th>{{ trans('salesteam.salesteam') }}</th>--}}
{{--                        <th>{{ trans('salesteam.main_staff') }}</th>--}}
                        <th>{{ trans('quotation.total') }}</th>
                        <th>{{ trans('quotation.date') }}</th>
                        <th>{{ trans('quotation.exp_date') }}</th>
                        <th>{{ trans('quotation.payment_term') }}</th>
                        <th>{{ trans('quotation.status') }}</th>
                        <th>{{ trans('quotation.expired') }}</th>
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
    <script>
        @if(session('success_message'))
        setTimeout(function(){
            $("#sendby_ajax").hide();
        },4000);
        @endif
        @if(session('quotation_rejected'))
        setTimeout(function(){
            $("#sendby_ajax").hide();
        },4000);
        @endif
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
                        {"data":"quotations_number"},
                        {"data":"customer"},
                        {"data":"date"},
                        {"data":"exp_date"},
                        {"data":"final_price"},
                        {"data":"payment_term"},
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