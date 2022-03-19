@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'disable' => 'selected done',
        'settings' => 'selected done',
        'mail_settings' => 'selected ',
    ]])
    @include('layouts.messages')
    {!! Form::open(['url' => 'install/email_settings']) !!}
    <div class="step-content">
        <div class="blade-fileh">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="header_bottom">{{trans('install.mail_settings')}}</h3>
                    <div class="form-group required {{ $errors->has('email_driver') ? 'has-error' : '' }}">
                        {!! Form::label('email_driver', trans('settings.email_driver'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            <div class="form-inline">
                                <div class="radio">
                                    {!! Form::radio('email_driver', 'mail',true, ['id'=>'mail', 'class'=>'email_driver icheck'])  !!}
                                    {!! Form::label('mail', 'MAIL')  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('email_driver', 'smtp', false, ['id'=>'smtp', 'class'=>'email_driver icheck'])  !!}
                                    {!! Form::label('smtp', 'SMTP') !!}
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('email_driver', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group smtp required {{ $errors->has('email_host') ? 'has-error' : '' }}">
                        {!! Form::label('email_host', trans('settings.email_host'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::input('text','email_host', old('email_host'), ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('email_host', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group smtp required {{ $errors->has('email_port') ? 'has-error' : '' }}">
                        {!! Form::label('email_port', trans('settings.email_port'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::input('text','email_port', old('email_port'), ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('email_port', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group smtp required {{ $errors->has('email_username') ? 'has-error' : '' }}">
                        {!! Form::label('email_username', trans('settings.email_username'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::input('text','email_username', old('email_username'), ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('email_username', ':message') }}</span>
                        </div>
                    </div>
                    <div class="form-group smtp required {{ $errors->has('email_password') ? 'has-error' : '' }}">
                        {!! Form::label('email_password', trans('settings.email_password'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::input('text','email_password', old('email_password'), ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('email_password', ':message') }}</span>
                        </div>
                    </div>
                    <button class="btn btn-primary pull-right">
                        {{trans('install.finish')}}
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        </div>
    {!! Form::close() !!}
@stop
@section('scripts')
    <script src="{{ asset('js/icheck.min.js') }}" type="text/javascript"></script>
    <script>
        jQuery(document).ready(function($) {
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            $("#smtp").on("ifChecked",function(){
                $('.smtp').show();
            });
            $("#smtp").on("ifUnchecked",function(){
                $(".smtp").hide();
            });
            if($("#smtp").closest(".iradio_minimal-blue").hasClass("checked")){
                $('.smtp').show();
            }else{
                $('.smtp').hide();
            }
        })
    </script>
@stop