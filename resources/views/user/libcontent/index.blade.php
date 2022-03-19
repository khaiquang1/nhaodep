@extends('layouts.user') {{-- Web site Title --}} @section('title') {{ $title }} @stop {{-- Content --}} @section('content')
<div class="page-header clearfix">
    @if($user_data->hasAccess(['sales_team.write']) || $user_data->inRole('admin'))
    <div class="pull-right">
        <a href="{{ url($type.'/create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> {{ trans('tags.create_new') }}</a>
       
    </div>
    @endif
</div>
<div class="clearfix">
    {!! Form::open(['url' => 'libcontent', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
    <div class="row">
        <div class="col-md-10">
                <div class="form-group {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('keyword',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
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
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
                <i class="material-icons">groups</i>
                {{ $title }}
            </h4>
    </div>
    
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="10%">{{ trans('libcontent.stt') }}</th>
                        <th width="20%">{{ trans('libcontent.name') }}</th>
                        <th width="5%">{{ trans('libcontent.type') }}</th>
                        <th width="50%">{{ trans('libcontent.content') }}</th>
                        <th width="10%">{{ trans('libcontent.status') }}</th>
                        <th width="5%">{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($contentPage)
                        @php $i=0;@endphp
                        @foreach($contentPage as $listContent)
                        @php $i++;@endphp
                        <tr>
                            <td>{{$i}}</td>
                            <td>{{ $listContent["title"]}}</td>
                            <td>{{ $listContent["type"]}}</td>
                            <td>{!! $listContent["content"]!!}</td>
                            <td>@if($listContent["status"]==1) Kích hoạt @else Không kích hoat @endif</td>
                            <td>
                            @if((isset($roleconfig) && $roleconfig==1) || $user_data->inRole('admin'))
                                <a href="{{ url('libcontent/' .  $listContent['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
                                    <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                            @endif
                            @if(Sentinel::inRole('admin'))
                            <a href="{{ url('libcontent/' .  $listContent['id'] . '/delete' ) }}" title="{{ trans('table.delete') }}">
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
                        @include('layouts.paging', ['paginator' => $contentPage])
                    </div>
                </div>
        </div>
    </div>
</div>
@stop {{-- Scripts --}}
