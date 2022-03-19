@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop
{{-- Content --}}
@section('content')
<meta name="_token" content="{{ csrf_token() }}">
<script>
            function setupCalendar() {
                    $task_title=$("#task_title").val(); 
                    $lead_id=$("#lead_id").val(); 
                    $task_description=$("#task_description").val(); 
                    $task_note=$("#task_note").val(); 
                    $task_deadline=$("#task_deadline").val(); 
                    $task_start=$("#task_start").val(); 

                    $finished=$("#finished").val(); 
                    if($task_title!=""){
                        $.ajax({
                            type: "post",
                            url: '{{ url('task/addtasktolead')}}',
                            data: {'task_title': $task_title, 'lead_id': $lead_id, 'task_description': $task_description, 'task_note': $task_note, 'task_deadline': $task_deadline, 'task_start': $task_start, 'finished': $finished, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                alert("Cài đặt thành công");
                                $item=data.Result,
                                $edit='';
                                $lineHistory="<tr><td></td><td>"+$item.full_name+"</td><td>--</td><td>"+$item.task_title+"</td> <td>"+$item.task_description+"</td><td>"+$item.task_note+"</td><td>"+$item.task_start+"</td><td>"+$item.task_deadline+"</td><td>"+$item.finished+"</td><td>"+$edit+"</td></tr>";
                                $('.list_of_items_calendar').append($lineHistory);
                                $.magnificPopup.close();
                                document.location.reload(true);

                            }
                        });
                    }else{
                        alert("Tiêu đề không được trống");
                        return false;
                    }
             }
            $(document).ready(function(){
                $(function () {
                    $('.popup-modal').magnificPopup({
                    type: 'inline',
                    preloader: false,
                    focus: 'task_title',
                    modal: true
                    });
                    $(document).on('click', '.popup-modal-dismiss', function (e) {
                    e.preventDefault();
                    $.magnificPopup.close();
                    });
                });

                $('.popup-modal-report').magnificPopup({
                    type: 'ajax',
                    preloader: false,
                    focus: 'task_report_description',
                    modal: true
                });
                $(document).on('click', '.popup-modal-report-dismiss', function (e) {
                    e.preventDefault();
                    $.magnificPopup.close();
                });

                var dateTimeFormat = 'Y-m-d H:i';
                flatpickr("#task_deadline", {
                    minDate: '{{  now() }}',
                    dateFormat: dateTimeFormat,
                    enableTime: true,
                });
                flatpickr("#task_start", {
                    minDate: '{{now()}}',
                    dateFormat: dateTimeFormat,
                    enableTime: true,
                });
                $('.popup-edit').magnificPopup({
                    type: 'ajax',
                });
                
                
                $("#user_id").find("option:contains('{{trans('task.user')}}')").prop('selected',true);
                $("#user_id").select2({
                    theme:"bootstrap",
                    placeholder:"{{trans('task.user')}}"
                });

                $('.task-body').slimscroll({
                    height: '650px',
                    size: '5px',
                    opacity: 0.2
                });
            });
            $('.icheckgreen').iCheck({
                checkboxClass: 'icheckbox_minimal-green',
                radioClass: 'iradio_minimal-green'
            });
            function showReport($taskid) {
                if (document.getElementById("linetask"+$taskid).style.display === "none") {
                    document.getElementById("linetask"+$taskid).style.display = "contents";
                    $("#showhideicons"+$taskid).html("<span class=\"show\">-</span>");
                } else {
                    document.getElementById("linetask"+$taskid).style.display = "none";
                    $("#showhideicons"+$taskid).html("<span class=\"show\">+</span>");
                }
               // document.getElementById("linetask"+$taskid).style.display = 'contents';
               // document.getElementById('linetask'+$taskid).classList.remove("boxshowhide");
                $.ajax({
                    type: "GET",
                    url: baseUrl+"/task/history_task_report",
                    data: {'task_id': $taskid, _token: '{{ csrf_token() }}'},
                    success: function (result) {
                        $dataLine="";
                        $.each(result, function (i, item) {
                            $status="<span style='color:red; font-weight:normal'>"+item.title_status+"</span>";

                            if(item.type_task==1){
                                $status="<span style='color:green; font-weight:bold'>"+item.title_status+"</span>";
                            }
                            $dataLine+=" <tr>";
                            $dataLine+=" <td>"+(i+1)+"</td>";
                            $dataLine+=" <td>"+item.full_name+"</td>";
                            $dataLine+=" <td>"+$status+"</td>";
                            $dataLine+=" <td>"+item.task_report_description+"</td>";
                            $dataLine+=" <td>"+item.date_report+"</td>";
                            if(item.file_report!=""){
                                $dataLine+=" <td><a href='/"+item.file_report+"' target='_blank'>Download file</a></td>";
                            }else{
                                $dataLine+=" <td>Không có file báo cáo</td>";

                            }
                            $dataLine+=" </tr>";
                        });
                        $('.list_of_items_data_'+$taskid).html($dataLine);
                    }
                });
                   
            }
            function hideReport($taskid) {
                $('.list_of_items_data_'+$taskid).html("");
            }
        </script>


    <div class="clearfix">
    <!--
    <div class="col-md-12">
        <div class="panel panel-primary todolist">
            <div class="panel-heading border-light">
                <h4 class="panel-title">
                    <i class="livicon" data-name="medal" data-size="18" data-color="white" data-hc="white"
                        data-l="true"></i>
                    {{trans('task.tasks')}}
                </h4>
            </div>
            <div class="panel-body">
                <div class="todolist_list adds">
                    {!! Form::hidden('task_from_user',Sentinel::getUser()->id, ['id'=>'task_from_user']) !!}
                    <div class="form-group">
                        {!! Form::label('task_description', trans('task.description')) !!}
                        {!! Form::text('task_description', null, ['class' => 'form-control','id'=>'task_description']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('task_deadline', trans('task.deadline')) !!}
                        Form::text('task_deadline', null, ['class' => 'form-control date','id'=>'task_deadline']) 
                    </div>
                    <div class="form-group">
                        {!! Form::label('user_id', trans('task.user')) !!}
                        Form::select('user_id', $users , Sentinel::getUser()->id, ['class' => 'form-control'])
                    </div>
                    {!!  Form::hidden('full_name', $user_data->full_name, ['id'=> 'full_name'])!!}
                    <button type="submit" class="btn btn-primary add_button">
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div> -->

        {!! Form::open(['url' => 'task', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
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
                            {!! Form::select('status', $statusList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                            <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                        {!! Form::label('name',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Tiêu đề']) !!}
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
                <i class="material-icons">thumb_up</i>
                {{ $title }} (Tổng số @if($totalTask) {{$totalTask}} @endif)
                <a href="#popup-modal" data-rel="popup"  class="popup-modal poupmain">Lên lịch làm việc</a>
            </h4>
        </div>
        <div class="panel-body task-body">
                <div class="table-responsive">
                        <table id="data2" class="table table-bordered" style="width:1200px">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>STT</th>
                                    <th>Nhân viên</th>
                                    <th>Khách hàng</th>
                                    <th>Tiêu đề</th>
                                    <!-- <th>Công việc</th>-->
                                    <th>Ghi chú</th>
                                    <th>Bắt đầu</th>
                                    <th>Kết thúc</th>
                                    <!--<th>Tình trạng</th>-->
                                    <th>Chỉnh sửa</th>
                                    
                                </tr>
                            </thead>
                            <tbody class="list_of_items_calendar">
                            @if($taskList)
                            @php $i=0; @endphp
                                @foreach($taskList as $listData)
                                @php $i++; @endphp
                                @php $status="Chưa thực hiện"; @endphp
                                <tr>
                                     <td class="number"><a href="javascript:void(0)" onclick="return showReport({{$listData["id"]}});" id="showhideicons{{$listData["id"]}}"><span class="show">+</span></a></td>
                                    <td class="number">{{$i}} </td>
                                    <td><a href="/staff/{{$listData["user_id"]}}/dashboard/">{{$listData["full_name"]}}</a></td> 
                                    <td>@if($listData["lead_id"]!="" && $listData["lead_id"]!=0) <a href="/lead/{{$listData["lead_id"]}}/edit/">{{$listData["lead_name"]}} @else -- @endif</a></td> 
                                    <td>{{$listData["task_title"]}}</td> 
                                   <!-- <td>{{$listData["task_description"]}}</td> -->
                                    <td>{{$listData["task_note"]}} [@if($listData["report_status"]==0) <span style="color:red">Chưa có báo cáo</span> @else <a href="javascript:void(0)" onclick="return showReport({{$listData["id"]}});">Xem báo cáo</a> @endif ]</td>
                                    <td>{{$listData["task_start"]}}</td>

                                    <td>{{$listData["task_deadline"]}}</td>
                                    <!-- <td>@if($listData["type_task"]==2) <span style='color:green'> @else <span style='color:#000'> @endif {{$listData["status_title"]}}</span></td> -->
                                    <td><a class="popup-edit" href="/task/editag?id={{$listData["id"]}}">Sửa</a> | <a class="popup-modal-report" href="/task/reporttask?task_id={{$listData["id"]}}&redirect={{$linkfull}}">Báo cáo</a></td>
                                </tr>
                                <tr id="linetask{{$listData["id"]}}"  class="boxshowhide" style="display:none">
                                    <td class="list_of_items{{$listData["id"]}}" colspan="10">
                                        <table id="data{{$listData["id"]}}" class="table table-bordered" style="width:1000px">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>User</th>
                                                <th>Tình trạng</th>
                                                <th>Nội dung</th>
                                                <th>Ngày báo cáo</th>
                                                <th>File báo cáo</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list_of_items_data_{{$listData["id"]}}">

                                        </tbody>
                                        </table>
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
                        @include('layouts.paging', ['paginator' => $taskPage])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div data-role="popup" class="mfp-hide white-popup-block" id="popup-modal" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
        <div data-role="header" data-theme="a"><strong>Lên lịch làm việc</strong></div>
        <div role="main" class="ui-content">
            {!! Form::open(['url' => "", 'method' => 'post', 'files'=> true,'id'=>'task']) !!}
            <input type="hidden" id="lead_id" name="lead_id" value="@if(isset($lead)){{$lead->id}}@endif" />
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                    {!! Form::text('task_title', null, ['class' => 'form-control', 'id'=>'task_title','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề công việc']) !!}
                    </div>
                    <div class="controls">
                    {!! Form::textarea('task_description', null, ['class' => 'form-control', 'id'=>'task_description', 'placeholder'=>'Nội dung công việc']) !!}
                    </div>
                    <div class="controls">
                    {!! Form::textarea('task_note', null, ['class' => 'form-control', 'id'=>'task_note', 'placeholder'=>'Ghi chú']) !!}
                    </div>
                    {!! Form::label('status', trans('lead.status'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('status', $statusList, null, ['id'=>'finished', 'class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                    </div>
                    <div class="controls">
                    {!! Form::label('task_start', trans('task.timestart')) !!}
                    {!! Form::text('task_start', null, ['class' => 'form-control datetime','id'=>'task_start']) !!}
                    </div>
                    <div class="controls">
                    {!! Form::label('task_deadline', trans('task.deadline')) !!}
                    {!! Form::text('task_deadline', null, ['class' => 'form-control datetime','id'=>'task_deadline']) !!}
                    </div>
                    
                </div>
            </div>
             <a href="#" onclick="return setupCalendar();" class="button_upate" data-rel="back" data-transition="flow">Cài đặt lịch</a>
            <a href="#" class="popup-modal-dismiss" data-rel="back">Bỏ qua</a>
             {!! Form::close() !!}
        </div>
    </div>
    {{-- Scripts --}}

@stop