@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-16">Tạo nhóm mới</h4>
            </div>
        </div>
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')
@endsection
