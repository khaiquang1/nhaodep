@extends('layout.main') @section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div> 
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
@endif

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
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{trans('file.Choose Your Date')}}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field-search  form-control" value="" />
                                    <input type="hidden" id="start_date" name="start_date" value="{{$dataSearch["start_date"]}}" />
                                    <input type="hidden" id="end_date" name="end_date" value="{{$dataSearch["end_date"]}}" />

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
                    <div class="col-md-6"></div>

                </div>
            </div>

        </div>
    </div>
    {!! Form::close() !!}

    <div class="table-responsive">
        <table id="attendance-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.date')}}</th>
                    <th>{{trans('file.Employee')}}</th>
                    <th>{{trans('file.CheckIn')}}</th>
                    <th>{{trans('file.CheckOut')}}</th>
                    <th>{{trans('file.Status')}}</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_attendance_all as $key=>$attendance)
 
                <tr data-id="{{$attendance->id}}">
                    <td>{{$key}}</td>
                    <td>{{ date($general_setting->date_format, strtotime($attendance->date)) }}</td>
                    <td>{{ $attendance->fullname }}</td>
                    <td>{{date("H:i:s",strtotime($attendance->checkin))}}</td>
                    <td>{{date("H:i:s",strtotime($attendance->checkout))}}</td>
                    @if($attendance->status && $attendance->status==1)
                        <td><div class="badge badge-success">Ok</div></td>
                    @elseif($attendance->status && $attendance->status==2)
                        <td><div class="badge badge-danger">Trể</div></td>
                    @else
                        <td><div class="badge badge-danger">Chưa xác định</div></td>
                    @endif
                    <td>
                        <div class="btn-group">
                        @if(in_array("attendance", $all_permission))
                        <button type="button" class="btn btn-link edit-btn" data-id="{{$attendance->id}}" data-status="{{$attendance->status}}" data-note="{{$attendance->note}}" data-clicked=false data-toggle="modal" data-target="#edit-attendence"><i class="dripicons-document-edit"></i> {{trans("file.edit")}}</button>
                        @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>


<div id="edit-attendence" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">Cập nhật nghỉ phép</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'attendance.updatelatter', 'method' => 'post', 'files' => true, 'class' => 'payment-form' ]) !!}
                    <div class="row">
                        <input type="hidden" name="balance">
                        <div class="form-group col-md-12">
                            <label> Tình trạng</label>
                            <select class="form-control selectpicker" name="status" id="status">
                                <option value="0">--Chọn--</option>
                                <option value="1">Đúng giờ</option>
                                <option value="2">Đi muộn</option>
                                <option value="3">Đi muộn có xin phép</option>
                            </select>
                        </div>
                        <div class="form-group  col-md-12">
                            <label>Ghi chú</label>
                            <textarea rows="3" class="form-control" name="note" id="note"></textarea>
                        </div>

                        <input type="hidden" name="id" id="attendence_id">
                        <div class="form-group col-md-12">
                                 <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                        </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(function() {

        $("#attendance-table").on("click", ".edit-btn", function(event) {
            var id = $(this).data('id').toString();
            var status = $(this).data('status').toString();
            var note = $(this).data('note').toString();
            $('input[id="attendence_id"]').val(id);
            console.log(status);
            $('select[id="status"]').val(status);
            $('textarea[id="note"]').val(note);
            $('.selectpicker').selectpicker('refresh');
        });


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
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
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

@endsection