<div class="white-popup-block" style="max-width:400px;">

        <div data-role="header" data-theme="a"><strong>Chuyển KH cho NV khác</strong></div>
            <div role="main" class="ui-content">
            {!! Form::open(['url' => "", 'method' => 'post', 'files'=> true,'id'=>'task']) !!}
            <input type="hidden" id="lead_id" name="lead_id" value="{{$assign["lead_id"]}}" />
            <input type="hidden" id="assign_id" name="assign_id" value="{{$assign["id"]}}" />
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('task_title', "Tiêu đề", ['class' => 'control-label required']) !!}
                    <div class="controls">

                    <input class="form-control" id="task_title" data-fv-integer="true" placeholder="Tiêu đề công việc"  name="task_title" type="text" value="{{$assign["task_title"]}}">
                    
                    </div>
                    {!! Form::label('task_description', "Nội dung công việc", ['class' => 'control-label required']) !!}
                    <div class="controls">
                    <textarea class="form-control" id="task_description" row="5" placeholder="Nội dung công việc" name="task_description" cols="50" rows="3">{{$assign["task_description"]}}</textarea>
                    </div>
                    {!! Form::label('group_user_id', trans('lead.select_group_user'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        <select name="group_user_id" id="group_user_id" class="form-control select_function" onchange="showuser(this.value)">
                            @if($groupStaff)
                                @foreach($groupStaff as $key=>$values)
                                <option value="{{$key}}" @if($key==$assign["group_id"]) selected @endif>{{$values}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    {!! Form::label('user_id',  trans('staff.staffs'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        <select name="user_id_data_asign" id="user_id_data_asign" class="form-control select_function">
                            <option>Chọn nhân viên</option>
                        </select>
                    </div>
                    {!! Form::label('task_deadline', trans('task.deadline')) !!}
                    <div class="controls">
                    {!! Form::text('task_deadline', null, ['class' => 'form-control datetime','id'=>'task_deadline', 'value'=>'@if(isset($assign)){{$assign["task_end"]}}@endif']) !!}
                    </div>
                    <div class="controls">
                        <div class="form-group" id="assignstatus">
                            <div id="assigning"></div>
                            <button onclick="assignUser();" type="button" class="btn" id="chatwithuser" name="chatwithuser">Chuyển Khách hàng</button>
                            <a href="#" class="popup-modal-dismiss" data-rel="back">Bỏ qua</a>
                        </div>
                    </div>
                </div>
            </div>
             {!! Form::close() !!}
    </div>
</div>
    <script>
        function assignUser(){
            $("#transferclienting").html("Đang chuyển khách hàng");
            $idassign=$('#assign_id').val();
            $user_fullname=$("#user_id_data_asign :selected").text(); // The text content of the selected option
            $user_to=$("#user_id_data_asign :selected").val(); 
            $lead=$('#lead_id').val();
            $task_title=$('#task_title').val();
            $task_description=$('#task_description').val();
            $task_deadline=$('#task_deadline').val();
            $group_id=$("#group_user_id :selected").val(); 
            if($lead!="" &&  $user_to!=""){
                $.ajax({
                    type: "post",
                    url: '{{ url('lead/updateassignlead')}}',
                    data: {'id': $idassign, 'lead_id': $lead, 'user_to': $user_to, 'user_fullname': $user_fullname, 'task_title': $task_title, 'task_description': $task_description, 'task_deadline': $task_deadline, 'group_id': $group_id, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        location.reload();
                    }
                });
            }else{
                return false;
            }
        
        }
         $(document).ready(function(){

            var dateTimeFormat = 'Y-m-d H:i';
            flatpickr("#task_deadline", {
                //minDate: '@if(isset($task)){{$task["task_end"]}}@endif',
                dateFormat: dateTimeFormat,
                enableTime: true,
            });
          
        });
        function showuser($groupid, $user){
            if($groupid!="" && $groupid>0){
                $("#user_id_data_asign").empty();
                $.ajax({
                    method: "get",
                    url: "{{ url('groupuser/user_group')}}",
                    data: {group_id: $groupid, _token: '{{ csrf_token() }}'},
                    success: function(data) {
                        if(data){
                            $.each(data, function (i, item) {
                                $selected="";
                                if(item.id==$user){
                                    $selected="selected";
                                }
                                $("#user_id_data_asign").append('<option '+$selected+' value="'+item.id+'">'+item.fullname+'</option>');
                            })
                        }
                    }
                });
            }
        }
        @if($assign && $assign!='')
        showuser('{{$assign["group_id"]}}', '{{$assign["user_id"]}}');
        @endif
        
        
    </script>
