@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="row">
            <div class="col-md-12">
                @include('flash::message')
            </div>
        </div>
        <div class="pull-right">

        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading bg-white">
            <h4 class="panel-title">
                <i class="material-icons">flag</i>
                {{ $title }}
            </h4>
            <span class="pull-right">
                <i class="fa fa-fw fa-chevron-up clickable"></i>
                <i class="fa fa-fw fa-times removecard clickable"></i>
            </span>
        </div>
        <div class="panel-body">
            <a href="{{ url($type.'/store') }}" class="btn btn-primary">{{ trans('backup.backup') }}</a>
            <a href="{{ url($type.'/clean') }}" class="btn btn-warning">{{ trans('backup.clean') }}</a>
        </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')

@stop
