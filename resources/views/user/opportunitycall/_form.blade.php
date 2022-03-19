<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($call))
            {!! Form::model($call, ['url' => $type . '/' . $opportunity->id . '/' . $call->id, 'id' => 'opportunity_call', 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type. '/' . $opportunity->id , 'id' => 'opportunity_call', 'method' => 'post', 'files'=> true]) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('company_name') ? 'has-error' : '' }}">
                        {!! Form::label('company_name', trans('call.company'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('company_name', $companies[$opportunity->company_name], ['class' => 'form-control', 'readonly'=>'readonly']) !!}
                            <span class="help-block">{{ $errors->first('company_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('date') ? 'has-error' : '' }}">
                    {!! Form::label('date', trans('call.date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('date', isset($call) ? $call->call_date : null, ['class' => 'form-control date']) !!}
                        <span class="help-block">{{ $errors->first('date', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('duration') ? 'has-error' : '' }}">
                    {!! Form::label('duration', trans('call.duration'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::input('number','duration',null, ['class' => 'form-control', 'min'=>'1']) !!}
                        <span class="help-block">{{ $errors->first('duration', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('call_summary') ? 'has-error' : '' }}">
                    {!! Form::label('call_summary', trans('call.summary'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('call_summary', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('call_summary', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('resp_staff_id') ? 'has-error' : '' }}">
                    {!! Form::label('resp_staff_id', trans('salesteam.main_staff'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('resp_staff_id', $staffs, null, ['id'=>'resp_staff_id', 'class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('resp_staff_id', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}
                </button>
                <a href="{{ url($type.'/'.$opportunity->id ) }}" class="btn btn-warning"><i
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
            @if(!isset($call))
            var mainStaff='{{$opportunity->salesteam}}';
            $("#resp_staff_id").find("option[value='"+mainStaff+"']").attr('selected',true);
            $("#resp_staff_id").find("option[value!='"+mainStaff+"']").attr('selected',false);
            @endif
            $("#resp_staff_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('salesteam.main_staff') }}"
            });
            $("#date").on("dp.change",function(){
               $("#opportunity_call").bootstrapValidator('revalidateField','date');
            });
            $("#opportunity_call").bootstrapValidator({
                fields: {
                    date: {
                        validators: {
                            notEmpty: {
                                message: 'The date field is required.'
                            }
                        }
                    },
                    duration: {
                        validators: {
                            notEmpty: {
                                message: 'The duration field is required.'
                            }
                        }
                    },
                    call_summary: {
                        validators: {
                            notEmpty: {
                                message: 'The call summary field is required.'
                            }
                        }
                    },
                    company_name: {
                        validators: {
                            notEmpty: {
                                message: 'The company field is required.'
                            }
                        }
                    },
                    resp_staff_id: {
                        validators: {
                            notEmpty: {
                                message: 'The main staff field is required.'
                            }
                        }
                    }
                }
            });
            var dateFormat = '{{ config('settings.date_format') }}';
            flatpickr("#date", {
                minDate: '{{ isset($call) ? $call->created_at : now() }}',
                dateFormat: dateFormat,
                disableMobile: "true",
            });
        });
    </script>
@endsection
