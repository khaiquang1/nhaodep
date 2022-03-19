<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($salesteam))
            {!! Form::model($salesteam, ['url' => $type . '/' . $salesteam->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('salesteam') ? 'has-error' : '' }}">
                        {!! Form::label('salesteam', trans('salesteam.salesteam'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('salesteam', null, ['class' => 'form-control', 'placeholder'=>'Sales team', 'required'=>'true']) !!}
                            <span class="help-block">{{ $errors->first('salesteam', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('team_leader') ? 'has-error' : '' }}">
                        {!! Form::label('team_leader', trans('salesteam.main_staff'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('team_leader', $staffs, null, ['id'=>'team_leader', 'required'=>'true', 'class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('team_leader', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('team_members') ? 'has-error' : '' }}">
                        {!! Form::label('team_members', trans('salesteam.staff_members'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('team_members[]', $staffs, isset($salesteam)?$salesteam->members:null, ['id'=>'team_members', 'multiple'=>'multiple', 'class' => 'select2 form-control select2-multiple']) !!}
                            <span class="help-block">{{ $errors->first('team_members', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group required {{ $errors->has('invoice_target') ? 'has-error' : '' }}">
                {!! Form::label('invoice_target', trans('salesteam.invoice_target'), ['class' => 'control-label required']) !!}
                <div class="controls">
                    {!! Form::text('invoice_target', null, ['class' => 'form-control input-sm moneyInput1 number', 'placeholder'=>'Mục tiêu']) !!}
                    <span class="help-block">{{ $errors->first('invoice_target', ':message') }}</span>
                </div>
            </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                    {!! Form::label('notes', trans('salesteam.notes'), ['class' => 'control-label']) !!}
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

<script>
    $(document).ready(function () {
        //$('.moneyInput1').inputmask("decimal", { radixPoint: ".", autoGroup: true, groupSeparator: ",", groupSize: 3 });
        //$('.numberInput').inputmask({ "mask": "9", "repeat": 10, "greedy": false });
    });
    $(document).ready(function () {
        function MainStaffChange(){
            $("#team_leader").select2({
                placeholder:"{{ trans('salesteam.main_staff') }}",
                theme: 'bootstrap'
            }).on("change",function(){
                var MainStaff=$(this).select2("val");
                var staffMembers=$("#team_members").find("option[value='"+MainStaff+"']").val();
                $("#team_members").find("option").prop('disabled',false);
                $("#team_members").find("option").attr('selected',false);
                $("#team_members").select2({
                    placeholder:"{{ trans('salesteam.staff_members') }}",
                    theme: 'bootstrap'
                });
                if(MainStaff=staffMembers){
                    $("#team_members").find("option[value='"+MainStaff+"']").prop('disabled',true);
                }
            });
        }
        MainStaffChange();
        $("#team_members").select2({
            placeholder:"{{ trans('salesteam.staff_members') }}",
            theme: 'bootstrap'
        }).find("option:first").attr({
            selected:false
        });
        var MainStaff=$("#team_leader").select2("val");
        var staffMembers=$("#team_members").find("option[value='"+MainStaff+"']").val();
        if(MainStaff=staffMembers){
            $("#team_members").find("option[value='"+MainStaff+"']").prop('disabled',true);
        }

    });
</script>
<script src="{{ URL::asset('assets/libs/select2/js/select2.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/bootstrap-timepicker/js/bootstrap-timepicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/datepicker/datepicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/js/pages/form-advanced.init.js')}}"></script> 