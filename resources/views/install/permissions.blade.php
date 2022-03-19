@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected'
    ]])
    @if (! $allGranted)
        <div class="alert alert-danger">
            {!!trans('install.system_not_meet_requirements')!!}
        </div>
    @endif
    <div class="step-content">
        <div class="blade-fileh">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="header_bottom">{{trans('install.permissions')}}</h3>
                    <div class="m-t-30">
                        <ul class="list-group">
                            @foreach($folders as $path => $isWritable)
                                <li class="list-group-item">
                                    {{ $path }}
                                    @if ($isWritable)
                                        <span class="label label-default pull-right">775</span>
                                        <span class="badge badge-success"><i class="fa fa-check"></i></span>
                                    @else
                                        <span class="label label-default pull-right">775</span>
                                        <span class="badge badge-danger"><i class="fa fa-times"></i></span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        @if ($allGranted)
                            <a class="btn btn-primary pull-right m-t-20" href="{{ url('install/database') }}">
                                {{trans('install.next')}}
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        @else
                            <a class="btn btn-info pull-right m-t-20" href="{{ url('install/permissions') }}">
                                {{trans('install.refresh')}}
                                <i class="fa fa-refresh"></i></a>
                            <button class="btn btn-green pull-right m-t-20" disabled>
                                {{trans('install.next')}}
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop