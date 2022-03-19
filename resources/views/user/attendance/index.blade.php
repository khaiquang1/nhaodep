@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')

<section>
    <!-- 
    <div class="container-fluid">
        <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i> {{trans('file.Add Attendance')}} </button>
    </div> -->

    <div class="clear">&nbsp;</div>
    {!! Form::open(['route' => 'attendance.index', 'method' => 'get']) !!}
    <div class="container-fluid">
        <div class="row boxfilter">
            <div class="col-md-12">
                <div class="row ">

                    <div class="col-md-4">
                        <div class="form-group required {{ $errors->has('start_date') ? 'has-error' : '' }}">
                            {!! Form::label('start_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::text('start_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2">Chi nhánh</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <select id="warehouse_id" name="warehouse_id" class="form-control" title="Chọn chi nhánh...">
                                        <option value="0">Tất cả</option>
                                      
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2">Nhân viên</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <select name="user_id" id="user_id" class="form-control" title="Tất cả..." data-live-search="true" data-live-search-style="begins">
                                        <option value="">Tất cả</option>
                                        @foreach($lims_employee_list as $users)
                                            <option value="{{$users->id}}" @if(isset($dataSearch) && $dataSearch["user_id"]==$users->id) selected @endif>{{$users->full_name}}</option>
                                        @endforeach 
                                        </select>
                                </div> 
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2" style="margin: 0 auto;padding: 0;">
                        <label class="d-tc mt-2">Tìm kiếm</label>
                        <div>
                            <input type="submit" value="Tìm kiếm" class="btn btn-secondary buttons-pdf buttons-html5"/>
                        </div>

                        
                    </div>

                </div>
            </div>

        </div>
    </div>
    {!! Form::close() !!}

    <div class="table-responsive">
        <table id="attendance-table" class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Hình</th>
                    <th>Nhân viên</th>
                    <th>Số ngày checkin</th>
                    <th>Số ngày trể</th>
                    <th>Tình trạng</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_attendance_all as $key=>$attendance)
                <?php 
                    $totalLater = DB::table('attendances')->where('user_id',$attendance->user_id)->where('status',2)->whereDate('date','>=', $dataSearch["start_date"])->whereDate('date','<=', $dataSearch["end_date"])->count();
                ?>
                <tr data-id="{{$attendance->id}}">
                    <td>{{$key}}</td>
                    <td><a href="/attendance/detail?id={{$attendance->user_id}}&start_date={{$dataSearch["start_date"]}}&end_date={{$dataSearch["end_date"]}}" target="blank"><img src="{{$attendance->user_avatar}}" heigh="100px"></a></td>

                    <td><a href="/attendance/detail?id={{$attendance->user_id}}&start_date={{$dataSearch["start_date"]}}&end_date={{$dataSearch["end_date"]}}" target="blank">{{ $attendance->fullname }}</a></td>
                    <td class="number">{{ $attendance->total_check_in }}</td>
                    <td class="number">{{$totalLater}}</td>
                    @if($totalLater<=3)
                        <td><div class="badge badge-success">Ok</div></td>
                    @else
                        <td><div class="badge badge-danger">Cảnh báo</div></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Attendance')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                {!! Form::open(['route' => 'attendance.store', 'method' => 'post', 'files' => true]) !!}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Employee')}} *</label>
                        <select class="form-control selectpicker" name="employee_id[]" required data-live-search="true" data-live-search-style="begins" title="Select Employee..." multiple>
                            @foreach($lims_employee_list as $employee)
                            <option value="{{$employee->id}}">{{$employee->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.date')}} *</label>
                        <input type="text" name="date" class="form-control date" value="{{date('Y-m-d')}}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.CheckIn')}} *</label>
                        <input type="text" id="checkin" name="checkin" class="form-control" value="{{$lims_hrm_setting_data->checkin}}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.CheckOut')}} *</label>
                        <input type="text" id="checkout" name="checkout" class="form-control" value="{{$lims_hrm_setting_data->checkout}}" required>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{trans('file.Note')}}</label>
                        <textarea name="note" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
      $('.daterangepicker-field-search').daterangepicker({
        opens: 'left',
        timePicker: false,
        startDate:"{{$dateStartSearch}}",
        endDate: "{{$dateEndSearch}}",
        ranges: {
           'Hôm nay': [moment(), moment()],
           'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 ngày trước': [moment().subtract(6, 'days'), moment()],
           '30 ngày trước': [moment().subtract(29, 'days'), moment()],
           'Tháng này': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
      }, function(start, end, label) {
        $('input[name="start_date"]').val(start.format('YYYY-MM-DD'));
        $('input[name="end_date"]').val(end.format('YYYY-MM-DD'));
      });
    });
	$("ul#hrm").siblings('a').attr('aria-expanded','true');
    $("ul#hrm").addClass("show");
    $("ul#hrm #attendance-menu").addClass("active");

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    var attendance_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
	var date = $('.date');
    date.datepicker({
     format: "dd-mm-yyyy",
     autoclose: true,
     todayHighlight: true
     });

    $('#checkin, #checkout').timepicker({
    	'step': 15,
    });

    var table = $('#attendance-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 7]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '{{trans("file.PDF")}}',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                }
            },
            {
                extend: 'csv',
                text: '{{trans("file.CSV")}}',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                },
            },
            {
                extend: 'print',
                text: '{{trans("file.Print")}}',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                },
            },
            {
                extend: 'colvis',
                text: '{{trans("file.Column visibility")}}',
                columns: ':gt(0)'
            },
        ],
    } );
</script>

@stop