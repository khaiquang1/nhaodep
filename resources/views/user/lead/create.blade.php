@extends('layout2.master')
@section('title') Thêm KH @endsection
@section('content')
    <div class="page-header clearfix">
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')
@endsection
