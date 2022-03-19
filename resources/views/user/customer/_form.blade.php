<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($customer))
            {!! Form::model($customer, ['url' => $type . '/' . $customer->id, 'method' => 'put', 'files'=> true,'id'=>'customer']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true,'id'=>'customer']) !!}
        @endif
            <div class="row">
            <div class="col-md-12">
                <div class="form-group {{ $errors->has('user_avatar_file') ? 'has-error' : '' }}">
                    {!! Form::label('user_avatar_file', trans('customer.customer_avatar'), ['class' => 'control-label']) !!}
                    <div class="controls row" v-image-preview>
                        <div class="col-sm-12">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                    <img id="image-preview" width="300">
                                    @if(isset($customer->user->user_avatar) && $customer->user->user_avatar!="")
                                        <img src="{{ url('uploads/avatar/thumb_'.$customer->user->user_avatar) }}"
                                             alt="Image" class="ima-responsive">
                                    @endif
                                </div>
                                <div>
                                    <span class="btn btn-default btn-file">
                                        <span class="fileinput-new">{{trans('dashboard.select_image')}}</span>
                                        <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                                        <input type="file" name="user_avatar_file">
                                    </span>
                                    <a href="#" class="btn btn-default fileinput-exists"
                                       data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <span class="help-block">{{ $errors->first('user_avatar_file', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('customer.title'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('title', $titles, (isset($user))?$user->customer->title:null, ['id'=>'title', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        {!! Form::label('first_name', trans('customer.first_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('first_name', isset($customer)?$customer->user->first_name:null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        {!! Form::label('last_name', trans('customer.last_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('last_name', isset($customer)?$customer->user->last_name:null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('job_position') ? 'has-error' : '' }}">
                        {!! Form::label('job_position', trans('customer.job_position'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('job_position', (isset($user))?$user->customer->job_position:null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('job_position', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('company_id') ? 'has-error' : '' }}">
                        {!! Form::label('company', trans('customer.company'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('company_id', $companies, (isset($user))?$user->customer->company_id:null, ['id'=>'company_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group {{ $errors->has('sales_team_id') ? 'has-error' : '' }}">
                        {!! Form::label('sales_team_id', trans('customer.sales_team_id'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('sales_team_id', $salesteams, (isset($user))?$user->customer->sales_team_id:null, ['id'=>'sales_team_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('sales_team_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        {!! Form::label('email', trans('customer.email'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::email('email', isset($customer)?$customer->user->email:null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                        {!! Form::label('phone_number', trans('customer.phone'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('phone_number', isset($customer)?$customer->user->phone_number:null, ['class' => 'form-control ','data-fv-integer' => 'true']) !!}
                            <span class="help-block">{{ $errors->first('phone_number', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                        {!! Form::label('mobile', trans('customer.mobile'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('mobile', (isset($user))?$user->customer->mobile:null, ['class' => 'form-control','data-fv-integer' => 'true']) !!}
                            <span class="help-block">{{ $errors->first('mobile', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @if(!Request::is('customer/*/edit'))
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            {!! Form::label('password', trans('customer.password'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::password('password', ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                            {!! Form::label('password_confirmation', trans('customer.password_confirmation'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                                <small class="text-danger" id='message'>Password is not matching.</small>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="row password_fields">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            {!! Form::label('password', trans('customer.password'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::password('password', ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                            {!! Form::label('password_confirmation', trans('customer.password_confirmation'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
                                <small class="text-danger" id='message'>Password is not matching.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m-b-10">
                    <button class="btn btn-warning change_password">Change password</button>
                </div>
                @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                        {!! Form::label('address', trans('customer.address'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('address', (isset($user))?$user->customer->address:null, ['class' => 'form-control resize_vertical']) !!}
                            <span class="help-block">{{ $errors->first('address', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
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
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function(){
            $("#message").hide();
            $("#password, #password_confirmation").on("keyup", function () {
                if ($("#password").val() == $("#password_confirmation").val()) {
                    $("#message").hide();
                } else{
                    $("#message").show();
                    $('#customer').data('bootstrapValidator').validate();
                }
            });
            $(".change_password").on("click",function(){
                $(".password_fields").show();
                $(this).hide();
                $('#customer').data('bootstrapValidator').validate();
            });
            $(".password_fields").hide();
            $("#customer").bootstrapValidator({
                fields: {
                    user_avatar_file: {
                        validators: {
                            file: {
                                extension: 'jpeg,jpg,png',
                                type: 'image/jpeg,image/png',
                                maxSize: 1000000,
                                message: 'The logo format must be in jpeg, jpg or png and size less than 1MB'
                            }
                        }
                    },
                    title: {
                        validators: {
                            notEmpty: {
                                message: 'The title field is required.'
                            }
                        }
                    },
                    first_name: {
                        validators: {
                            notEmpty: {
                                message: 'The first name field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The first name must be minimum 3 characters.'
                            }
                        }
                    },
                    last_name: {
                        validators: {
                            notEmpty: {
                                message: 'The last name field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The last name must be minimum 3 characters.'
                            }
                        }
                    },
                    company_id: {
                        validators: {
                            notEmpty: {
                                message: 'The company name field is required.'
                            }
                        }
                    },
                    sales_team_id: {
                        validators: {
                            notEmpty: {
                                message: 'The Sales team field is required.'
                            }
                        }
                    },
                    email: {
                        validators: {
                            notEmpty: {
                                message: 'The email field is required.'
                            }
                        }
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: 'The password field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The password must be minimum 3 characters.'
                            }
                        }
                    },
                    password_confirmation: {
                        validators: {
                            notEmpty: {
                                message: 'The password confirmation field is required.'
                            }
                        }
                    },
                    phone_number: {
                        validators: {
                            notEmpty: {
                                message: 'The phone number is required.'
                            },
                            regexp: {
                                regexp: /^\d{5,15}?$/,
                                message: 'The phone number can only consist of numbers.'
                            }
                        }
                    }
                }
            });
            $("#title").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('customer.title') }}"
            });
            $("#company_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('customer.company') }}"
            });
            $("#sales_team_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('customer.sales_team_id') }}"
            });
        })
    </script>
    @endsection
