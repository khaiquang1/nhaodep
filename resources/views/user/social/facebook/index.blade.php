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
                    @if($pages instanceof Illuminate\Support\Collection)
                        <form class="bv-form" action="{{ url('/social/facebook/posting') }}" method="post">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <legend for="pages"><i class="material-icons md-24">pages</i> {{ trans('social.pages') }}</legend>
                                @foreach($pages as $page)
                                    <label class="pageslabel">
                                        <input class="icheck fb-page" type="checkbox" value="{{ $page->id }}" name="pages[]"> {{ $page->name }} ({{$page->category}})
                                    </label>
                                @endforeach
                            </div>

                            <div class="posting-data social-dis">
                                <div class="form-group">
                                    <legend><i class="material-icons md-24">share</i> {{ trans('sozial.message') }}</legend>
                                    <div class="controls">
                                        <textarea class="form-control resize_vertical" name="message" id="message"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <legend><i class="material-icons md-24">link</i> {{ trans('sozial.link') }}</legend>
                                    <div class="controls">
                                        <input type="link" class="form-control" name="link" id="link" />
                                    </div>
                                </div>
                                <button type="submit" class="pull-right btn btn-primary">Submit</button>
                            </div>
                        </form>
                    @elseif(Sentinel::inRole('admin'))
                        <a href="{{ $pages }}">{{trans('social.login.admin', ['social' => 'Facebook'])}}</a>
                    @else
                        <p>{{trans('social.login.byadmin', ['social' => 'Facebook'])}}</p>
                    @endif
            </div>
        </div>
    </div>

@stop

@section('scripts')

    <script type="text/javascript" src="{{ asset('js/icheck.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            $('.fb-page').on('click', updatePosting);
            $('.pageslabel').on('click', updatePosting);
            $('.iCheck-helper').on('click', updatePosting);

            function updatePosting() {
                console.log('CHECKED');
                if($('.fb-page:checked').length > 0) {
                    $('.posting-data').show();
                } else {
                    $('.posting-data').hide();
                }
            }
            updatePosting();
        });
    </script>

@stop
