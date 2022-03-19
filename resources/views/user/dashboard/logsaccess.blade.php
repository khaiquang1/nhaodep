@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
<div class="clearfix">
    {!! Form::open(['url' => 'logsaccess', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'sales_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('sales_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">thumb_up</i>
                {{ $title }}
            </h4>
                                
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th width="150px">{{ trans('dashboard.stt') }}</th>
                            <th>{{ trans('dashboard.user') }}</th>
                            <th>{{ trans('dashboard.ip') }}</th>
                            <th>{{ trans('dashboard.browser') }}</th>
                            <th>{{ trans('dashboard.date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($logshow)
                        @php $i=0; @endphp
                        @foreach($logshow as $logshowData)
                        @php $i++; @endphp
                        <tr>
                            <td class="number">{{ $i }}</td>
                            <td><a href="{{ url('logsaccess?sales_id=' . $logshowData['user_id'] .'&starting_date=' ) }}">{{trim($logshowData["fullname"]) }}</a></td>
                            <td>{{$logshowData["ip"]}}</td>
                            <td>{{$logshowData["browser"]}}</td>
                            <td>{{$logshowData["date"]}}</td>
                           

                        </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="dataTables_info">
                            @include('layouts.paging', ['paginator' => $logaccessPage])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
