@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
@endsection
@section('content')

    <div class="clearfix">
    {!! Form::open(['url' => 'deal/plan', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'sales_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('sales_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-3">
               
            </div>
            
        </div>
        {!! Form::close() !!}
    </div>
    <div class="table-rep-plugin">
        <div class="table-responsive mb-0" data-pattern="priority-columns">
            <table class="table table-striped" id="data">
                <thead>
                    <?php $numbercol=3;?>
                <tr>
                    <th width="200">Nhóm Lead</th>
                    <th  width="50">{{ trans('report.total')}}</th>
                    <th  width="50">{{date("d/m",strtotime($starting_date))}}</th>
                    @if($dateNumber)
                    @for($i=1;$i<=$dateNumber;$i++)
                    <?php $numbercol++;?>
                    <th  width="50">{{date('d/m', strtotime($starting_date.' +'.$i.' day'))}}</th>
                    @endfor
                    @endif
                    
                </tr>
                </thead>
                <tbody>

                @if($totalStatusGroupDetailLead)
                    @foreach($totalStatusGroupDetailLead as $totalStatusGroupDataLead)
                        <tr>
                            <td><span style="background:{{$totalStatusGroupDataLead["color_bg"]}}; color: {{$totalStatusGroupDataLead["color_text"]}}; padding: 5px 10px; border-radius: 5px; font-weight: bold;">@if(isset($totalStatusGroupDataLead["title"])){{$totalStatusGroupDataLead["title"]}}@endif</span></td>
                            <td class="number">{{ $totalStatusGroupDataLead["totalStatusGroup"] }}</td>
                            <td class="number">
                            @if(isset($listNumberDate) && $listNumberDate!="" && isset($listNumberDate[$totalStatusGroupDataLead["id"]][date('Y-m-d',strtotime($starting_date))]))
                                {{$listNumberDate[$totalStatusGroupDataLead["id"]][date('Y-m-d',strtotime($starting_date))]}}
                            @endif
                            </td>
                            @if($dateNumber)
                                @for($k=1;$k<=$dateNumber;$k++)
                                <td class="number">
                                    @php
                                        $datesearch=date('Y-m-d',strtotime($starting_date.' +'.$k.' day'));
                                    @endphp
                                    @if(isset($listNumberDate) && $listNumberDate!="" && isset($listNumberDate[$totalStatusGroupDataLead["id"]][$datesearch]))
                                        {{$listNumberDate[$totalStatusGroupDataLead["id"]][$datesearch]}}
                                    @endif
                                </td>
                                @endfor
                            @endif
                            
                        </tr>
                    @endforeach
                @endif
                
                </tbody>
            </table>

            <table class="table table-striped" id="data2">
                <thead>
                    <?php $numbercol=3;?>
                <tr>
                    <th width="200">Nhóm KH</th>
                    <th  width="50">{{ trans('report.total')}}</th>

                    <th  width="50">{{date("d/m",strtotime($starting_date))}}</th>
                    @if($dateNumber)
                    @for($i=1;$i<=$dateNumber;$i++)
                    <?php $numbercol++;?>
                    <th  width="50">{{date('d/m', strtotime($starting_date.' +'.$i.' day'))}}</th>
                    @endfor
                    @endif
                </tr>
                </thead>
                <tbody>
                @if($totalStatusGroupDetail)
                    @foreach($totalStatusGroupDetail as $totalStatusGroupData)
                        <tr>

                            <td><span style="background:{{$totalStatusGroupData["color_bg"]}}; color: {{$totalStatusGroupData["color_text"]}}; padding: 5px 10px; border-radius: 5px; font-weight: bold;">@if(isset($totalStatusGroupData["title"])){{$totalStatusGroupData["title"]}}@endif</span></td>
                            <td class="number">{{ $totalStatusGroupData["totalStatusGroup"] }}</td>

                            <td class="number">
                            @if(isset($listNumberDate) && $listNumberDate!="" && isset($listNumberDate[$totalStatusGroupData["id"]][date('Y-m-d',strtotime($starting_date))]))
                                {{$listNumberDate[$totalStatusGroupData["id"]][date('Y-m-d',strtotime($starting_date))]}}
                            @endif
                            </td>
                            @if($dateNumber)
                                @for($k=1;$k<=$dateNumber;$k++)
                                <td class="number">
                                    @php
                                        $datesearch=date('Y-m-d',strtotime($starting_date.' +'.$k.' day'));
                                    @endphp
                                    @if(isset($listNumberDate) && $listNumberDate!="" && isset($listNumberDate[$totalStatusGroupData["id"]][$datesearch]))
                                        {{$listNumberDate[$totalStatusGroupData["id"]][$datesearch]}}
                                    @endif
                                </td>
                                @endfor
                            @endif
                        </tr>
                    @endforeach
                @endif
                
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script language="javascript">
    $(document).ready(function() {
        $('input[name="starting_date"]').daterangepicker();
    });
        //khai báo nút submit form

  </script>
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