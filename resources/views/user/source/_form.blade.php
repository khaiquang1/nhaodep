<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($source))
            {!! Form::model($source, ['url' => $type . '/' . $source->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('source') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('source.title'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('title', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('value', trans('source.value'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('value', null, ['class' => 'form-control', 'placeholder'=>'Gía trị']) !!}
                            <span class="help-block">{{ $errors->first('value', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                        <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                            {!! Form::label('position', trans('source.position'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::text('position', null, ['class' => 'form-control resize_vertical','placeholder'=>'Vị trí']) !!}
                                <span class="help-block">{{ $errors->first('position', ':message') }}</span>
                            </div>
                        </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($source) && $source->status==1)checked @endif>
                            {{trans('source.status')}} </label>
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