@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
@endsection
@section('content')

    @php $listCustomerTitle=null; $listCustomerNumber=null; $listCustomerAllNumber=null; $listCustomerNumberReply=null; $listCustomerColor=null; @endphp

    @if($list_customer_group)  
        @foreach($list_customer_group as $values)
            @php 
                $listCustomerTitle[]='"'.$values["name"].'"';
                $listCustomerNumber[]=$values["leads"];
                $listCustomerNumberReply[]=$values["leadsReply"];
                $listCustomerAllNumber[]=$values["leadsAll"];

                $listCustomerColor[]='"'.$values["color"].'"';
            @endphp
        @endforeach
    @endif 
    @php $listLeadTitle=null; $listLeadNumber=null; $listLeadNumberAll=null; $listLeadNumberReply=null; $listLeadColor=null; @endphp

    @if($list_lead_group)  
        @foreach($list_lead_group as $values)
            @php 
                $listLeadTitle[]='"'.$values["name"].'"';
                $listLeadNumber[]=$values["leads"];
                $listLeadNumberReply[]=$values["leadsReply"];
                $listLeadNumberAll[]=$values["leadsAll"];
                
                $listLeadColor[]='"'.$values["color"].'"';
            @endphp
        @endforeach
    @endif
    @php $listLeadTitleDay=null; $listLeadNumberDay=null; $listLeadsAllNumberDay=null; $listLeadColorDay=null; @endphp
    @if($list_lead_group_new)  
         
        @for($i=0;$i<count($list_lead_group_new);$i++)
            @php 
                $values=$list_lead_group_new[$i];
                $listLeadTitleDay[]='"'.$values["name"].'"';
                $listLeadNumberDay[]=$values["leads"];
                $listLeadsAllNumberDay[]=$values["leadsAll"];
                $listLeadColorDay[]='"'.$values["color"].'"';
            @endphp
        @endfor
    @endif

    
    @php $listClientTitle=null; $listOrderNumber=null; $listNewOrderNumber=null; $listOrderAllNumber=null; @endphp

    @if($list_client_group_order)  
        @foreach($list_client_group_order as $values)
            @php 
                $listClientTitle[]='"'.$values["name"].'"';
                $listOrderNumber[]=$values["orderOld"];
                $listNewOrderNumber[]=$values["orderNew"];
                $listOrderAllNumber[]=$values["orderAll"];
            @endphp
        @endforeach
    @endif
    @php $daysListData=null;  $dataDate=$daysList; @endphp
    @if($daysList)  
        @for($i=0;$i<count($dataDate);$i++)
            @php 
             $daysListData[]='"'.$dataDate[$i].'"';
            @endphp
        @endfor
    @endif

    <div class="row mar-20">
        <h3>{{trans('staff.user_report')}}: <strong style="color:red">{{$staffDetail->first_name}} {{$staffDetail->last_name}}</strong></h3>
    </div>
    <div class="clearfix">
        {!! Form::open(['url' => 'report/summary', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('daterange',  trans('lead.daterange'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        <select class="listselect2 form-control" name="daterange" id="daterange">  
                             <option value="">Tùy chọn</option>
                             <option value="1" @if(isset($daterange) && $daterange==1) selected @endif>Hôm nay</option>
                             <option value="2"  @if(isset($daterange) && $daterange==2) selected @endif>Hôm qua</option>
                             <option value="7" @if(isset($daterange) && $daterange==7) selected @endif>Trong 7 ngày</option>
                             <option value="thismonth" @if(isset($daterange) && $daterange=='thismonth') selected @endif>Tháng này</option>
                             <option value="lastmonth" @if(isset($daterange) && $daterange=='lastmonth') selected @endif>Tháng trước</option>
                        </select>     
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'sales_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('sales_id', ':message') }}</span>
                    </div>
                </div>
            </div>


            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
            
        </div>
        {!! Form::close() !!}
    </div>
    <div class="row mar-20">
            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-navy">
                    <div class="inner">
                        <h3>{{number_format($totalRevenus)}}</h3>
                        <p>Doanh thu</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="/sales" target="_blank" class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3>{{$totalOrder}}</h3>
                        <p>Tổng đơn hàng</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="/sales" target="_blank"  class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 >{{$totalNewCustomer}}</h3>
                        <p>Tổng KH mới(Mua hàng)</p> 
                    </div>
                    <div class="icon">
                        <i class="ion ion-android-contacts"></i>
                    </div>
                    <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=44&type_client=new" class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 >{{$totalOldCustomer}}</h3>
                        <p>Tổng KH cũ(Mua hàng)</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-android-contacts"></i>
                    </div>
                    <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=44&type_client=old" class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="row mar-20">
            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-aqua"> 
                    <div class="inner">
                        <h3>{{$leadToClientBuy}}</h3>
                        <p>Lead->KH(Đã mua)</p>
                    </div>
                    <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                    </div>
                    <a href="/lead/home?starting_date={{$date_select}}&group_id=44" class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-aqua"> 
                    <div class="inner">
                        <h3>{{$leadToClient}}</h3>
                        <p>Hotlead</p>
                    </div>
                    <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                    </div>
                    <a href="/lead/home?starting_date={{$date_select}}&group_id=44" class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6 boxreport">
                <!-- small box -->
                <div class="small-box bg-aqua"> 
                    <div class="inner">
                        <h3>{{$totalVistor}}</h3>
                        <p>Khách ghé cửa hàng</p>
                    </div>
                    <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                    </div>
                    <a href="/customer/customervistor?start_date={{date('Y-m-d',strtotime($starting_date))}}&end_date={{date('Y-m-d',strtotime($ending_date))}}" target="_blank" class="small-box-footer">
                        View Detail<i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
    </div>
    <!-- Vùng lỗi -->
    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{$totalLeadNotCare}}</h3>
                    <p>Tổng Lead còn lại</p>
                </div>
            <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div> 
                <a href="/lead/home?starting_date={{$date_select}}&daterange={{$daterange}}&group_id=45&tuongtac=3" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{$totalAssign}}</h3>
                    <p>Tổng số lần chuyển Lead</p>
                </div>
                <div class="icon">
                    <i class="ion"></i>
                </div>
                <a href="/lead?sales_id={{$salesSearch}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalLeadAccept}}</h3>
                    <p>Lead đã tiếp nhận</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <!-- 
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>-->
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalLeadBung}}</h3>
                    <p>Lead tiếp nhận bi bung</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <!-- 
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>-->
            </div>
        </div>
    </div>
    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalClientBung}}</h3>
                    <p>KH cũ bị bung</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalReportTags}}</h3>
                    <p>Số lượng gắn sai thẻ</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date={{$date_select}}&report_tag=1" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 >{{round($timeProcessLead)}}</h3>
                    <p>TG xử lý lead (Giây)</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            @php // $total=$totalCustomer+$totalLead; @endphp
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{round($timeAcceptLead)}}</h3>
                    <p>TG tiếp nhận Lead (Giây)</p>
                </div>
                <div class="icon">
                    <i class="ion"></i>
                </div>
                
            </div>
        </div>
    </div>
    <!-- Kêt thuc Vung2 lỗi -->
    <!-- Vùng nuôi dưong --> 
    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalLead}}</h3>
                    <p>Đang chăm sóc</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date={{$date_select}}&daterange={{$daterange}}&group_id=45&tuongtac=1" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 >{{$totalLeadOld}}</h3>
                    <p>Tổng lead cũ</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date={{$date_select}}&daterange={{$daterange}}&group_id=45&type_client=old" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{$totalLeadReply}}</h3>TT lead cũ có phản hồi</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-call"></i>
                </div>
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45&have_reply=1" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalMessengerNew}}</h3>
                    <p>Tin nhắn Lead mới</p>
                </div>
                <div class="icon">
                <i class="ion ion-ios7-chatbubble-outline"></i>
                </div>
                <a href="/lead/home?starting_date={{$date_select}}&&mess=0" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalMessengerOld}}</h3>
                    <p>Tin nhắn Lead cũ</p>
                </div>
                <div class="icon">
                <i class="ion ion-ios7-chatbubble-outline"></i>
                </div> 
                <a href="/lead/home?starting_date={{$date_select}}&mess=1" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="row mar-20">
        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>Chỉ số tương tác 2 chiều Lead cũ</h4>
                <div id='chart_tuong_tac_2_chieu'></div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>Tương tác lead cũ</h4>
                <div id='chart_opp_lead'></div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="box1 opport">
                <h4>Tương tác Khách hàng cũ</h4>
                <div id='chart_opp_customer'></div>
            </div>

        </div>
        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>Tỷ lệ mua hàng</h4>
                <div id='chart_order_client'></div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>Chỉ số nhóm lead mới nhận từ Ca</h4>
                <div id='chart_new_lead_day'></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript" src="{{ asset('js/jquery-jvectormap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.v3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/c3.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/countUp.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
    <script>
        $(document).ready(function(){
            setTimeout(function () {
            $('.icheckgreen').iCheck({
                checkboxClass: 'icheckbox_minimal-green',
                radioClass: 'iradio_minimal-green'
            });
            },300);
            var deleteButton = " <a href='' class='tododelete redcolor'><span class='fa fa-trash'></span></a>";
            var editButton = "<a href='' class='todoedit'><span class='fa fa-pencil'></span>|</a>";
            var checkBox = "<input type='checkbox' class='striked icheckgreen' autocomplete='off' />";
            var checkBoxChecked = "<input type='checkbox' checked class='striked icheckgreen' autocomplete='off' />";
            var twoButtons = "<div class='col-md-3 col-sm-3 col-xs-3  pull-right showbtns todoitembtns'>" + editButton + deleteButton + "</div>";
            var oneButtons = "<div class='col-md-3 col-sm-3 col-xs-3  pull-right showbtns todoitembtns'>" + deleteButton + "</div>";

            $.ajax({
                type: "GET",
                url: baseUrl+"/staff/{{$staffDetail->id}}/task",
                success: function (result) {
                    $.each(result, function (i, item) {
                        $('.list_of_items').append("<div class='todolist_list showactions list1' id='"+ item.id +"'>" +
                            "<div class='col-md-12 col-sm-12 col-xs-12 nopadmar custom_textbox1'><div class='col-md-12 col-sm-12 col-xs-12'> "+ item.id+"&nbsp;&nbsp;" + item.description + "&nbsp;&nbsp;" + item.created_at + "</div></div>");
                    });
                }
            });

        });
        /*c3 line chart*/
        $(function () {
            @if(isset($listLeadTitle) && count($listLeadTitle)>0)
            var chart_lead = c3.generate({
                bindto: '#chart_opp_lead',
                data: {
                    x : 'x',
                    columns: [
                       
                        ['x', @php echo html_entity_decode(implode(",",$listLeadTitle)); @endphp],
                        ['Tích lũy', {{implode(",",$listLeadNumberAll)}}],
                        ['Đã gởi tin', {{implode(",",$listLeadNumber)}}],
                        ['Lead tương tác lại', {{implode(",",$listLeadNumberReply)}}]
                    ],
                    types: {
                        'Nhóm lead đang chăm sóc': 'bar',
                    }

                },
                color: {
                    @if($list_lead_group)
                        @foreach($list_lead_group as $values)
                            '{{$values["name"]}}':'@php echo '"'.$values["color"].'"'; @endphp',
                        @endforeach
                    @endif
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });
            setTimeout(function () {
                chart_lead.resize();
            }, 2000);
            @endif
            @if(isset($daysListData) && count($daysListData)>0)
            var chart_lead_tuong_tac = c3.generate({
                bindto: '#chart_tuong_tac_2_chieu',
                data: {
                    x : 'x',
                    columns: [
                        ['x', {!!implode(",",$daysListData)!!}],
                        ['Tương tác', {{implode(",",$oldLeadOldDays)}}],
                        ['Khách reply', {{implode(",",$newLeadReplyDays)}}]
                    ],
                    types: {
                        'Tỷ lệ chăm sóc lead': 'bar',
                        
                    }

                },
                axis: {
                    x: {
                        type: 'timeseries',
                        tick: {
                                format: "%d-%m"
                        }
                    },
                }
            });
            setTimeout(function () {
                chart_lead_tuong_tac.resize();
            }, 2000);
            @endif
            @if(isset($listClientTitle) && count($listClientTitle)>0)
            var chart_order = c3.generate({
                bindto: '#chart_order_client',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listClientTitle)); @endphp],
                        ['Tích lũy', {{implode(",",$listOrderAllNumber)}}],
                        ['KH mới', {{implode(",",$listNewOrderNumber)}}],
                        ['KH cũ', {{implode(",",$listOrderNumber)}}],
                    ],
                    types: {
                        'Tỷ lệ mua hàng': 'bar',
                    }

                },
                color: {
                    @if($list_client_group_order)
                        @foreach($list_client_group_order as $values)
                            '{{$values["name"]}}':'@php echo '"#37b71d"'; @endphp',
                        @endforeach
                    @endif
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });
            setTimeout(function () {
                chart_order.resize();
            }, 2000);
            @endif
            @if(isset($listLeadTitleDay) && count($listLeadTitleDay)>0)
            var chart_new_lead_day = c3.generate({
                bindto: '#chart_new_lead_day',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listLeadTitleDay)); @endphp],
                        ['Tích lũy', {{implode(",",$listLeadsAllNumberDay)}}],
                        ['Lead mới', {{implode(",",$listLeadNumberDay)}}],
                    ], 
                    types: {
                        'Số lượng các nhóm lead trong ca': 'bar',
                    }

                },
                color: {
                    @if($list_lead_group_new)
                        @foreach($list_lead_group as $values)
                            '{{$values["name"]}}':'@php echo '"'.$values["color"].'"'; @endphp',
                        @endforeach
                    @endif
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });
            setTimeout(function () {
                chart_new_lead_day.resize();
            }, 2000);
            @endif
            
            @if(isset($listCustomerTitle) && count($listCustomerTitle)>0)
            var chart_opp_customer = c3.generate({
                bindto: '#chart_opp_customer',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listCustomerTitle)); @endphp],
                        ['Tích lũy', {{implode(",",$listCustomerAllNumber)}}],
                        ['Tương tác', {{implode(",",$listCustomerNumber)}}],
                        ['Khách tương tác lại', {{implode(",",$listCustomerNumberReply)}}]
                    ],
                    types: {
                        'Nhóm khách hàng đang chăm sóc': 'bar',
                    }

                },
                color: {
                    @if($list_customer_group)
                        @foreach($list_customer_group as $values)
                            '{{$values["name"]}}':'@php echo '"'.$values["color"].'"'; @endphp',
                        @endforeach
                    @endif
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });
            setTimeout(function () {
                chart_opp_customer.resize();
            }, 2000);
            @endif
            function formatMonth(d) {
            }
            // c3 chart end
            /*dashboard countup*/
            var useOnComplete = false,
                useEasing = false,
                useGrouping = false,
                options = {
                    useEasing: useEasing, // toggle easing
                    useGrouping: useGrouping, // 1,000,000 vs 1000000
                    separator: ',', // character to use as a separator
                    decimal: '.' // character to use as a decimal
                };

            
            /*countup end*/
            $('.task-body1').slimscroll({
                height: '363px',
                size: '5px',
                opacity: 0.2
            });

        });
    </script>
    <script src="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.js')}}"></script>
<!-- Init js-->
<script src="{{ URL::asset('assets/js/pages/table-responsive.init.js')}}"></script> 
<!-- Calendar init -->
<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/jquery.repeater/jquery.repeater.min.js')}}"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script src="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.css')}}" />

    {{-- Scripts --}}
@endsection