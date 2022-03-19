@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
@endsection
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

    @if($user_data->inRole('admin'))
        <fieldset>
            <legend>{{trans('profile.history')}}</legend>
            <ul>
                @foreach($salesteam->revisionHistory as $history )
                    <li>{{ $history->userResponsible()->first_name }} changed {{ $history->fieldName() }}
                        from {{ $history->oldValue() }} to {{ $history->newValue() }}</li>
                @endforeach
            </ul>
        </fieldset>
    @endif
@endsection
