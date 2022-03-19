@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

    
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-16">{{ $title }}</h4>
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div class="page-title-right">
                        <a href="{{ url($type.'/create') }}" class="btn btn-primary"> {{ trans('salesteam.create_salesteam') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-rep-plugin">
        <div class="table-responsive mb-0" data-pattern="priority-columns">
            <table id="data" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>{{ trans('salesteam.salesteam') }}</th>
                        <th>{{ trans('salesteam.invoice_target') }}</th>
                        <th>{{ trans('salesteam.actual_invoice') }}</th>
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
                        {"data":"salesteam"},
                        {"data":"target", "className":"number"},
                        {"data":"actual_invoice", "className":"number"},
                        {"data":"actions"},
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
                });
            });
        </script>
         <script src="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.js')}}"></script>
         <script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

        <!-- Init js-->
        <script src="{{ URL::asset('assets/js/pages/table-responsive.init.js')}}"></script> 
        <!-- Calendar init -->
        <script src="{{ URL::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
        <script src="{{ URL::asset('assets/libs/jquery.repeater/jquery.repeater.min.js')}}"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
        <script src="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.js')}}"></script>
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.css')}}" />

    @endif
@endsection
