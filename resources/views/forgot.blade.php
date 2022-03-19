@extends('layouts.auth')
@section('content')
    <div class="box-color text-color">
        <h4>{{trans('auth.forgot')}}</h4>
        <br>
        {!! Form::open(array('url' => url('password'), 'method' => 'post')) !!}
        
        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
            {!! Form::label(trans('auth.email')) !!} :
            <span>{{ $errors->first('email', ':message') }}</span>
            {!! Form::email('email', null, array('class' => 'form-control', 'required'=>'required', 'placeholder'=>'E-mail')) !!}
        </div>
        <input type="submit" class="btn btn-primary btn-block" value="{{trans('auth.send_reset')}}">
        {!! Form::close() !!}
        <h5 class="text-center">
            <a href="{{url('signin')}}" class="forgot_pw _600">{{trans('auth.login')}}?</a>
        </h5>
    </div>
@stop