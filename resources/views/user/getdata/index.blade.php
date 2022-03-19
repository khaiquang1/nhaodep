@extends('layouts.user') {{-- Web site Title --}} @section('title') {{ $title }} @stop {{-- Content --}} @section('content')
<div class="page-header clearfix">
    @if($user_data->hasAccess(['leads.write']) || $user_data->inRole('admin'))
    <div class="pull-right">
        <a href="{{ url($type.'/create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> {{ trans('groupclient.create_new') }}</a>
       
    </div>
    @endif
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
            <table id="data" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="100px">{{ trans('getdata.stt') }}</th>
                        <th width="150px">{{ trans('getdata.name') }}</th>
                        <th width="100px">{{ trans('getdata.type') }}</th>
                        <th  width="350px" >{{ trans('getdata.token') }}</th>
                        <th  width="100px">{{ trans('getdata.page_id') }}</th>
                        <th  width="100px">{{ trans('getdata.status') }}</th>
                        <th  width="100px">{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-12">
                    <iframe src="https://api2.fastercrm.com/facebook" target="_blank" style="width: 100%;border: 0;"></iframe>
                </div>
            </div>
            
        </div>

    </div>
</div>
@stop {{-- Scripts --}}
@section('scripts')
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    "columns":[
                        {"data":"stt"},
                        {"data":"title"},
                        {"data":"type"},
                        {"data":"token", "className": 'width300px'},
                        {"data":"page_id"},
                        {"data":"status"},
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@stop
