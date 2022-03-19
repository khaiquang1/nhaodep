@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- header styles --}}
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" type="text/css">
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-16">{{ $title }}</h4>
            </div>
        </div>
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')
@endsection
