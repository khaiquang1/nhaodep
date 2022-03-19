@extends('layouts/emails')

@section('content')
Hello, <br><br><b>Email:</b>  {{$email}} <br> <b>Password:</b>
{{$password}} <br>Please <a href=" {{url('signin')}} ">click here</a> for login
@stop