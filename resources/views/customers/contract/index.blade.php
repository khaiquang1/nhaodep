@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">satellite</i>
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
                    <th>{{ trans('contract.start_date') }}</th>
                    <th>{{ trans('contract.end_date') }}</th>
                    <th>{{ trans('contract.description') }}</th>
                    <th>{{ trans('contract.company') }}</th>
                    <th>{{ trans('contract.resp_staff_id') }}</th>
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

@stop