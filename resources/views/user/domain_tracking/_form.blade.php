<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($DomainTracking))
            {!! Form::model($DomainTracking, ['url' => $type . '/' . $DomainTracking->id.'/edit', 'method' => 'post',  'id'=> 'DomainTracking']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'id'=> 'DomainTracking']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('domain') ? 'has-error' : '' }}">
                        {!! Form::label('domain', trans('domain_tracking.domain_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('domain', null, ['class' => 'form-control','placeholder' => 'Domain name']) !!}
                            <span class="help-block">{{ $errors->first('domain', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('domain_type') ? 'has-error' : '' }}">
                        {!! Form::label('domain_type', trans('domain_tracking.domain_type'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('domain_type', $domainTypeList, null, ['id'=>'domain_type','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('domain_type', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('ga_id') ? 'has-error' : '' }}">
                        {!! Form::label('ga_id', trans('domain_tracking.ga_id'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('ga_id', null, ['class' => 'form-control','placeholder' => 'GA account']) !!}
                            <span class="help-block">{{ $errors->first('ga_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                        {!! Form::label('status', trans('domain.status'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                            <input type='radio' name='status' value='1' @if(isset($DomainTracking) && $DomainTracking->status==1)  class='icheck' checked @endif /> Active
                            <input type='radio' name='status' value='0' @if(isset($DomainTracking) && $DomainTracking->status==0)  class='icheck' checked @endif /> UnActive

                        </div>
                    </div>
                </div>
            </div>
            <!-- Form Actions -->
            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                </div>
            </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>


@section('scripts')
    <script>
        $(document).ready(function () {

            var MaxInputs = 50; //maximum input boxes allowed
            var InputsWrapper = $("#InputsWrapper"); //Input boxes wrapper ID

            var x = InputsWrapper.length; //initlal text box count
            var FieldCount = 1; //to keep track of text box added

        
            
//            form validation
            $("#DomainTracking").bootstrapValidator({
                fields: {
                   
                    domain: {
                        validators: {
                            notEmpty: {
                                message: 'The domain name field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The domain name must be minimum 3 characters.'
                            }
                        }
                    },
                    domain_type: {
                        validators: {
                            notEmpty: {
                                message: 'The domain type field is required.'
                            }
                        }
                    },
                }
            });
        });
    </script>
@endsection
