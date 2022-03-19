@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

@section('styles')
@stop
{{-- Content --}}
@section('content')
    @include('vendor.flash.message')
    <div id="social-posting" class="panel panel-primary">

        <div class="panel-body">

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="posting">
                    @if(!$twitter->isLoggedIn())
                        <a href="{{ $twitter->loginUrl() }}">Please login in with twitter</a>
                    @else
                    <form class="bv-form" action="{{ route('social.twitter.tweet') }}" method="post">
                        {!! csrf_field() !!}

                        <div class="posting-data" >
                            <div class="form-group">
                                <legend><i class="material-icons md-24">share</i> {{ trans('social.message') }}</legend>
                                <div class="controls">
                                    <textarea class="form-control resize_vertical" name="message" id="message"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="pull-right btn btn-primary">Submit</button>
                        </div>


                    </form>
                    @endif

            </div>
        </div>

    </div>

@stop

@section('scripts')
@stop
