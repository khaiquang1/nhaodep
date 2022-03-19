@extends('layouts/emails')

@section('content')
Hello, <br><br><b>User </b> ' . {{$user}} . '. was sent you this message:<br>
{{$bodyMessage}}
@stop