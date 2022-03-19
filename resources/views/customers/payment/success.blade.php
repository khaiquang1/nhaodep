@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
    </div>
    <div class="panel panel-primary">
        <div class="panel-body">
            <p>
                <a href="{{url('customers')}}" class="btn btn-primary btn-sm primary">
                    <i class="fa fa-arrow-left"></i> {{trans('payment.home')}} </a>
            </p>
        </div>
    </div>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
@stop
