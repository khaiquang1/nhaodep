<div class="white-popup-block" style="max-width:400px;">
    <div data-role="header" data-theme="a"><strong>Báo cáo tình trạng công việc</strong></div>
    <div role="main" class="ui-content">
        {!! Form::model($taskreport, ['url' =>'/task/addreporttask', 'enctype'=>"multipart/form-data", 'method' => 'post', 'id'=>'reporttask', 'files'=> true]) !!}
        <input type="hidden" id="task_report_id" name="task_report_id" value="@if(isset($taskreport)){{$taskreport['id']}}@endif" />
        <input type="hidden" id="task_id" name="task_id" value="{{$task_id}}" />
        <input type="hidden" id="redirect" name="redirect" value="{{$urlredirect}}" />

        <div class="col-md-12">
            <div class="form-group">

                {!! Form::label('task_report_description', "Nội dung", ['class' => 'control-label']) !!}
                <div class="controls">
                {!! Form::textarea('task_report_description', null, ['class' => 'form-control', 'id'=>'task_report_description', 'placeholder'=>'Nội dung công việc']) !!}
                </div>
                {!! Form::label('status', trans('lead.status_report'), ['class' => 'control-label required']) !!}
                <div class="controls">
                    {!! Form::select('status', $statusList, null, ['id'=>'status', 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                </div>
                <div class="controls">
                <input type="file" name="file_report" id="file_report" accept=".xls,.xlsx,.doc,.docx,.pdf,.png,.jpg,.gif">
                </div>
            </div>
        </div>
         <a href="#" id="goibaocao" class="button_upate" data-rel="back" data-transition="flow">Cập nhật</a>
        <a href="#" class="popup-modal-dismiss" data-rel="back">Bỏ qua</a>
         {!! Form::close() !!}
    </div>
</div>
    <script>
                 
                 document.getElementById("goibaocao").addEventListener("click", function () {
                        var form = document.getElementById("reporttask");
                        form.submit();
                  });
                 $("#reporttask").on('submit',(function(e) {
                    e.preventDefault();
                    $.ajax({
                            type: "post",
                            url: '{{ url('task/addreporttask')}}',
                            enctype: 'multipart/form-data',
                            data:  new FormData(this),//{'task_report_id':$task_report_id, 'task_report_description': $task_report_description, 'task_id': $task_id, 'file_report': $file_report, 'status':$status, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                alert("Thêm báo cáo hoàn thành");
                                $.magnificPopup.close();
                            }
                        });
                 })
                 )
                 ;
                 /*
                    $task_report_id=$("#task_report_id").val(); 
                    $task_id=$("#task_id").val(); 
                    $task_report_description=$("#task_report_description").val(); 
                    $status=$("#status").val(); 
                    $file_report=$("#file_report").val(); 
                    
                    if($task_report_description!=""){
                        $.ajax({
                            type: "post",
                            url: '{{ url('task/addreporttask')}}',
                            enctype: 'multipart/form-data',
                            data:  new FormData(this),,//{'task_report_id':$task_report_id, 'task_report_description': $task_report_description, 'task_id': $task_id, 'file_report': $file_report, 'status':$status, _token: '{{ csrf_token() }}'},
                            success: function (data) {
                                alert("Thêm báo cáo hoàn thành");
                                $.magnificPopup.close();
                            }
                        });
                    }else{
                        alert("Nội dung báo cáo không được rỗng!");
                        return false;
                    } */
                
         $(document).ready(function(){

            var dateTimeFormat = 'Y-m-d H:i';
            flatpickr("#task_deadline_edit", {
                minDate: '@if(isset($task)){{$task["task_deadline"]}}@endif',
                dateFormat: dateTimeFormat,
                enableTime: true,
            });

           
          
        });

        
        
    </script>
