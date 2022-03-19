@extends('layouts.install')
@section('content')
    @include('install.steps', ['steps' => ['welcome' => 'selected done', 'requirements' => 'selected']])
    @if (! $allLoaded)
        <div class="alert alert-danger">
            {!!trans('install.system_not_meet_requirements')!!}
        </div>
    @endif
    <div class="step-content">
        <div class="blade-fileh">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="header_bottom">{{trans('install.system_requirements')}}</h3>
                    <div class="m-t-30">
                        <ul class="list-group">
                            @foreach ($requirements as $extension => $loaded)
                                <li class="list-group-item {{ ! $loaded ? 'list-group-item-danger' : '' }}">
                                    {{ $extension }}
                                    @if ($loaded)
                                        <span class="badge badge-success"><i class="fa fa-check"></i></span>
                                    @else
                                        <span class="badge badge-danger"><i class="fa fa-times"></i></span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        @if ($allLoaded)
                            <a class="btn btn-primary pull-right m-t-20" href="{{ url('install/permissions') }}">
                                {{trans('install.next')}}
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        @else
                            <a class="btn btn-info pull-right" href="{{ url('install/permissions') }}">
                                {{trans('install.refresh')}}
                                <i class="fa fa-refresh"></i></a>
                            <button class="btn btn-primary pull-right" disabled>
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