@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="clearfix">
    {!! Form::open(['url' => 'lead', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('function') ? 'has-error' : '' }}">
                    {!! Form::label('function', trans('Đến từ'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('function', $sourceList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="form-group required {{ $errors->has('UTM_Source') ? 'has-error' : '' }}">
                    {!! Form::label('UTM_Source',  trans('lead.source'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('UTM_Source', $leadGroupSource, null, ['id'=>'UTM_Source', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('UTM_Source', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="form-group required {{ $errors->has('product') ? 'has-error' : '' }}">
                    {!! Form::label('product',  trans('product.products'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('product_id', $productList, null, ['id'=>'product_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
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
        @if($user_data->hasAccess(['leads.write']) || $user_data->inRole('admin'))
            <div class="pull-right">
                <a href="{{ $type.'/create' }}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('lead.new') }}</a> | 
                <a href="{{ $type.'/export' }}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('lead.export') }}</a>
            </div>
        @endif
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">thumb_up</i>
                {{ $title }} (Tổng số @if($totalLead) {{$totalLead}} @endif)
            </h4>
        </div>
        <div class="panel-body">
            
            <div class="table-responsive">
                
                <table id="data" class="table table-bordered" style="width:1600px">
                    <thead>
                    <tr>
                        <th>{{ trans('lead.id') }}</th>
                        <th>{{ trans('lead.lead_name') }}</th>
                        <th>{{ trans('lead.email') }}</th>
                        <th>{{ trans('lead.phone') }}</th>
                        <th>{{ trans('lead.salesperson') }}</th>
                        <th>{{ trans('lead.product_name') }}</th>
                        <th>{{ trans('lead.source') }}</th>
                        <th>{{ trans('lead.campaign') }}</th>
                        <th>{{ trans('lead.medium') }}</th>
                        <th>{{ trans('lead.creation_date') }}</th>
                        <th>{{ trans('lead.next_time_follow') }}</th>
                    </tr>
                   
                    </thead>
                    <tbody>
                    @if($leads)
                        @foreach($leads as $listData)
                            <tr>
                                <td>{{ $listData["id"] }}</td>
                                <td>{{ $listData["opportunity"] }}</td>
                                <td>{{ $listData["email"] }}</td>
                                <td>{{ $listData["phone"] }}</td>
                                <td>{{ $listData["sale_name"] }}</td> 
                                <td>{{ $listData["product_name"] }}</td>
                                <td>{{ $listData["source"] }}</td>
                                <td>{{ $listData["UTM_Campaign"] }}</td>
                                <td>{{ $listData["UTM_Medium"] }}</td>
                                <td>{{ $listData["created_at"] }}</td>
                                <td>{{ $listData["next_time_follow"] }}</td>
                                
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                        @include('layouts.paging', ['paginator' => $leadsData])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script language="javascript">
    function pushApp($id, $partner_id){
        $( "#lead_status"+$id).addClass( "show" );
        $( "#lead_status"+$id).removeClass( "hide" );
        $( "#boxLead"+$id).hide();
        $.ajax({
            method: "POST",
            url: "https://api.crmsmart.io/api/push_app",
            data: {lead_id: $id, partner_id: $partner_id},
            success: function(data) {
                $( "#lead_status"+$id).addClass( "hide" );
                 $( "#callstatus"+$id).html("Đã chuyển lead cho "+data);
            }
        });
    }

    </script>
@stop