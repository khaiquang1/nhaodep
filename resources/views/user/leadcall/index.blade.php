@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <input type="hidden" id="id" value="{{$lead->id}}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-fw fa-bell-o"></i>
                {{ $title }} - <strong>{{ $lead->opportunity }}</strong>
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table table-bordered" data-id="data">
                    <thead>
                    <tr>
                         <th>ID</th>
                        <th>{{ trans('call.salesperson') }}</th>
                        <th>{{ trans('call.typecall') }}</th>
                        <th>{{ trans('call.date') }}</th>
                        <th>{{ trans('call.time_start') }}</th>
                        <th>{{ trans('call.time_end') }}</th>
                        <th>{{ trans('call.phone') }}</th>
                        <th>Khách hàng</th>
                        <th>File</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    columns:[
                        {"data":"id"},
                        {"data":"salesperson"},
                        {"data":"typecall"},
                        {"data":"date", "className": "date number"},
                        {"data":"time_start", "className": "date number"},
                        {"data":"time_end", "className": "date number"},
                        {"data":"phone", "className": "date number"},
                        {"data":"lead_name"},
                        {"data":"file"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
             });
             
        </script>
    @endif
@stop