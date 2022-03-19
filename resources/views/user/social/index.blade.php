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
            <form class="bv-form" action="{{ route('social.posting') }}" method="post">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="social-providers">
                    <label class="all_provider">
                        <input type="checkbox" value="contracts"
                        name="all_provider" class="icheck all_provider" id="all_provider">
                         {{ trans('social.all_providers') }}
                    </label>
                    <div class="providers">
                        @foreach($providers as $provider)
                            @if($provider->isInitialized() && $provider->isLoggedIn())
                                <label>
                                    <input type="checkbox" value="{{ strtolower($provider->getName()) }}"
                                    name="provider[]" class="icheck provider">
                                     {{ $provider->getName() }}
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="posting">
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
                </div>

            </form>
        </div>

    </div>

@stop

@section('scripts')

    <script type="text/javascript" src="{{ asset('js/icheck.min.js') }}"></script>
    <script type="text/javascript">
        $('.icheck').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        });

        $('.all_provider').on('ifChecked', function(event){
            $('.provider').iCheck('check')
        });

        $('.all_provider').on('ifUnchecked', function(event){
            $('.provider').iCheck('uncheck')
        });

        $('.provider').on('ifUnchecked', function(event){
            $('.all_provider').iCheck('uncheck')
        });
    </script>
@stop
