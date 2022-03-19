@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'disable' => 'selected ',
    ]])
    @if (! $allDisableGranted)
        <div class="alert alert-danger">
            {!!trans('install.system_not_meet_requirements')!!}
        </div>
    @endif
    <div class="step-content blade-fileh">
        <h3>{{trans('install.disable')}}</h3>
        <hr>
        <ul class="list-group">
            @foreach($foldersDisable as $path => $isWritable)
                <li class="list-group-item">
                    {{ $path }}
                    @if ($isWritable)
                        <span class="label label-default pull-right">644</span>
                        <span class="badge badge-success"><i class="fa fa-times"></i></span>
                    @else
                        <span class="label label-default pull-right">644</span>
                        <span class="badge badge-danger"><i class="fa fa-check"></i></span>
                    @endif
                </li>
            @endforeach
        </ul>
        @if ($allDisableGranted)
            <a class="btn btn-green pull-right" href="{{ url('install/settings') }}">
                {{trans('install.next')}}
                <i class="fa fa-arrow-right"></i>
            </a>
        @else
            <a class="btn btn-info pull-right" href="{{ url('install/disable') }}">
                {{trans('install.refresh')}}
                <i class="fa fa-refresh"></i></a>
            <button class="btn btn-green pull-right" disabled>
                {{trans('install.next')}}
                <i class="fa fa-arrow-right"></i>
            </button>
        @endif
        <div class="clearfix"></div>
    </div>
@stop