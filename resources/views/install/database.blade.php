@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected'
    ]])
    @include('layouts.messages')

    {!! Form::open(['url' =>  'install/start-installation', 'method' => 'post']) !!}
    <div class="step-content">
        <div class="blade-fileh">
            <h3 class="header_bottom">{{trans('install.database_info')}}</h3>
            <div class="form-group">
                <label for="host" class="control-label">{{trans('install.host')}}</label>
                <div class="controls">
                    {!! Form::text('host', old('host'),['class' => 'form-control','placeholder' => 'localhost']) !!}
                    <small>{{trans('install.host_info')}}</small>
                </div>
            </div>
            <div class="form-group">
                <label for="username" class="control-label">{{trans('install.username')}}</label>
                <div class="controls">
                    {!! Form::text('username', old('username'),['class' => 'form-control','placeholder' => 'Username']) !!}
                    <small>{{trans('install.username_info')}}</small>
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="control-label">{{trans('install.password')}}</label>
                <div class="controls">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    <small>{{trans('install.password_info')}}</small>
                </div>
            </div>
            <div class="form-group">
                <label for="database" class="control-label">{{trans('install.database')}}</label>
                <div class="controls">
                    {!! Form::text('database', old('database'),['class' => 'form-control','placeholder' => 'Database Name']) !!}
                    <small>{{trans('install.database_info2')}}</small>
                </div>
            </div>
            <button class="btn btn-primary pull-right">
                {{trans('install.next')}}
                <i class="fa fa-arrow-right" style="margin-left: 6px"></i>
            </button>
        </div>
        </div>
    {!! Form::close() !!}
@stop