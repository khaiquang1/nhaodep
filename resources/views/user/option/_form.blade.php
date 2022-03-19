<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($option))
            {!! Form::model($option, ['url' => $type . '/' . $option->id, 'method' => 'put', 'files'=> true, 'id'=>'options']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id' => 'options']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('category') ? 'has-error' : '' }}">
                        {!! Form::label('type', trans('option.category'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('category', $categories, null, ['id'=>'category', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('category', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('option.title'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('title', null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('value') ? 'has-error' : '' }}">
                        {!! Form::label('value', trans('option.value'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('value', null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('value', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i
                            class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                <a href="{{ url($type) }}" class="btn btn-warning"><i
                            class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
            </div>
        </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function(){
            $("#category").select2({
                theme:"bootstrap",
                placeholder: "{{trans('option.category')}}"
            });
            @if(!isset($option))
                $("#category").prepend("<option value='' selected>{{trans('option.category')}}</option>");
            @endif
            $("#options").bootstrapValidator({
                fields: {
                    category: {
                        validators: {
                            notEmpty: {
                                message: 'The category field is required'
                            }
                        }
                    },
                    title:{
                        validators: {
                            notEmpty: {
                                message: 'The title field is required'
                            }
                        }
                    },
                    value:{
                        validators: {
                            notEmpty: {
                                message: 'The value field is required'
                            }
                        }
                    }
                }
            })
        });
    </script>
    @endsection