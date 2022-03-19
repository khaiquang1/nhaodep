@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="page-header clearfix">
            </div>
            {!! Form::open(['url' => $type . '/' . $opportunity->id.'/opportunity_delete_list', 'method' => 'get', 'class' => 'bf']) !!}

            @include('user/'.$type.'/_details')

            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('scripts')
    <script>
    </script>
@endsection