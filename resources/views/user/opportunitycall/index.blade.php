@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            @if($user_data->hasAccess(['logged_calls.write']) || $user_data->hasAccess(['opportunities.write']) || $user_data->inRole('admin'))
                <a href="{{ url($type.'/'.$opportunity->id.'/create') }}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('call.create_opportunity_call') }}</a>
            @endif
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-fw fa-bell-o"></i>
                {{ $title }}
            </h4>
                                <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
        </div>
        <div class="panel-body">
            <input type="hidden" id="id" value="{{$opportunity->id}}">
            <div class="table-responsive">
            <table id="data" class="table  table-bordered" data-id="data">
                <thead>
                <tr>
                    <th>{{ trans('call.date') }}</th>
                    <th>{{ trans('call.summary') }}</th>
                    <th>{{ trans('call.duration') }}</th>
                    <th>{{ trans('call.company') }}</th>
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
                        {"data":"date"},
                        {"data":"call_summary"},
                        {"data":"duration"},
                        {"data":"company"},
                        {"data":"responsible"},
                        {"data":"actions"}
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop