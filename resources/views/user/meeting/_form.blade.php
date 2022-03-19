<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($meeting))
            {!! Form::model($meeting, ['url' => $type . '/' . $meeting->id, 'method' => 'put', 'id'=>'meeting', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'id'=>'meeting', 'files'=> true]) !!}
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('meeting_subject') ? 'has-error' : '' }}">
                    {!! Form::label('meeting_subject', trans('meeting.meeting_subject'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('meeting_subject', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('meeting_subject', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
       <div class="row">
           <div class="col-md-12">
               <div class="form-group {{ $errors->has('company_attendees') ? 'has-error' : '' }}">
                   {!! Form::label('company_attendees', trans('meeting.company_attendees'), ['class' => 'control-label required']) !!}
                   <div class="controls">
                       {!! Form::select('company_attendees[]', $company_attendees, (isset($meeting)?$company_attendee:null), ['id'=>'attendees','multiple'=>'multiple', 'class' => 'form-control select2']) !!}
                       <span class="help-block">{{  $errors->first('company_attendees', ':message') }}</span>
                   </div>
               </div>
           </div>
       </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('responsible_id') ? 'has-error' : '' }}">
                        {!! Form::label('responsible_id', trans('salesteam.main_staff'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('responsible_id', (isset($meeting)?$staffs:$staffs), null,['class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('responsible_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('staff_attendees') ? 'has-error' : '' }}">
                        {!! Form::label('staff_attendees', trans('meeting.staff_attendees'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            @if(isset($meeting))
                                {!! Form::select('staff_attendees[]', $mainStaff, (isset($meeting)?$staff_attendees:null), ['id'=>'staff_attendees','multiple'=>'multiple', 'class' => 'form-control']) !!}
                            @else
                                {!! Form::select('staff_attendees[]', $staffs, (isset($meeting)?$staff_attendees:null), ['id'=>'staff_attendees','multiple'=>'multiple', 'class' => 'form-control']) !!}
                            @endif
                            <span class="help-block">{{ $errors->first('staff_attendees', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('meeting.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($meeting) ? $meeting->meeting_starting_date : null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('starting_date', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('ending_date') ? 'has-error' : '' }}">
                    {!! Form::label('ending_date', trans('meeting.ending_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('ending_date', isset($meeting) ? $meeting->meeting_ending_date : null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('ending_date', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('location') ? 'has-error' : '' }}">
                    {!! Form::label('location', trans('meeting.location'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('location', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('location', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('meeting_description') ? 'has-error' : '' }}">
                    {!! Form::label('meeting_description', trans('meeting.meeting_description'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::textarea('meeting_description', null, ['class' => 'form-control resize_vertical']) !!}
                        <span class="help-block">{{ $errors->first('meeting_description', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>
                        <input type="checkbox" value="1" name="all_day" id="all_day" class='icheck'
                               @if(isset($meeting) && $meeting->all_day==1)checked @endif>
                        {{trans('meeting.all_day')}} </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('privacy') ? 'has-error' : '' }}">
                    {!! Form::label('privacy', trans('meeting.privacy'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('privacy', $privacy, null, ['class' => 'form-control select2']) !!}
                        <span class="help-block">{{ $errors->first('privacy', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('show_time_as') ? 'has-error' : '' }}">
                    {!! Form::label('show_time_as', trans('meeting.show_time_as'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('show_time_as', $show_times, null, ['class' => 'form-control select2']) !!}
                        <span class="help-block">{{ $errors->first('show_time_as', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
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
            @if(isset($meeting))
                    if($(".icheckbox_minimal-blue").hasClass('checked')){
                        $("#show_time_as").find("option:contains('Free')").remove();
                    }
            @endif
            $('#all_day').on('ifChecked', function(event){
                $("#show_time_as").find("option:contains('Busy')").attr('selected',true);
                $("#show_time_as").find("option:contains('Free')").remove();
            });
            $('#all_day').on('ifUnchecked', function(event){
                $("#show_time_as").prepend('<option value="Free" selected>{{ trans("Free") }}</option>');
                $("#show_time_as").find("option:contains('Busy')").attr('selected',false);
            });

            var dateTimeFormat = '{{ config('settings.date_format').' H:i' }}';
            flatpickr('#starting_date',{
                minDate: '{{ isset($meeting) ? $meeting->created_at : now() }}',
                dateFormat: dateTimeFormat,
                enableTime: true,
                disableMobile: "true",
                "plugins": [new rangePlugin({ input: "#ending_date"})],
                onChange:function(){
                    $('#meeting').bootstrapValidator('revalidateField', 'ending_date');
                }
            });

            function mainStaffChange(){
                $("#responsible_id").on("change",function(){
                    $("#staff_attendees").find("option").attr("selected",false);
                    $("#staff_attendees").select2({
                        placeholder:"{{ trans('salesteam.staff_members') }}",
                        theme: 'bootstrap'
                    });
                    var mainStaff=$(this).val();
                    var staffMembers=$("#staff_attendees").find("option[value='"+mainStaff+"']").val();
                    $("#staff_attendees").find("option").prop('disabled',false);
                    if(mainStaff=staffMembers){
                        $("#staff_attendees").find("option[value='"+mainStaff+"']").prop('disabled',true);
                    }
                });
            }
            mainStaffChange();
            $("#staff_attendees").select2({
                placeholder:"{{ trans('salesteam.staff_members') }}",
                theme: 'bootstrap'
            }).find("option:first").attr({
                selected:false
            });

            $("#responsible_id").select2({
                placeholder:"{{ trans('salesteam.main_staff') }}",
                theme: 'bootstrap'
            }).find("option:first").attr({
                selected:false,
                value:''
            });
            var mainStaff=$("#responsible_id").val();
            var staffMembers=$("#staff_attendees").find("option[value='"+mainStaff+"']").val();

            if(mainStaff=staffMembers){
                $("#staff_attendees").find("option[value='"+mainStaff+"']").prop('disabled',true);
            }
            $("#meeting").bootstrapValidator({
                fields: {
                    meeting_subject: {
                        validators: {
                            notEmpty: {
                                message: 'The meeting subject field is required.'
                            }
                        }
                    },
                    responsible_id: {
                        validators: {
                            notEmpty: {
                                message: 'The main staff field is required.'
                            }
                        }
                    },
                    'company_attendees[]': {
                        validators: {
                            notEmpty: {
                                message: 'The company attendees field is required.'
                            }
                        }
                    },
                    starting_date: {
                        validators: {
                            notEmpty: {
                                message: 'The starting date field is required.'
                            }
                        }
                    },
                    ending_date: {
                        validators: {
                            notEmpty: {
                                message: 'The ending date field is required.'
                            }
                        }
                    },
                    location: {
                        validators: {
                            notEmpty: {
                                message: 'The location field is required.'
                            }
                        }
                    }
                }
            });
        });
    </script>
@stop