@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'disable' => 'selected done',
        'settings' => 'selected ',
    ]])
    @include('layouts.messages')
    {!! Form::open(['url' =>  'install/settings', 'method' => 'post']) !!}
    <div class="step-content">
        <div class="blade-fileh">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="header_bottom">{{trans('install.settings')}}</h3>
                    <div class="form-group required {{ $errors->has('site_name') ? 'has-error' : '' }}">
                        <label for="site_name" class="control-label required">{{trans('install.site_name')}}</label>
                        <div class="controls">
                            {!! Form::text('site_name', old('site_name'),['class' => 'form-control','placeholder' => 'Lcrm']) !!}
                            <small>{{trans('install.site_name_info')}}</small>
                            <span class="help-block">{{ $errors->first('site_name', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('site_email') ? 'has-error' : '' }}">
                        <label for="site_email" class="control-label required">{{trans('install.site_email')}}</label>
                        <div class="controls">
                            {!! Form::text('site_email', old('site_email'),['class' => 'form-control','placeholder' => 'admin@admin.com']) !!}
                            <small>{{trans('install.site_email_info')}}</small>
                            <span class="help-block">{{ $errors->first('site_email', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('currency') ? 'has-error' : '' }}">
                        {!! Form::label('currency', trans('settings.currency'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::select('currency', $currency, old('currency'), ['id'=>'currency','class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('currency', ':message') }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group required {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        <label for="first_name" class="control-label required">{{trans('install.first_name')}}</label>
                        <div class="controls">
                            {!! Form::text('first_name', old('first_name'),['class' => 'form-control','placeholder' => 'John']) !!}
                            <small>{{trans('install.first_name_info')}}</small>
                            <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        <label for="last_name" class="control-label required">{{trans('install.last_name')}}</label>
                        <div class="controls">
                            {!! Form::text('last_name', old('last_name'),['class' => 'form-control','placeholder' => 'Doe']) !!}
                            <small>{{trans('install.last_name_info')}}</small>
                            <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="email" class="control-label required">{{trans('install.email')}}</label>
                        <div class="controls">
                            {!! Form::text('email', old('email'),['class' => 'form-control','placeholder' => 'admin@admin.com']) !!}
                            <small>{{trans('install.email_info')}}</small>
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('password') ? 'has-error' : '' }}">
                        <label for="password" class="control-label required">{{trans('install.password')}}</label>
                        <div class="controls">
                            {!! Form::password('password', ['class' => 'form-control']) !!}
                            <small>{{trans('install.password_info2')}}</small>
                            <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                        <label for="password" class="control-label required">{{trans('install.password_confirmation')}}</label>
                        <div class="controls">
                            {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                        </div>
                    </div>
                    <button class="btn btn-primary pull-right">
                        {{trans('install.next')}}
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
@section('scripts')
    <script>
        $(document).ready(function($) {
            $(".select2").select2({
                theme:"bootstrap"
            })
        })
    </script>
@stop