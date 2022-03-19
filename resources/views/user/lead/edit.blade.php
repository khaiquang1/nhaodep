@extends('layout2.master')
@section('title') Edit Lead @endsection
@section('content')

    <div class="page-header clearfix">
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')
@endsection