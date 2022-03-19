<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($contract))
            {!! Form::model($contract, array('url' => $type . '/' . $contract->id, 'method' => 'put', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => $type, 'method' => 'post', 'files'=> true)) !!}
        @endif
        <div class="form-group required {{ $errors->has('start_date') ? 'has-error' : '' }}">
            {!! Form::label('start_date', trans('contract.start_date'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('start_date', null, array('class' => 'form-control date')) !!}
                <span class="help-block">{{ $errors->first('start_date', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('end_date') ? 'has-error' : '' }}">
            {!! Form::label('end_date', trans('contract.end_date'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('end_date', null, array('class' => 'form-control date')) !!}
                <span class="help-block">{{ $errors->first('end_date', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('description') ? 'has-error' : '' }}">
            {!! Form::label('description', trans('contract.description'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('description', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('description', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('company_id') ? 'has-error' : '' }}">
            {!! Form::label('company_id', trans('contract.company'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::select('company_id', $companies, null, array('id'=>'company_id', 'class' => 'form-control select2')) !!}
                <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('resp_staff_id') ? 'has-error' : '' }}">
            {!! Form::label('resp_staff_id', trans('contract.resp_staff_id'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::select('resp_staff_id', $staffs, null, array('id'=>'resp_staff_id', 'class' => 'form-control select2')) !!}
                <span class="help-block">{{ $errors->first('resp_staff_id', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('real_signed_contract_file') ? 'has-error' : '' }}">
            {!! Form::label('real_signed_contract_file', trans('contract.real_signed_contract'), array('class' => 'control-label')) !!}
            <div class="controls row" v-image-preview>
                <div class="col-xs-12 col-sm-4">
                    @if(isset($contract->real_signed_contract))
                        <img src="{{ url('uploads/contract/thumb_'.$contract->real_signed_contract) }}"
                             alt="Signed">
                    @endif
                </div>
                <div class="col-xs-12 col-sm-8">
                    <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                            <img id="image-preview" width="300">
                        </div>
                        <div>
                        <span class="btn btn-default btn-file"><span
                                    class="fileinput-new">{{trans('dashboard.select_image')}}</span>
                            <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                            <input type="file" name="real_signed_contract_file"></span>
                            <a href="#" class="btn btn-default fileinput-exists"
                               data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5">
                    <span class="help-block">{{ $errors->first('real_signed_contract_file', ':message') }}</span>
                </div>
            </div>
        </div>
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <a href="{{ route($type.'.index') }}" class="btn btn-primary"><i
                            class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                <button type="submit" class="btn btn-success"><i
                            class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
            </div>
        </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>


@section('scripts')
    <script>
        $(document).ready(function () {
            $('#start_date').datetimepicker({format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-arrow-up",
                    down: "fa fa-arrow-down"
                }});
            $('#end_date').datetimepicker({
                useCurrent: false,
                format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-arrow-up",
                    down: "fa fa-arrow-down"
                }
            });
            $("#start_date").on("dp.change", function (e) {
                $('#end_date').data("DateTimePicker").minDate(e.date);
            });
            $("#end_date").on("dp.change", function (e) {
                $('#start_date').data("DateTimePicker").maxDate(e.date);
            });
        });
    </script>
@stop
