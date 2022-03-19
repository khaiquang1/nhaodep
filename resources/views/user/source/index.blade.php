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
                    <a href="{{ url($type.'/create') }}" class="btn btn-primary">{{ trans('source.create_new') }}</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="table-rep-plugin">
    <div class="table-responsive mb-0" data-pattern="priority-columns">
        <table id="data" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>{{ trans('source.stt') }}</th>
                        <th>{{ trans('source.name') }}</th>
                        <th>{{ trans('source.value') }}</th>
                        <th>{{ trans('source.status') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
    </div>
</div>
@endsection
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
                        {"data":"stt", "class":"number"},
                        {"data":"title"},
                        {"data":"value"},
                        {"data":"status"},
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif
@endsection
