@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
@endsection
@section('content')

<meta name="_token" content="{{ csrf_token() }}">


    <div class="clearfix">
    

        {!! Form::open(['url' => 'lead/assign', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                        {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                        </div>
                    </div>
                </div>
                @if($salesList)
                <div class="col-md-3">
                    <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                        {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                        <div class="controls">
                            {!! Form::select('sales_id', $salesList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                            <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-2">
                    <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                        {!! Form::label('status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                        <div class="controls">
                            <select name="status" id="status" class="form-control select_function">
                                 <option value="" >--T???t c??? --</option>
                                 <option value="0" @if($status=='0') selected @endif >--Ch??a ch???p nh??n --</option>
                                 <option value="1" @if($status=='1') selected @endif >--???? nh???n --</option>
                                 <option value="2" @if($status=='2') selected @endif >--H???t h???n--</option>

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                        {!! Form::label('name',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Ti??u ?????']) !!}
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

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-16">{{ $title }} (T???ng s??? @if($totalAssign) {{$totalAssign}} @endif)</h4>
            

            </div>
        </div>
    </div>

    <div class="table-rep-plugin">
        <div class="table-responsive mb-0" data-pattern="priority-columns">
                <table id="data" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="80px">STT</th>
                                <th width="120px">Chuy???n ?????n</th>
                                <th width="120px">Kh??ch h??ng</th>
                                <th width="150px">Ti??u ?????</th>
                                <th width="200px">N???i dung</th>
                                <th width="150px">Ng??y chuy???n</th>
                                <th width="150px">Deadline</th>
                                <th width="120px">T??nh tr???ng</th>
                                <th width="80px">Ch???nh s???a</th>
                            </tr>
                        </thead>
                        <tbody class="list_of_items_calendar">
                        @if($assignList)
                        @php $i=0; @endphp
                            @foreach($assignList as $listData)
                            @php $i++; @endphp
                            @php $status="Ch??a ch???p nh???n"; @endphp
                            <tr>
                                <td class="number">{{$i}} </td>
                                <td><a href="/staff/{{$listData["assign_to_id"]}}/dashboard/">{{$listData["assign_to"]["first_name"]}} {{$listData["assign_to"]["last_name"]}}</a></td> 
                                <td>@if($listData["lead_id"]!="" && $listData["lead_id"]!=0) <a href="/lead/{{$listData["lead_id"]}}/edit/">{{$listData["lead_name"]}} @else -- @endif</a></td> 
                                <td>{{$listData["taskwork"]["task_title"]}}</td> 
                                <td>{{$listData["taskwork"]["task_description"]}}</td>
                                <td>{{$listData["date_assign"]}}</td>
                                <td>{{$listData["taskwork"]["task_end"]}}</td>
                                <td>
                                    @if($listData["status"]==0) 
                                        <span style="color:orange; font-weight:bold">Ch??a nh???n 
                                    @elseif($listData["status"]==1)
                                        <span style="color:green; font-weight:bold">???? nh???n
                                    @else
                                        <span style="color:red; font-weight:bold">H???t h???n
                                    @endif 
                                        </span>
                                </td>
                                <td>@if($listData["status"]==0) <a class="popup-edit" href="/lead/editassign?id={{$listData['id']}}">Chuy???n </a>@endif </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>    
                    </table>
            </div>
            <div class="row">
            <div class="col-sm-12">
                <div class="dataTables_info"> 
                    @include('layouts.paging', ['paginator' => $assignPage])
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
            $(document).ready(function(){
                $('input[name="starting_date"]').daterangepicker();

                var dateTimeFormat = 'Y-m-d H:i';

                $('input[id="task_deadline"]').daterangepicker({
                    timePicker: true,
                    startDate: moment().startOf('hour'),
                    locale: {
                    format: dateTimeFormat
                    }
                });

                $('input[id="task_start"]').daterangepicker({
                    timePicker: true,
                    startDate: moment().startOf('hour'),
                    locale: {
                    format: dateTimeFormat
                    }
                });
                /*
                $('.popup-edit').magnificPopup({
                    disableOn: 700,
                    type: 'iframe',
                    mainClass: 'mfp-fade',
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false
                }); */
                

                $('.task-body').slimscroll({
                    height: '650px',
                    size: '5px',
                    opacity: 0.2
                });
            });
        </script>
        <!-- Plugins js -->
<script src="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.js')}}"></script>
<!-- Init js-->
<script src="{{ URL::asset('assets/js/pages/table-responsive.init.js')}}"></script> 
<!-- Calendar init -->
<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/jquery.repeater/jquery.repeater.min.js')}}"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script src="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.css')}}" />

    {{-- Scripts --}}

@endsection