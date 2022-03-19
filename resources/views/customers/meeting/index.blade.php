@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            @if($user_data->hasAccess(['meetings.read']) || $user_data->inRole('admin') || $user_data->inRole('customer'))
                <a href="{{ url($type.'/calendar')  }}" class="btn btn-success">
                    <i class="fa fa-calendar"></i> {{ trans('opportunity.calendar') }}</a>
            @endif
            @if($user_data->hasAccess(['meetings.write']) || $user_data->inRole('admin'))
                <a href="{{ url($type.'/create') }}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('meeting.meeting_create') }}</a>
            @endif
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">radio</i>
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
                        <th>{{ trans('meeting.meeting_subject') }}</th>
                        <th>{{ trans('meeting.starting_date') }}</th>
                        <th>{{ trans('meeting.ending_date') }}</th>
                        <th>{{ trans('salesteam.main_staff') }}</th>
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
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    columns:[
                        {"data":"meeting_subject"},
                        {"data":"starting_date"},
                        {"data":"ending_date"},
                        {"data":"responsible"},
                        {"data":"actions"}
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop