<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($smsconfig))
            {!! Form::model($smsconfig, ['url' => $type . '/' . $smsconfig->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('device') ? 'has-error' : '' }}">
                        {!! Form::label('device', trans('smsconfig.device'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('device', $deviceToken, null, ['id'=>'device','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('device', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('smsconfig') ? 'has-error' : '' }}">
                        {!! Form::label('name', trans('smsconfig.name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('note', trans('smsconfig.note'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('note', null, ['class' => 'form-control resize_vertical','placeholder'=>'Ghi chú']) !!}
                            <span class="help-block">{{ $errors->first('note', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('smsconfig') ? 'has-error' : '' }}">
                        {!! Form::label('limit_sms', trans('smsconfig.limit_sms'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('limit_sms', null, ['class' => 'form-control', 'placeholder'=>'Giới hạn số lương sms/ngày']) !!}
                            <span class="help-block">{{ $errors->first('limit_sms', ':message') }}</span>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="row">
            <div class="col-md-4">
                    <div class="form-group">
                        <label>
                        <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($smsconfig) && $smsconfig->status==1)checked @endif>
                            {{trans('smsconfig.status')}} </label>                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
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

@section('script')
    <script>

    $('#city_id').change(function () {
        if($(this).val()!="" && $(this).val()!=null){
            getdistrict($(this).val());
        }
    });
    $('#district_id').change(function () {
        if($(this).val()!="" && $(this).val()!=null){
            getward($(this).val());
        }
    });
    getcities(241);
    @if(isset($smsconfig) && $smsconfig->id)
    getdistrict({{$smsconfig->city_id}});
    getward({{$smsconfig->district_id}});
    @endif
    function getcities(country) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_city_list')}}',
            data: {'id': country, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#city_id').empty();
                $('#city_id').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn Tỉnh/Thành phố"
                }).trigger('change');
                $.each(data, function (i, item) {
                    $('#city_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($smsconfig) && $smsconfig->id){{$smsconfig->city_id}}@endif" ? true : false));

                });

            }
        });  
    }
    function getdistrict(cities) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_district_list')}}',
            data: {'id': cities, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#district_id').empty();
                $('#district_id').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn Quận/Huyện"
                }).trigger('change');
                $.each(data, function (i, item) {
                   $('#district_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($smsconfig) && $smsconfig->id){{$smsconfig->district_id}}@endif" ? true : false));
                });
            }
        }); 
    }
    function getward(district) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_ward_list')}}',
            data: {'id': district, _token: '{{ csrf_token() }}'},
            
            success: function (data) {
                $('#ward_id').empty();
                $('#ward_id').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn Phường/Xã"
                }).trigger('change');
                $.each(data, function (i, item) {
                    $('#ward_id').append($('<option></option>').val(i).html(item).attr('selected', i == "@if(isset($smsconfig) && $smsconfig->id){{$smsconfig->ward_id}}@endif" ? true : false));
                });
            }
        }); 
    }
    </script>
    <script src="{{ URL::asset('assets/libs/select2/js/select2.min.js')}}"></script>

@stop