@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        @if($user_data->hasAccess(['products.write']) || $user_data->inRole('admin'))
            <div class="pull-right">
                <a href="{{ $type.'/create' }}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('product.create_product') }}</a>
                <a href="{{ $type.'/import' }}" class="btn btn-primary" >
                    <i class="fa fa-plus-circle"></i> {{ trans('product.import') }}</a>
            </div>
        @endif
    </div>

    {!! Form::open(['url' => 'product', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
        <div class="col-md-2">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('user_id',  trans('product.user_care'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('user_id', $staff_care, null, ['id'=>'user_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('user_id', ':message') }}</span>
                    </div>
                </div>
            </div>
             <div class="col-md-2">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('status',  trans('product.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('status', $statuses, null, ['id'=>'status', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('category_id',  trans('product.product_name'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('category_id', $categories, null, ['id'=>'category_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('category_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('name',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                    <div class="controls">
                        {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
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
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                {{ $title }} (Tổng số @if($totalProduct) {{$totalProduct}} @endif) @if(isset($_GET["messenger"]) && $_GET["messenger"]) <span style="color:green">{{$_GET["messenger"]}}</span> @endif
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table  class="table  table-bordered" style="width:1800px">
                    <thead>
                    <tr>
                        <th>STT</th>
                        <th width="200px">{{ trans('product.product_name') }}</th>
                        <th>{{ trans('product.category_name') }}</th>
                        <th>{{ trans('product.product_type') }}</th>
                        <th>{{ trans('product.status') }}</th>
                        <th>{{ trans('product.quantity_on_hand') }}</th>
                        <th>{{ trans('product.quantity_available') }}</th>
                        
                        <th>{{ trans('product.user_care') }}</th>
                        @if(isset($partner) && $partner["partner_type"]==2)
                        <th>{{ trans('product.start_date') }}</th>
                        <th>{{ trans('product.end_date') }}</th>
                        @endif
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($productList)
                        @php $i=0; @endphp
                        @foreach($productList as $listData)
                        @php $i++; 
                        @endphp 
                    <tr> 
                        <td class="number">{{$i}}</td>
                        <td><a href="{{ url('product/' .  $listData['id'] . '/edit' ) }}">{{ $listData["product_name"] }}</a></td>
                        <td><a href="{{ url('product?category_id='.$listData['category_id']) }}">{{ $listData["title_category"] }}</a></td>
                        <td>{{ $listData["product_type"] }}</td>
                        <td>{{ $listData["status"] }}</td>
                        <td class="number">{{ $listData["quantity_on_hand"] }}</td>
                        <td class="number">{{ $listData["quantity_available"] }}</td>

                        
                        <td>@if($listData["user_care_fullname"]!="") {{ $listData["user_care_fullname"] }} @else {{ $listData["user_care_text"] }} @endif</td>
                        @if(isset($partner) && $partner["partner_type"]==2)
                        <td>{{ $listData["start_date"] }}</td>
                        <td>{{ $listData["end_date"] }}</td>
                        @endif
                        <td> 
                            @if(Sentinel::getUser()->hasAccess(['products.write']) || Sentinel::inRole('admin'))
                                <a href="{{ url('product/' .  $listData['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
                                    <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                            @endif
                            @if(Sentinel::getUser()->hasAccess(['products.read']) || Sentinel::inRole('admin'))
                            <a href="{{ url('product/' . $listData['id'] . '/export-code' ) }}" title="Download code gắn vào website"><i class="fa fa-fw fa-download text-primary"></i></a>
                            @endif
                            @if(Sentinel::getUser()->hasAccess(['products.delete']) || Sentinel::inRole('admin'))
                            <a href="{{ url('product/' .  $listData['id'] . '/delete' ) }}" title="{{ trans('table.delete') }}">
                                    <i class="fa fa-fw fa-trash text-danger"></i> </a>
                            @endif
                        </td>
                    </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                        @include('layouts.paging', ['paginator' => $productsPage])
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
