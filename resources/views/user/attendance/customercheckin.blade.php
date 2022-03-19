@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')

<section>
    <div class="clear">&nbsp;</div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                {!! Form::open(['route' => 'customer.customervistor', 'method' => 'get']) !!}
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>Chọn ngày</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field-search  form-control" value="" />
                                    <input type="hidden" id="start_date" name="start_date" value="{{$dataSearch["start_date"]}}" />
                                    <input type="hidden" id="end_date" name="end_date" value="{{$dataSearch["end_date"]}}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>Từ khóa</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Nhập từ khóa" value="@if(isset($dataSearch["keyword"])){{$dataSearch["keyword"]}}@endif" />
                                       
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row">
                             <label class="d-tc mt-2"><strong>Xem danh sách</strong> &nbsp;</label>
                             <div class="input-group">
                            <input type="submit" value="Xem danh sách" class="btn btn-secondary buttons-pdf buttons-html5"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        
                        <div class="form-group row">
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="table-responsive">
    {!! Form::open(['route' => 'customer.customersyndata', 'method' => 'POST']) !!}
        <table id="customer-table"  class="table sale-list nowrap"  style="width:100%!important">
            <thead> 
                <tr>
                    <th class="not-exported"></th>
                    <th>Hình</th>
                    <th>Tên</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Địa điểm</th>
                    <th>Thiết bị</th>
                    <th class="not-exported">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0;?>
                <tr>
                <td colspan="8"><input type="submit" name="syndata" value="Đồng bộ dữ liệu" class="btn btn-secondary buttons-print buttons-html5"/> <input type="submit" name="staffdata" value="Là nhân viên" class="btn btn-secondary buttons-print buttons-html5"/> 
                <input type="submit" name="deletedata" value="Xóa dữ liệu trùng" class="btn btn-secondary buttons-pdf buttons-html5"/></td>

                </tr>
                @foreach($lims_customer_all as $key=>$customer)
                <?php $i++;?>
                <tr data-id="{{$customer->id}}">
                    <td><input type="checkbox" name="keysyn[]" value="{{$customer->person_id}}" /></td>
                    <td>@if($customer->images_link!="")<a href="/attendance/customer-offline-log?person_id={{$customer->person_id}}"><img src="{{$customer->images_link}}" width="100px"></a> @endif</td>
                    <td><a href="/attendance/customer-offline-log?person_id={{$customer->person_id}}">@if($customer->fullname!="") {{ $customer->fullname}} @else Chưa xác định @endif</a></td>
                    <td>{{ $customer->check_in}}</td>
                    <td>{{ $customer->check_out}}</td>
                    <td> {{ $customer->placeID}}</td>
                    <td>{{ $customer->deviceID}}</td>
                    <td> 
                    <div class="btn-group">
                        <button type="button" class="btn btn-link add-btn" data-person_id="{{$customer->person_id}}" data-id="{{$customer->id}}" data-images="{{$customer->images_link}}" data-clicked=false data-toggle="modal" data-target="#add_customer"><i class="dripicons-document-add"></i> Thêm vào nhân viên</button></div>
                        <!-- 
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans('file.action')}}
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="/customer/customer-offline-log?person_id={{$customer->person_id}}">Xem lịch sử</a>
                                </li>
                                <li>
                                    
                                </li>
                            </ul>
                        </div> -->
                    </td>
                </tr>
                @endforeach
                
            </tbody>
            
        </table>
        {!! Form::close() !!}
        <div class="row">
            <div class="col-sm-12">
                    @include('layouts.paging', ['paginator' => $lims_customer_all])
            </div>
        </div>
    </div>
</section>

<div id="add_customer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">Cập nhật hình cho KH</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'customer.customerupdateperson', 'method' => 'post']) !!}
                    <div class="form-group">
                        <label><strong>Hình</strong></label>
                        <div id="images"></div>
                    </div>
                    <div class="form-group">
                        <label><strong>Chọn KH</strong></label>
                            <select required name="customer_id" id="customer_id" class="selectpicker form-control customer_list" data-live-search="true" data-live-search-style="begins" title="Select nhân viên...">
                                @foreach($lims_customer_list as $customer)
                                <option value="{{$customer->id}}">{{$customer->first_name}} {{$customer->last_name}}</option>
                                @endforeach
                            </select>
                    </div>
                    <input type="hidden" id="person_id" name="person_id">
                    <input type="hidden" id="person_image" name="person_image"> 
                    <button type="submit" class="btn btn-primary">{{trans('file.update')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $("ul#people").siblings('a').attr('aria-expanded','true');
    $("ul#people").addClass("show");
    $("ul#people #customer-list-menu").addClass("active");
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

    function confirmDelete() {
      if (confirm("Are you sure want to delete?")) {
          return true;
      }
      return false;
    }

    var customer_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;
    var all_permission = [];
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $(".deposit").on("click", function() {
        var id = $(this).data('id').toString();
        $("#depositModal input[name='customer_id']").val(id);
  });
    $("#customer-table").on("click", ".add-btn", function(event) {
        var id = $(this).data('id').toString();
        var person_id = $(this).data('person_id').toString();
        var images = $(this).data('images').toString();
        $('input[id="person_id"]').val(person_id);
        $('input[id="person_image"]').val(images);
        $('#images').html("<img src='"+images+"' width='100px' />");
        $('.selectpicker').selectpicker('refresh');
    });

    var table = $('#customer-table').DataTable( {
        "order":  [[ 2, "desc" ]],
        "scrollY": 600,
        "scrollX": true,
        "paging": false,
        "search": false,
        "bFilter":false,
        "scroller": {
            loadingIndicator: true
        },
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
                "searchable": false,
                'targets': [0, 9]
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
                }
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
                    rows: ':visible'
                },
            },
            {
                extend: 'csv',
                text: '{{trans("file.CSV")}}',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'print',
                text: '{{trans("file.Print")}}',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                text: '{{trans("file.delete")}}',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        customer_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                customer_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(customer_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'customer/deletebyselection',
                                data:{
                                    customerIdArray: customer_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!customer_id.length)
                            alert('No customer is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '{{trans("file.Column visibility")}}',
                columns: ':gt(0)'
            },
            
        ],
    } );

  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  if(all_permission.indexOf("customers-delete") == -1)
        $('.buttons-delete').addClass('d-none');

    $("#export").on("click", function(e){
        e.preventDefault();
        var customer = [];
        $(':checkbox:checked').each(function(i){
          customer[i] = $(this).val();
        });
        $.ajax({
           type:'POST',
           url:'/exportcustomer',
           data:{
                customerArray: customer
            },
           success:function(data){
             alert('Exported to CSV file successfully! Click Ok to download file');
             window.location.href = data;
           }
        });
    });

    $('.selectpicker').selectpicker().filter('.customer_list').ajaxSelectPicker(options);


</script>
@stop