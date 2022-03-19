@extends('layouts.auth')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class=" col-md-12">
                <div class="box-color">
                    <h4>{{trans('auth.sign_account')}}</h4>
                    <br>
                    {!! Form::open(['url' => url('signin'), 'method' => 'post', 'name' => 'form']) !!}
                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        {!! Form::label(trans('auth.email')) !!} :
                        <span>{{ $errors->first('email', ':message') }}</span>
                        {!! Form::email('email', null, ['class' => 'form-control', 'required'=>'required', 'placeholder'=>'E-mail' ]) !!}
                    </div>
                    <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                        {!! Form::label(trans('auth.password')) !!} :
                        <span>{{ $errors->first('password', ':message') }}</span>
                        {!! Form::password('password', ['class' => 'form-control', 'required'=>'required', 'placeholder'=>'Password']) !!}
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="remember" value="remember" name="remember">
                            <i class="primary"></i> {{trans('auth.keep_login')}}
                        </label>
                    </div>
                    <input type="submit" class="btn btn-primary btn-block" value="{{trans('auth.login')}}"></input>
                    {!! Form::close() !!}
                </div>
                <hr class="separator">
                <div class="text-center">
                    <h5><a href="{{url('forgot')}}" class="forgot_pw _600">{{trans('auth.forgot')}}?</a></h5>
                </div>
            </div>
        </div>
    </div>

@stop
