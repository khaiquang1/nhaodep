@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')
@stop
