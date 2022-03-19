@extends('layouts/emails')

@section('content')
    Hello, <br><br><b>User: </b> '{{$user}}' has sent you this message:<br>
{{$bodyMessage}}
@stop