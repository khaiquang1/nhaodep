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
                {!! Form::open(['route' => 'customer.index', 'method' => 'get']) !!}
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
                    <div class="col-md-2">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>Từ khóa</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Nhập từ khóa" value="@if(isset($dataSearch["keyword"])){{$dataSearch["keyword"]}}@endif" />
                                       
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group row">
                             <label class="d-tc mt-2"><strong>Lọc KH</strong> &nbsp;</label>
                             <div class="input-group">
                            <input type="submit" value="Lọc KH" class="btn btn-secondary buttons-pdf buttons-html5"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        
                        <div class="form-group row">
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table id="customer-table"  class="table sale-list nowrap"  style="width:100%!important">
            <thead> 
                <tr>
                    <th class="not-exported"></th>
                    <th>Ngày</th>
                    <th>Hình</th>
                    <th>Tên</th>
                    <th>Vị trí</th>
                    <th>Thiết bị</th>
                    <th class="not-exported">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0;?>
                @foreach($lims_customer_all as $key=>$customer)
                <?php $i++;?>
                <tr data-id="{{$customer->id}}">
                    <td>{{$key}}</td>
                    <td> {{ $customer->date}}</td>
                    <td>@if($customer->detected_image_url!="")<img src="{{$customer->detected_image_url}}" width="100px"> @endif</td>
                    <td> {{ $customer->personName}}</td>
                    <td> {{ $customer->placeID}}</td>
                    <td>{{ $customer->deviceName}}</td>
                    <td> 
                        <button type="button" class="btn btn-link add-btn" data-person_id="{{$customer->person_id}}" data-id="{{$customer->id}}" data-images="{{$customer->detected_image_url}}" data-clicked=false data-toggle="modal" data-target="#add_customer"><i class="dripicons-document-add"></i> Thêm nhân viên</button>
                                <!--
                                {{ Form::open(['route' => ['customer.destroy', $customer->id], 'method' => 'DELETE'] ) }}
                                    <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> Xóa</button>
                                {{ Form::close() }} -->
                    </td>
                </tr>
                @endforeach
               
            </tbody>
            
        </table>
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
                        <label><strong>Chọn Nhân viên</strong></label>
                            <select required name="customer_id" id="customer_id" class="selectpicker form-control customer_list" data-live-search="true" data-live-search-style="begins" title="Select customer...">
                                <?php $deposit = []; ?>
                                @foreach($lims_customer_list as $customer)
                                <option value="{{$customer->id}}">{{$customer->first_name}} {{$customer->last_name}}</option>
                                @endforeach
                            </select>
                    </div>
                    <input type="hidden" id="person_id" name="person_id">
                    <input type="hidden" id="person_image" name="person_image"> 
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
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
    var user_verified = 1
    var all_permission = [];
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#customer-table").on("click", ".add-btn", function(event) {
        var id = $(this).data('id').toString();
        var person_id = $(this).data('person_id').toString();
        var images = $(this).data('images').toString();
        $('input[id="person_id"]').val(person_id);
        $('input[id="person_image"]').val(images);
        $('#images').html("<img src='"+images+"' width='100px' />");
    });


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
</script>
@stop