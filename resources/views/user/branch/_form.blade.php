<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($branch))
            {!! Form::model($branch, ['url' => $type . '/' . $branch->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('branch') ? 'has-error' : '' }}">
                        {!! Form::label('name', trans('branch.name'), ['class' => 'control-label required']) !!}
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
                        {!! Form::label('description', trans('branch.description'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('description', null, ['class' => 'form-control resize_vertical','placeholder'=>'Nội dung cần tư vấn trong nhóm']) !!}
                            <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('branch') ? 'has-error' : '' }}">
                        {!! Form::label('phone', trans('branch.phone'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('phone', null, ['class' => 'form-control', 'placeholder'=>'Phone']) !!}
                            <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('branch') ? 'has-error' : '' }}">
                        {!! Form::label('email', trans('branch.email'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder'=>'Email']) !!}
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
            <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="2" name="type" id="type" class='icheck'
                                @if(isset($branch) && $branch->type==2)checked @endif> {{trans('branch.stock')}} </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($branch) && $branch->status==1)checked @endif>
                            {{trans('branch.status')}} </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="is_default" id="is_default" class='icheck'
                                @if(isset($branch) && $branch->is_default==1)checked @endif>
                            {{trans('branch.main')}} </label>
                    </div>
                </div>
            </div>

        <!-- contact -->
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('address', trans('lead.address'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('address', null, ['class' => 'form-control resize_vertical']) !!}
                        <span class="help-block">{{ $errors->first('address', ':message') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('city_id', trans('lead.city'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('city_id', [0=>trans('lead.select_city')], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('district_id', trans('lead.district'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('district_id', [0=>trans('lead.select_district')], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ward_id', trans('lead.ward'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('ward_id', [0=>trans('lead.select_ward')], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div> 
        </div>
        <!-- end contact -->

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

        {!! Form::close() !!}
    </div>
</div>
@section('script')
<script>
    $(document).ready(function () {

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
        @if(isset($branch) && $branch->id)
        getdistrict({{$branch->city_id}});
        getward({{$branch->district_id}});
        @endif
    }); 
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
                        $('#city_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($branch) && $branch->id){{$branch->city_id}}@endif" ? true : false));

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
                        $('#district_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($branch) && $branch->id){{$branch->district_id}}@endif" ? true : false));
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
                        $('#ward_id').append($('<option></option>').val(i).html(item).attr('selected', i == "@if(isset($branch) && $branch->id){{$branch->ward_id}}@endif" ? true : false));
                    });
                }
            }); 
        }
</script>
<script src="{{ URL::asset('assets/libs/select2/js/select2.min.js')}}"></script>
@endsection('script')