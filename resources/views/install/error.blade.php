@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'installation' => 'selected done',
        'complete' => 'selected error'
    ]])
    <div class="step-content blade-fileh">
        <h3>{{trans('install.whoops')}}</h3>
        <hr>
        <p><strong>{!! trans('install.something_wrong')!!}</strong></p>
        <p>{!! trans('install.check_log') !!}</p>
        <a class="btn btn-green pull-right" href="{{ url('install') }}">
            <i class="fa fa-undo"></i>
            {{trans('install.try_again')}}
        </a>
        <div class="clearfix"></div>
    </div>
@stop