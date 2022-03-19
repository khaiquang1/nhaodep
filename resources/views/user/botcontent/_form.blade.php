<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($getdata))
            {!! Form::model($getdata, ['url' => $type . '/' . $getdata->id, 'method' => 'put', 'files'=> true, 'id'=>'getdata']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'getdata']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required">
                        {!! Form::label('pages',  trans('lead.pages'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                        <div class="controls">
                            <select class="listselect2 form-control" name="status" id="status">  
                                <option value="0">Tất cả</option>
                                  @if($pagesList)
                                    @foreach($pagesList as $key=>$value)
                                        <option value="{{$key}}" @if(isset($getdata) && $key==$getdata["partner_id"]) selected @endif>{{$value}}</option>
                                    @endforeach
                                @endif
                            </select>                
                            <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('content', trans('getdata.content'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('content', null, ['class' => 'form-control', 'placeholder'=>'Nội dung']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('keyword', trans('botcontent.keyword'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('keyword', null, ['class' => 'form-control resize_vertical','placeholder'=>'Keyword']) !!}
                            <span class="help-block">{{ $errors->first('keyword', ':message') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('tags', trans('botcontent.tags'), ['class' => 'control-label']) !!}
                        <div class="controls">
                        {!! Form::text('tags', null, ['class' => 'form-control resize_vertical','placeholder'=>'tags']) !!}
                        <span class="help-block">{{ $errors->first('branch_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('button_text_next', trans('botcontent.button_text_next'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('button_text_next', null, ['class' => 'form-control resize_vertical','placeholder'=>'Text nút tiếp theo']) !!}
                            <span class="help-block">{{ $errors->first('button_text_next', ':message') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('keyword_text_next', trans('botcontent.keyword_text_next'), ['class' => 'control-label']) !!}
                        <div class="controls">
                        {!! Form::text('keyword_text_next', null, ['class' => 'form-control resize_vertical','placeholder'=>'keyword_text_next']) !!}
                        <span class="help-block">{{ $errors->first('keyword_text_next', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                    {!! Form::label('type_button_next', trans('botcontent.type_button_next'), ['class' => 'control-label']) !!}
                        <div class="controls">
                        {!! Form::text('type_button_next', null, ['class' => 'form-control resize_vertical','placeholder'=>'type_button_next']) !!}
                        <span class="help-block">{{ $errors->first('type_button_next', ':message') }}</span>
                        </div>
                    </div>
                </div>

            </div>

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
    </script>
@stop