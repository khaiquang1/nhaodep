<div class="white-popup-block" style="max-width:400px;">
    <div data-role="header" data-theme="a"><strong>Sửa tình trạng làm việc với KH</strong></div>
    <div role="main" class="ui-content">
        {!! Form::model($task, ['url' =>"", 'method' => 'put', 'id'=>'edittag', 'files'=> true]) !!}
        <input type="hidden" id="lead_id_edit" name="lead_id" value="@if(isset($task)){{$task['lead_id']}}@endif" />
        <input type="hidden" id="task_id_edit" name="task_id" value="@if(isset($task)){{$task['id']}}@endif" />

        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('task_title', "Tiêu đề công việc", ['class' => 'control-label required']) !!}

                <div class="controls">
                {!! Form::text('task_title', null, ['class' => 'form-control', 'id'=>'task_title_edit','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề công việc']) !!}
                </div>
                {!! Form::label('task_description', "Nội dung", ['class' => 'control-label']) !!}

                <div class="controls">
                {!! Form::textarea('task_description', null, ['class' => 'form-control', 'id'=>'task_description_edit', 'placeholder'=>'Nội dung công việc']) !!}
                </div>
                {!! Form::label('task_note', "Ghi chú", ['class' => 'control-label']) !!}

                <div class="controls">
                {!! Form::textarea('task_note', null, ['class' => 'form-control', 'id'=>'task_note_edit', 'placeholder'=>'Ghi chú']) !!}
                </div>
                
                {!! Form::label('status', trans('lead.status'), ['class' => 'control-label required']) !!}
                <div class="controls">
                    {!! Form::select('finished', $statusList, null, ['id'=>'finished_edit', 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                </div>
                {!! Form::label('sales', trans('lead.sales_takecare'), ['class' => 'control-label required']) !!}
                <div class="controls">
                    {!! Form::select('user_id', $staffs, null, ['id'=>'user_id_edit', 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('user_id', ':message') }}</span>
                </div>
                <div class="controls">
                    {!! Form::label('task_start', trans('task.timestart')) !!}
                    {!! Form::text('task_start', null, ['class' => 'form-control datetime','id'=>'task_start_edit', 'value'=>'@if(isset($task)){{$task["task_end"]}}@endif']) !!}
                    </div>
                <div class="controls">
                {!! Form::label('task_end', trans('task.timeline')) !!}
                {!! Form::text('task_end', null, ['class' => 'form-control datetime','id'=>'task_deadline_edit', 'value'=>'@if(isset($task)){{$task["task_end"]}}@endif']) !!}
                </div>
            </div>
        </div>
         <a href="#" onclick="return setupCalendarUpdate();" class="button_upate" data-rel="back" data-transition="flow">Cập nhật</a>
        <a href="#" class="popup-modal-dismiss" data-rel="back">Bỏ qua</a>
         {!! Form::close() !!}
    </div>
</div>
    <script>
     function setupCalendarUpdate() {
                    $task_title=$("#task_title_edit").val(); 
                    $lead_id=$("#lead_id_edit").val(); 
                    $task_id=$("#task_id_edit").val(); 
                    $task_description=$("#task_description_edit").val(); 
                    $task_note=$("#task_note_edit").val(); 
                    $status=$("#finished_edit").val(); 
                    $task_deadline=$("#task_deadline_edit").val(); 
                    $task_start=$("#task_start_edit").val(); 

                    $user_id=$("#user_id_edit").val(); 
                    if($task_title!="" &&  $lead_id!="" &&  $status!=""){
                        $.ajax({
                            type: "post",
                            url: '{{ url('task/addtasktolead')}}',
                            data: {'task_id':$task_id, 'task_title': $task_title, 'lead_id': $lead_id, 'task_description': $task_description, 'task_note': $task_note, 'task_deadline': $task_deadline, 'task_start': $task_start, 'user_id': $user_id, 'finished':$status, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                alert("Cài đặt thành công");
                                $.magnificPopup.close(); 
                            }
                        });
                    }else{
                        alert("Tiêu đề và trạng thái của task không được trống!");
                        return false;
                    }
                }
         $(document).ready(function(){

            var dateTimeFormat = 'Y-m-d H:i';
            flatpickr("#task_deadline_edit", {
                //minDate: '@if(isset($task)){{$task["task_end"]}}@endif',
                dateFormat: dateTimeFormat,
                enableTime: true,
            });
            flatpickr("#task_start_edit", {
                //minDate: '@if(isset($task) && $task["task_start"]!="0000-00-00 00:00:00"){{$task["task_start"]}}@else{{now()}}@endif',
                dateFormat: dateTimeFormat,
                enableTime: true,
            });

           
          
        });

        
        
    </script>
