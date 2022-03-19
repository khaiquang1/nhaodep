@extends('layouts/emails')

@section('content')
<p>{!! $request['msg'] !!}</p>

@stop
