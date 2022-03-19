<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($company))
            {!! Form::model($company, ['url' => $type . '/' . $company->id, 'method' => 'put', 'files'=> true, 'id'=>'company']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'company']) !!}
        @endif
            <div class="form-group required {{ $errors->has('company_avatar_file') ? 'has-error' : '' }}">
                {!! Form::label('company_avatar_file', trans('company.company_avatar'), ['class' => 'control-label']) !!}
                <div class="controls row" v-image-preview>
                    <div class="col-sm-12">
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                <img id="image-preview" width="300" class="img-responsive">
                                @if(isset($company->company_avatar) && $company->company_avatar!="")
                                    <img src="{{ url('uploads/company/thumb_'.$company->company_avatar) }}"
                                         alt="Image" class="img-responsive" width="300">
                                @endif
                            </div>
                            <div class="m-t-10">
                                <span class="btn btn-default btn-file">
                                    <span class="fileinput-new">{{trans('dashboard.select_image')}}</span>
                                    <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                                    <input type="file" name="company_avatar_file">
                                </span>
                                <a href="#" class="btn btn-default fileinput-exists"
                                   data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                            </div>
                            <span class="help-block">{{ $errors->first('company_avatar_file', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('name', trans('company.company_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('name', null, ['class' => 'form-control','placeholder'=>'Company name']) !!}
                            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('website') ? 'has-error' : '' }}">
                        {!! Form::label('website', trans('company.website'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('website', null, ['class' => 'form-control','placeholder'=>'Company website']) !!}
                            <span class="help-block">{{ $errors->first('website', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('phone') ? 'has-error' : '' }}">
                        {!! Form::label('phone', trans('company.phone'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('phone', null, ['class' => 'form-control','data-fv-integer' => "true",'placeholder'=>'Phone']) !!}
                            <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('mobile') ? 'has-error' : '' }}">
                        {!! Form::label('mobile', trans('company.mobile'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('mobile', null, ['class' => 'form-control','data-fv-integer' => "true",'placeholder'=>'Mobile']) !!}
                            <span class="help-block">{{ $errors->first('mobile', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="form-group">
                <h3>Location</h3>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group {{ $errors->has('country_id') ? 'has-error' : '' }}">
                        {!! Form::label('country_id', trans('company.country'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('country_id', $countries, null, ['id'=>'country_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('country_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('state_id') ? 'has-error' : '' }}">
                        {!! Form::label('state_id', trans('company.state'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('state_id', isset($company)?$states:[0=>trans('company.select_state')], null, ['id'=>'state_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('state_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('city_id') ? 'has-error' : '' }}">
                        {!! Form::label('city_id', trans('company.city'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('city_id', isset($company)?$cities:[0=>trans('company.select_city')], null, ['id'=>'city_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('city_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
       <div class="row">
           <div class="col-md-12">
               <div class="form-group required {{ $errors->has('address') ? 'has-error' : '' }}">
                   {!! Form::label('address', trans('company.address'), ['class' => 'control-label required']) !!}
                   <div class="controls">
                       {!! Form::textarea('address', null, ['class' => 'form-control resize_vertical','placeholder'=>'Address']) !!}
                       <span class="help-block">{{ $errors->first('address', ':message') }}</span>
                   </div>
               </div>
           </div>
       </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-success"><i
                                        class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                            <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        {!! Form::hidden('latitude', null, ['class' => 'form-control', 'id'=>"latitude"]) !!}
        {!! Form::hidden('longitude', null, ['class' => 'form-control', 'id'=>"longitude"]) !!}
        {!! Form::close() !!}
    </div>
</div>
@section('scripts')
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAPS_KEY')}}&libraries=places"></script>
    <script>
        $(document).ready(function () {
            $("#company").bootstrapValidator({
                fields: {
                    company_avatar_file: {
                       validators:{
                           file: {
                               extension: 'jpeg,jpg,png',
                               type: 'image/jpeg,image/png',
                               maxSize: 1000000,
                               message: 'The logo format must be in jpeg, jpg or png and size less than 1MB'
                           }
                       }
                    },
                    name: {
                        validators: {
                            notEmpty: {
                                message: 'The company name field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The company name must be minimum 3 characters.'
                            }
                        }
                    },
                    website: {
                        validators: {
                            notEmpty: {
                                message: 'The company website field is required.'
                            },
                            uri: {
                                allowLocal: true,
                                message: 'The input is not a valid URL'
                            }
                        }
                    },
                    phone: {
                        validators: {
                            notEmpty: {
                                message: 'The phone number is required.'
                            },
                            regexp: {
                                regexp: /^\d{5,15}?$/,
                                message: 'The phone number can only consist of numbers.'
                            }
                        }
                    },
                    country_id: {
                        validators: {
                            notEmpty: {
                                message: 'The country field is required.'
                            }
                        }
                    },
                    state_id: {
                        validators: {
                            notEmpty: {
                                message: 'The state field is required.'
                            }
                        }
                    },
                    city_id: {
                        validators: {
                            notEmpty: {
                                message: 'The city field is required.'
                            }
                        }
                    },
                    address: {
                        validators: {
                            notEmpty: {
                                message: 'The address field is required.'
                            }
                        }
                    }
                }
            });
            $(".fileinput").find('input').change(function () {
                button_disabled();
                $("input").on("keyup",function(){
                    button_disabled();
                });
            });
            function button_disabled(){
                if($(".form-group.required").hasClass("has-error")){
                    $("button[type='submit']").attr("disabled",true);
                }else{
                    $("button[type='submit']").attr("disabled",false);
                }
            }

            $("#function").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('lead.function') }}"
            });
            $("#title").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('lead.title') }}"
            });
            $("#priority").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('lead.priority') }}"
            });
            $("#country_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('lead.select_country') }}"
            });
            $("#state_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('lead.select_state') }}"
            });
            $("#city_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('lead.select_city') }}"
            });
        });
        $("#state_id").find("option:contains('Select state')").attr({
            selected: true,
            value: ""
        });
        $("#city_id").find("option:contains('Select city')").attr({
            selected: true,
            value: ""
        });
        $('#country_id').change(function () {
            getstates($(this).val());
        });
        @if(old('country_id'))
        getstates({{old('country_id')}});

        @endif
        function getstates(country) {
            $.ajax({
                type: "GET",
                url: '{{ url('lead/ajax_state_list')}}',
                data: {'id': country, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('#state_id').empty();
                    $('#city_id').empty();
                    $('#state_id').select2({
                        theme: "bootstrap",
                        placeholder: "Select State"
                    }).trigger('change');
                    $('#city_id').select2({
                        theme: "bootstrap",
                        placeholder: "Select City"
                    }).trigger('change');
                    $.each(data, function (val, text) {
                        $('#state_id').append($('<option></option>').val(val).html(text).attr('selected', val == "{{old('state_id')}}" ? true : false));
                    });
                }
            });
        }

        $('#state_id').change(function () {
            getcities($(this).val());
        });
        @if(old('state_id'))
        getcities({{old('state_id')}});

        @endif
        function getcities(cities) {
            $.ajax({
                type: "GET",
                url: '{{ url('lead/ajax_city_list')}}',
                data: {'id': cities, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('#city_id').empty();
                    $('#city_id').select2({
                        theme: "bootstrap",
                        placeholder: "Select City"
                    }).trigger('change');
                    $.each(data, function (val, text) {
                        $('#city_id').append($('<option></option>').val(val).html(text).attr('selected', val == "{{old('city_id')}}" ? true : false));
                    });
                }
            });
        }
        $('#city_id').change(function () {
            var geocoder = new google.maps.Geocoder();
            if (typeof $('#city_id').select2('data')[0] !== "undefined" && typeof $('#state_id').select2('data')[0] !== "undefined") {
                geocoder.geocode({'address': '"' + $('#city_id').select2('data')[0].text + '",' + $('#state_id').select2('data')[0].text + '"'}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        $('#latitude').val(results[0].geometry.location.lat());
                        $('#longitude').val(results[0].geometry.location.lng());
                    }
                });
            }
        });
    </script>
@endsection