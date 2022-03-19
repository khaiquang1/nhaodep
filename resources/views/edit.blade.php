@extends('layouts.auth')
@section('content')
    <div class="box-color">
        <h4>{{trans('auth.change_password')}}</h4>
        <br>
        {!! Form::open(array('url' => url('passwordreset/'.$id.'/'.$code), 'method' => 'post')) !!}
        <div class="form-group required {{ $errors->has('password') ? 'has-error' : '' }}">
            {!! Form::label('password',trans('auth.password'), array('class'=>'required')) !!} :
            <span class="help-block">{{ $errors->first('password', ':message') }}</span>
            {!! Form::password('password', array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <div class="form-group required {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
            {!! Form::label('password_confirmation',trans('auth.password_confirmation'), array('class'=>'required')) !!}
            :
            <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
            {!! Form::password('password_confirmation', array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">{{trans('auth.reset')}}</button>
        </div>
        {!! Form::close() !!}
        <h5 class="text-center">
            <a href="{{url('signin')}}" class="text-default">{{trans('auth.login')}}?</a>
        </h5>
    </div>
@stop