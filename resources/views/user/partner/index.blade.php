@extends('layouts.user') {{-- Web site Title --}} @section('title') {{ $title }} @stop {{-- Content --}} @section('content')
<div class="page-header clearfix">
</div>
<div class="panel panel-default">
    <div class="pull-right">
                <a href="{{ $type.'/create' }}" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> {{ trans('partner.new') }}</a>
     </div>
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
                        <th>{{ trans('partner.id') }}</th>
                        <th>{{ trans('partner.name') }}</th>
                        <th>{{ trans('partner.number_sales') }}</th>
                        <th>{{ trans('partner.phone') }}</th>
                        <th>{{ trans('partner.email') }}</th>
                        <th>{{ trans('partner.addess') }}</th>
                        <th>{{ trans('partner.status') }}</th>
                        <th>{{ trans('partner.date_create') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($partner)
                        @foreach($partner as $partnerData)
                        <tr>
                        <td class="number">{{ $partnerData["id"]}}</td>
                        <td>{{ $partnerData["name"] }}</td>
                        <td class="number">{{ $partnerData["number_sales"] }}</td>
                        <td>{{ $partnerData["phone"] }}</td>
                        <td>{{ $partnerData["email"] }}</td>
                        <td>{{ $partnerData["address"] }}</td>
                        <td> 
                          @if($partnerData["status"]=="1")
                                     <a>Active</a>
                          @else
                                    <a>Unactive</a>
                         @endif
                        </td>
                        <td>{{ $partnerData["created_at"] }}</td>
                        <td>
                        @if($user_data->inRole('admin'))
                        <a href="{{ url('partner/' . $partnerData['id'] . '/edit' ) }}"><i class="fa fa-fw fa-pencil text-warning"></i></a>
                        <a href="{{ url('partner/' . $partnerData['id'] . '/delete' ) }}"><i class="fa fa-fw fa-trash text-danger"></i></a>
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
                        @include('layouts.paging', ['paginator' => $partnerDataList])
                    </div>
                </div>
        </div>
    </div>

</div>
@stop