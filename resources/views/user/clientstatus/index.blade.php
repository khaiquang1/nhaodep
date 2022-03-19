@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">

@endsection
@section('content')
{{-- Content --}}
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-16">{{ $title }}</h4>
            @if($user_data->hasAccess(['lead.write']) || $user_data->inRole('admin'))
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <a href="{{ url($type.'/create') }}" class="btn btn-primary">{{ trans('clientstatus.create_new') }}</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row" style="margin-bottom: 10px;">
    <ul class="listclient">
        @if($typeList)    
            @foreach($typeList as $key=>$value)                                                               
            <li class="itembox"><a href="/clientstatus?type={{$key}}">{{$value}}</a></li>
            @endforeach
        @endif                                                                      
    </ul>
</div>
<div class="table-rep-plugin">
    <div class="table-responsive mb-0" data-pattern="priority-columns">
        <table id="data" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>{{ trans('clientstatus.stt') }}</th>
                    <th>{{ trans('clientstatus.name') }}</th>
                    <th>{{ trans('clientstatus.clorbg') }}</th>
                    <th>{{ trans('clientstatus.clortext') }}</th>
                    <th>{{ trans('clientstatus.position') }}</th>
                    <th>{{ trans('clientstatus.type') }}</th>
                    <th>{{ trans('clientstatus.status') }}</th>
                    <th>{{ trans('table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@stop {{-- Scripts --}}
@section('script')
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
                        {"data":"color_bg"},
                        {"data":"color_text"},
                        {"data":"position"},
                        {"data":"type_text"},
                        {"data":"status"},
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data?typestatus={{$typestatus}}")
                });
            });
            function updateposition(id, position) {
                $.ajax({
                    type: "POST",
                    url: '{{ url('clientstatus/updateposition')}}',
                    data: {'id': id, 'position':position, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        
                    }
                }); 
            }
        </script>
    @endif
@stop
