@extends('layouts.user')
@section('content')
    <div class="row">
        <div class="col-sm-7 col-md-9 col-sm-offset-1">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td><b>Username</b></td>
                        <td><a href="#"> {{$user_data->first_name}}</a></td>
                    </tr>
                    <tr>
                        <td><b>{{trans('profile.email')}}</b></td>
                        <td><a href="#">{{$user_data->email}}</a></td>
                    </tr>
                    <tr>
                        <td><b>{{trans('profile.phone_number')}}</b></td>
                        <td><a href="#"> {{$user_data->phone_number}}</a></td>
                    </tr>
                    </tbody>
                </table>
                <a href="{{url('account')}}" class="btn btn-success change-prof">
                    <i class="fa fa-pencil-square-o"></i> {{trans('profile.change_profile')}}</a>
            </div>
        </div>
    </div>

@stop