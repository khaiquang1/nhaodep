@extends('layouts.user')
@section('title')
    {{ $title }}
@stop
@section('styles')
    <link href="{{ asset('css/bootstrap-tagsinput.css') }}" rel="stylesheet">
    @endsection
@section('content')
    <div class="panel panel-primary">
        <div class="panel-body">
            @include('flash::message')
            {!! Form::open(['url' => $type.'/invite', 'method' => 'post', 'files'=> true]) !!}
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! Form::label('email', trans('staff.emails'), ['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::text('emails', null, ['class' => 'form-control','data-role'=>'tagsinput']) !!}
                    <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                </div>
            </div>
            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="row" style="display:none">
                <div class="col-sm-12">
                    <div class="table-responsive">
                    <table id="invite_staff" class="table table-striped table-bordered dataTable no-footer">
                        <thead>
                            <tr>
                                <th>{{trans('staff.email')}}</th>
                                <th>{{trans('staff.send_invitation')}}</th>
                                <th>{{trans('staff.accept_invitation')}}</th>
                                <th>{{trans('staff.cancel_invitation')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user_data->invite as $item)
                                <tr>
                                    <td>{{$item->email}}</td>
                                    <td>{{$item->created_at->format($date_format)}}</td>
                                    <td>{{$item->claimed_at}}</td>
                                    <td>
                                        @if(empty($item->claimed_at))
                                            <a href="{{url('/staff/invite/'.$item->id.'/cancel')}}" class="btn-link"><i class="fa fa-trash text-danger"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $('#invite_staff').DataTable({
            "pagination": true
        });
    </script>
    @endsection