@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/select2/css/select2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <link href="{{ URL::asset('assets/libs/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/datepicker/datepicker.min.css')}}" type="text/css">
@endsection
@section('content')
<div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-16">{{ $title }}</h4>
            </div>
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <a href="{{ url($type.'/create') }}" class="btn btn-primary">{{ trans('smsconfig.create_new') }}</a>
                </div>
            </div>
        </div>
</div>
<div class="table-rep-plugin">
    <div class="table-responsive mb-0" data-pattern="priority-columns">
        <table id="data" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>{{ trans('smsconfig.stt') }}</th>
                    <th>{{ trans('smsconfig.name') }}</th>
                    <th>{{ trans('smsconfig.device') }}</th>
                    <th>{{ trans('smsconfig.limit_sms') }}</th>
                    <th>{{ trans('smsconfig.limittoday') }}</th>
                    <th>{{ trans('smsconfig.status') }}</th>
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
                        {"data":"stt"},
                        {"data":"name"},
                        {"data":"device"},
                        {"data":"limit_sms"},
                        {"data":"total_sms_last_sent"},
                        {"data":"status"},
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
    @endif

@stop
