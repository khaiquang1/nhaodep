@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')

    <div class="row">
        <div class="col-lg-12">
            <ul class="list-inline">
                <li class="btn btn-default"><i class="material-icons" title="Back to inbox">keyboard_backspace</i></li>
                <li class="btn btn-default"><i class="material-icons" title="Delete">delete</i></li>
                <li class="btn btn-default"><i class="material-icons" title="Move to">loyality</i></li>
            </ul>
        </div>
    </div>
@stop

@section('scripts')
    <script>


    </script>
@stop