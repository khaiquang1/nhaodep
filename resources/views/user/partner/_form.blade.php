<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($partner))
            {!! Form::model($partner, ['url' => $type . '/' . $partner->id, 'method' => 'put', 'files'=> true, 'id'=>'partner']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> false, 'id'=>'partner']) !!}
        @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('partner') ? 'has-error' : '' }}">
                        {!! Form::label('partner', trans('partner.name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder'=>'Tên đối tác']) !!}
                            <span class="help-block">{{ $errors->first('partner', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('partner') ? 'has-error' : '' }}">
                        {!! Form::label('partner', trans('partner.partner'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('number_sales', null, ['class' => 'form-control', 'placeholder'=>'Số lượng sales']) !!}
                            <span class="help-block">{{ $errors->first('partner', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('partner') ? 'has-error' : '' }}">
                        {!! Form::label('phone', trans('partner.phone'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('phone', null, ['class' => 'form-control', 'placeholder'=>'Phone']) !!}
                            <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('partner') ? 'has-error' : '' }}">
                        {!! Form::label('email', trans('partner.phone'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder'=>'Email']) !!}
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('partner') ? 'has-error' : '' }}">
                        {!! Form::label('address', trans('partner.address'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('address', null, ['class' => 'form-control', 'placeholder'=>'Phone']) !!}
                            <span class="help-block">{{ $errors->first('address', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('partner') ? 'has-error' : '' }}">
                        {!! Form::label('status', trans('partner.status'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                        <label><input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($partner) && $partner->status==1)checked @endif>  {!! trans('partner.status') !!} </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('notes', trans('partner.notes'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('notes', null, ['class' => 'form-control resize_vertical','placeholder'=>'About Team']) !!}
                            <span class="help-block">{{ $errors->first('notes', ':message') }}</span>
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
                        <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i  class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ./ form actions -->

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
    </script>
@endsection