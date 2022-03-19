@extends('layouts.user')
@section('content')
    <div class="panel panel-primary">
        <div class="panel-body">
            {!! Form::model($user_data, ['url' => url('account/'.$user_data->id), 'method' => 'put', 'files'=> true]) !!}
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('user_avatar_file') ? 'has-error' : '' }}">
                        {!! Form::label('user_avatar_file', trans('profile.avatar'), ['class' => 'control-label']) !!}
                        <div class="controls row" v-image-preview>
                            <div class="col-sm-12">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                        <img id="image-preview" width="300">
                                        @if(isset($user_data->user_avatar))
                                            <img src="{{ url('uploads/avatar/thumb_'.$user_data->user_avatar) }}" alt="User Image">
                                        @endif
                                    </div>
                                    <div>
                        <span class="btn btn-default btn-file"><span
                                    class="fileinput-new">{{trans('dashboard.select_image')}}</span>
                            <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                            <input type="file" name="user_avatar_file"></span>
                                        <a href="#" class="btn btn-default fileinput-exists"
                                           data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                                    </div>
                                </div>
                                <span class="help-block">{{ $errors->first('user_avatar_file', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        {!! Form::label('first_name', trans('profile.first_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('first_name', null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        {!! Form::label('last_name', trans('profile.last_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('last_name', null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                        {!! Form::label('phone_number', trans('staff.phone_number'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('phone_number', null, ['class' => 'form-control','data-fv-integer' => 'true']) !!}
                            <span class="help-block">{{ $errors->first('phone_number', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
                        {!! Form::label('email', trans('profile.email'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('email', null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('password') ? 'has-error' : '' }}">
                        {!! Form::label('password', trans('profile.password'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::password('password', ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                        {!! Form::label('password_confirmation', trans('profile.password_confirmation'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    <a href="{{ url('/') }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

@stop