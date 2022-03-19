@extends('layouts.install')

@section('content')

    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'installation' => 'selected'
    ]])

    {!! Form::open(['url' => 'install/install']) !!}
    <div class="step-content">
            <div class="blade-fileh">
                <h3 class="header_bottom">{{trans('install.installation')}}</h3>
                <p>{{trans('install.ready_to_install')}}</p>
                <button class="btn btn-primary pull-right" data-toggle="loader" data-loading-text="Installing" type="submit">
                    <i class="fa fa-play install-blade"></i>
                    {{trans('install.install')}}
                </button>
            </div>
        </div>
    {!! Form::close() !!}
@stop