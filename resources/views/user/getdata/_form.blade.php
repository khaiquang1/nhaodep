<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($getdata))
            {!! Form::model($getdata, ['url' => $type . '/' . $getdata->id, 'method' => 'put', 'files'=> true, 'id'=>'getdata']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'getdata']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('getdata') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('getdata.title'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('title', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('type', trans('getdata.type'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('type', null, ['class' => 'form-control resize_vertical','placeholder'=>'Loại']) !!}
                            <span class="help-block">{{ $errors->first('type', ':message') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('brand_id', trans('staff.branch'), ['class' => 'control-label']) !!}
                        <div class="controls">
                        {!! Form::select('branch_id', $branch, (isset($getdata)?$getdata->branch_id:null), ['id'=>'branch_id','class' => 'form-control', 'onchange'=>'getUser(this.value)']) !!}
                        <span class="help-block">{{ $errors->first('branch_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('user_id', trans('staff.staff'), ['class' => 'control-label']) !!}
                        <div class="form-group" id="user_list"></div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('token', trans('getdata.token'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('token', null, ['class' => 'form-control resize_vertical','placeholder'=>'Token']) !!}
                            <span class="help-block">{{ $errors->first('token', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('page_id', trans('getdata.page_id'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('page_id', null, ['class' => 'form-control resize_vertical','placeholder'=>'Page']) !!}
                            <span class="help-block">{{ $errors->first('page_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('group_id', trans('lead.group_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('group_id', $groupLead, null, ['id'=>'group_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('group_id', ':message') }}</span>
                        </div>   

                    </div>

                </div>
                <div class="col-md-4">

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('client_status_id', trans('Nhóm khách hàng'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                            <div class="controls">
                                {!! Form::select('client_status_id', $statusGroup, null, ['id'=>'client_status_id', 'class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>

            branch

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($getdata) && $getdata->status==1)checked @endif>
                            {{trans('getdata.status')}} </label>
                    </div>
                </div>
            </div>

        <!-- Form Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}
                        </button>
                        <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ./ form actions -->

{{--            {{ $newSales }}--}}

        {!! Form::close() !!}
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
        
        });

        function getUser(branch) {
            $('#user_list').empty();
            $.ajax({
                type: "GET",
                url: '{{ url('staff/user_list')}}',
                data: {'branch_id': branch, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $.each(data, function (i, item)  {
                        $check="";
                        @if(isset($listUserCareData) && $listUserCareData!="")
                            if(inArray(item.id, {{json_encode($listUserCareData)}})){
                                $check="checked";
                            }
                        @endif
                        $('#user_list').append("<label> <input type=\"checkbox\" "+$check+" value='"+item.id+"' name=\"user_id[]\" id='user_id"+item.id+"' class=\"icheck\"/>&nbsp;&nbsp;"+item.full_name+"</label>&nbsp;&nbsp;");
                    });
                }
            });
        }
        function inArray(needle, haystack) {
            var length = haystack.length;
            for(var i = 0; i < length; i++) {
                if(haystack[i] == needle) return true;
            }
            return false;
        }
        @if(isset($getdata) && $getdata!="")
            getUser('{{$getdata["branch_id"]}}');
        @endif


        $('#group_id').change(function () {
            if($(this).val()!="" && $(this).val()!=null){
                getstatus($(this).val());
            }
         });
        function getstatus(groupid) {
            $.ajax({
                type: "GET",
                url: '{{ url('lead/statusgroup')}}',
                data: {'group_id': groupid, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('#client_status_id').empty();
                    $('#client_status_id').select2({
                        theme: "bootstrap",
                        placeholder: "Chọn tình trạng"
                    }).trigger('change');
                    $.each(data, function (i, item) {
                    $('#client_status_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($getdata) && $getdata->id){{$getdata->client_status_id}}@endif" ? true : false));
                    });
                }
            }); 
        }
    </script>
@stop