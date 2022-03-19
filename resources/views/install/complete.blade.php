@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => [
        'welcome' => 'selected done',
        'requirements' => 'selected done',
        'permissions' => 'selected done',
        'database' => 'selected done',
        'installation' => 'selected done',
        'complete' => 'selected'
    ]])
    <div class="step-content">
        <div class="blade-fileh">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="header_bottom">{{trans('install.complete2')}}</h3>
                    <p><strong>{{trans('install.well_done')}}</strong></p>
                    <p>{{trans('install.successfully')}}</p>
                    <div>
                        @if (is_writable(base_path()))
                            <p>{!!trans('install.final_info')!!}</p>
                        @endif
                        <a class="btn btn-primary pull-right" href="{{ url('/') }}">
                            <i class="fa fa-sign-in"></i>
                            {{trans('install.login')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop