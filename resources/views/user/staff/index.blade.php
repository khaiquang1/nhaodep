@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-left">
        @if($partnerList)
            {!! Form::open(['url' => 'staff', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('partner_id', trans('staff.partner'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('partner_id', $partnerList, null, ['id'=>'partner_id', 'class' => 'form-control select2']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
            {!! Form::close() !!}
        @endif
        </div>
        <div class="pull-right">
            <!-- $user_data->hasAccess(['staff.write']) ||  -->
            @if($user_data->hasAccess(['staff.write']) || $user_data->inRole('admin'))
                <a href="{{ $type.'/create' }}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('staff.create_staff') }}</a>
            @endif
                <a href="{{ $type.'/invite' }}" class="btn btn-warning">
                    <i class="fa fa-envelope"></i> {{ trans('staff.invite') }}</a>
        </div>
       
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">people_outline</i>
                {{ $title }}
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>{{ trans('customer.ID') }}</th>
                        @if($partnerList) <th>{{ trans('customer.partner') }}</th>@endif
                        <th>{{ trans('customer.full_name') }}</th>
                        <th>{{ trans('customer.email') }}</th>
                        <th>{{ trans('customer.register') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($users)
                        @foreach($users as $listData)
                    <tr>  
                        <td>{{ $listData["id"] }}</td>
                        @if($partnerList)<td>{{ $listData["partner"] }}</td>@endif
                        <td> <a href="{{ url('report/summary?daterange=1&sales_id=' . $listData['id']) }}" title="{{ trans('table.details') }}" >{{ $listData["full_name"] }}</a></td>
                        <td>{{ $listData["email"] }}</td>
                        <td>{{ $listData["created_at"] }}</td>
                        <td>    
                            @if(Sentinel::getUser()->hasAccess(['staff.write']) || Sentinel::inRole('admin'))
                                <a href="{{ url('staff/' .  $listData['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
                                    <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                            @endif
                            <a href="{{ url('report/summary?daterange=1&sales_id=' . $listData['id']) }}" title="{{ trans('table.details') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>

                            @if(Sentinel::getUser()->hasAccess(['staff.delete']) || Sentinel::inRole('admin'))
                            <a href="{{ url('staff/' . $listData['id']. '/delete' ) }}" title="{{ trans('table.delete') }}">
                                <i class="fa fa-fw fa-trash text-danger"></i> </a>
                            <a href="{{ url('staff/' . $listData['partner_user_id']. '/delete-partner' ) }}" title="{{ trans('table.delete') }}">
                                <i class="fa fa-fw fa-trash text-danger"></i> </a>
                            @else
                            
                            @endif
                        </td>
                        </tr>
                        @endforeach
                     @endif
                    
                    </tbody>
                </table>
            </div>
            @if($staffData)
            <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                        @include('layouts.paging', ['paginator' => $staffData])
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

@stop
