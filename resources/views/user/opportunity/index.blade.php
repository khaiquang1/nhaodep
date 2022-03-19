@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            <a href="{{ url('opportunity_converted_list') }}" class="btn btn-primary m-b-10">{{ trans('opportunity.converted_list') }}</a>
            <a href="{{ url('opportunity_delete_list') }}" class="btn btn-primary m-b-10">{{ trans('opportunity.delete_list') }}</a>
            <a href="{{ url('opportunity_archive') }}" class="btn btn-primary m-b-10">{{ trans('opportunity.archive') }}</a>
            @if($user_data->hasAccess(['opportunities.write']) || $user_data->inRole('admin'))
                <a href="{{ $type.'/create' }}" class="btn btn-primary m-b-10">
                    <i class="fa fa-plus-circle"></i> {{ trans('opportunity.create') }}</a>
            @endif
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">event_seat</i>
                {{ $title }}
            </h4>
                                <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table table-bordered">
                    <thead>
                    <tr>
                         <th>{{ trans('lead.id') }}</th>
                        <th>{{ trans('lead.leads_name') }}</th>
                        <th>{{ trans('lead.source') }}</th>
                        <th>{{ trans('opportunity.next_action') }}</th>
                        <th>{{ trans('opportunity.stages') }}</th>
                        <th>{{ trans('salesteam.sales_team_id') }}</th>
                        <th>{{ trans('salesteam.main_staff') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                        <th>{{ trans('opportunity.actions') }}</th>
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
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    "columns": [
                        {"data": "id"},
                        {"data": "opportunity"},
                        {"data": "source"},
                        {"data": "next_action"},
                        {"data": "stages"},
                        {"data": "sales_team_id"},
                        {"data": "salesteam"},
                        {"data": "options"},
                        {"data": "actions"}
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop