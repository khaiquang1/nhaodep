
<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($contentautomation))
            {!! Form::model($contentautomation, ['url' => $type . '/' . $contentautomation->id, 'method' => 'put', 'files'=> true, 'id'=>'libcontent']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'contentautomation']) !!}
        @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('contentautomation') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('contentautomation.title'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('title', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('type', trans('contentautomation.type_content'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                        <div class="controls">
                            <select name="type" id="type" class="form-control">
                                <option value="text" @if(isset($contentautomation) && $contentautomation["type"]=="text") selected @endif>Nội dung</option>
                                <option value="video" @if(isset($contentautomation) && $contentautomation["type"]=="video") selected @endif>Video</option>
                                <option value="photo" @if(isset($contentautomation) && $contentautomation["type"]=="photo") selected @endif>Hình</option>
                                <option value="file" @if(isset($contentautomation) && $contentautomation["type"]=="file") selected @endif>File</option>
                                <option value="address" @if(isset($contentautomation) && $contentautomation["type"]=="address") selected @endif>Hỏi địa chỉ</option>
                                <option value="user_email" @if(isset($contentautomation) && $contentautomation["type"]=="user_email") selected @endif>Hỏi email</option>
                                <option value="user_phone_number" @if(isset($contentautomation) && $contentautomation["type"]=="user_phone_number") selected @endif>Hỏi số điện thoại</option>
                                <option value="school" @if(isset($contentautomation) && $contentautomation["type"]=="phone") selected @endif>Hỏi học trường nào</option>

                            </select>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('parent_id', trans('contentautomation.parent'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                            <div class="controls">
                                <select name="parent_id" id="parent_id" class="form-control">
                                    <option value="0">Chọn mục gốc</option>
                                    @if($contentParent && $contentParent!="")
                                    {!! $contentParent!!}
                                    @endif
                                </select>
                            </div>
                        </div>
                </div>
                <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('keyword', trans('contentautomation.keyword'), ['class' => 'control-label required', 'placeholder' => 'Keyword']) !!}
                            <div class="controls">
                            {!! Form::text('keyword', null, ['class' => 'form-control', 'placeholder'=>'Từ khóa']) !!}
                            </div>
                        </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('reply', trans('contentautomation.content'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('reply', null, ['class' => 'form-control resize_vertical','placeholder'=>'Nội dung', 'rows'=>20, 'style="height: auto!important;"']) !!}
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($contentautomation) && $contentautomation->status==1)checked @endif>
                            {{trans('contentautomation.status')}} </label>
                    </div>
                </div>
                <div class="col-md-4">
                        <div class="form-group">
                         {!! Form::label('type_content', "Loai nội dung", ['class' => 'control-label', 'placeholder' => 'Loai nội dung']) !!}
                            <div class="controls">
                                <select name="type_content" id="type_content" class="form-control" onchange="return showhidefield(this.value)">
                                    <option value="normal" @if(isset($contentautomation) && $contentautomation["type_content"]=="normal") selected @endif>Normal</option>
                                    <option value="promotion" @if(isset($contentautomation) && $contentautomation["type_content"]=="promotion") selected @endif>Promotion</option>
                                </select>
                            </div>
                        </div>
                </div>
                <div class="col-md-4" id="promotioncode_field" @if(!isset($contentautomation) || $contentautomation["type_content"]!="promotion") style="display:none" @endif>
                        <div class="form-group">
                            {!! Form::label('promotion_code', "Mã Khuyến mãi", ['class' => 'control-label', 'placeholder' => 'Mã khuyến mãi']) !!}
                            <div class="controls">
                            {!! Form::text('promotion_code', null, ['class' => 'form-control', 'placeholder'=>'Mã khuyến mãi']) !!}
                            </div>
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
       function showhidefield($value){
            if($value=="promotion"){
                $('#promotioncode_field').show();
            }else{
                $('#promotioncode_field').hide();
            }
            return true;
       }
    </script>
@stop