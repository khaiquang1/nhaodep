@extends('layouts.user')
@section('title')
    {{trans('dashboard.dashboard')}}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/jquery-jvectormap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
@section('content')

    @if($list_customer_group)  
        @php $listCustomerTitle=null; $listCustomerNumber=null; $listCustomerColor=null; @endphp
        @foreach($list_customer_group as $values)
            @php 
                $listCustomerTitle[]='"'.$values["name"].'"';
                $listCustomerNumber[]=number_format($values["leads"]);
                $listCustomerColor[]='"'.$values["color"].'"';
            @endphp
        @endforeach
    @endif 

    @if($list_lead_group)  
        @php $listLeadTitle=null; $listLeadNumber=null; $listLeadColor=null; @endphp
        @foreach($list_lead_group as $values)
            @php 
                $listLeadTitle[]='"'.$values["name"].'"';
                $listLeadNumber[]=number_format($values["leads"]);
                $listLeadColor[]='"'.$values["color"].'"';
            @endphp
        @endforeach
    @endif

    @if($list_lead_group_new)  
        @php $listLeadTitleDay=null; $listLeadNumberDay=null; $listLeadColorDay=null; @endphp
        @foreach($list_lead_group_new as $values)
            @php 
                $listLeadTitleDay[]='"'.$values["name"].'"';
                $listLeadNumberDay[]=number_format($values["leads"]);
                $listLeadColorDay[]='"'.$values["color"].'"';
            @endphp
        @endforeach
    @endif

    

    @if($list_client_group_order)  
        @php $listClientTitle=null; $listOrderNumber=null; @endphp
        @foreach($list_client_group_order as $values)
            @php 
                $listClientTitle[]='"'.$values["name"].'"';
                $listOrderNumber[]=number_format($values["order"]);
            @endphp
        @endforeach
    @endif

    @if($daysList)  
        @php $daysListData=null; 
        $dataDate=$daysList;
        @endphp
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
                             <option value="">T???t c???</option>
                             <option value="1" @if(isset($daterange) && $daterange==1) selected @endif>H??m nay</option>
                             <option value="2"  @if(isset($daterange) && $daterange==2) selected @endif>H??m qua</option>
                             <option value="7" @if(isset($daterange) && $daterange==7) selected @endif>Trong 7 ng??y</option>
                             <option value="thismonth" @if(isset($daterange) && $daterange=='thismonth') selected @endif>Th??ng n??y</option>
                             <option value="lastmonth" @if(isset($daterange) && $daterange=='lastmonth') selected @endif>Th??ng tr?????c</option>
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
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{$totalLead}}</h3>
                    <p>T???ng lead ??ang s??c</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{$totalLeadNotCare}}</h3>
                    <p>Lead ch??a ch??m s??c</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            @php // $total=$totalCustomer+$totalLead; @endphp
            <!--if($totalCustomer>0)number_format(($totalCustomer/($total))*100)% else 0 endif -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{$totalAssign}}</h3>
                    <p>T???ng s??? l???n xoay, chuy???n Lead</p>
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
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 >{{$totalCustomer}}</h3>
                    <p>T???ng KH</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=44" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
    </div>

    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 >{{$totalLeadOld}}</h3>
                    <p>T???ng lead c??</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{$totalLeadReply}}</h3>TT lead c?? c?? ph???n h???i</p>
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
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>{{$totalOrder}}</h3>
                    <p>T???ng ????n h??ng</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="https://crm.lavendervn.com/sales" target="_blank"  class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-5 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-navy">
                <div class="inner">
                    <h3>{{number_format($totalRevenus)}}</h3>
                    <p>Doanh thu</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="https://crm.lavendervn.com/sales" target="_blank" class="small-box-footer">
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
                    <h3>{{$totalLeadBung}}</h3>
                    <p>Lead ti???p nh???n bi bung</p>
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
                    <h3>{{$totalClientBung}}</h3>
                    <p>KH c?? b??? bung</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <!-- <a href="/lead/home?starting_date=&daterange={{$daterange}}&group_id=45" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a> -->
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            @php // $total=$totalCustomer+$totalLead; @endphp
            <!--if($totalCustomer>0)number_format(($totalCustomer/($total))*100)% else 0 endif -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{$timeAcceptLead}}</h3>
                    <p>TG ti???p nh???n Lead</p>
                </div>
                <div class="icon">
                    <i class="ion"></i>
                </div>
                
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 >{{$timeProcessLead}}</h3>
                    <p>TG x??? l?? lead</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
            </div>
        </div>
        
    </div>

    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua"> 
                <div class="inner">
                    <h3>{{$leadToClient}}</h3>
                    <p>S??? l?????ng Lead chuy???n sang KH</p>
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
                    <h3>{{$totalReportTags}}</h3>
                    <p>S??? l?????ng g???n sai th???</p>
                </div>
                <div class="icon">
                <i class="ion ion-android-contacts"></i>
                </div>
                <a href="/lead/home?starting_date={{$date_select}}&report_tag=1" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    

    
    <div class="row mar-20">
        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>Ch??? s??? t????ng t??c 2 chi???u Lead c??</h4>
                <div id='chart_tuong_tac_2_chieu'></div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>T????ng t??c lead c??</h4>
                <div id='chart_opp_lead'></div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="box1 opport">
                <h4>T????ng t??c Kh??ch h??ng c??</h4>
                <div id='chart_opp_customer'></div>
            </div>

        </div>
        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>T??? l??? mua h??ng KH c??</h4>
                <div id='chart_order_client'></div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="box1 opp-led">
                <h4>Ch??? s??? nh??m lead m???i nh???n t??? Ca</h4>
                <div id='chart_new_lead_day'></div>
            </div>
        </div>
    </div>

    
    <p></p>
@stop

@section('scripts')
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

            var chart_lead = c3.generate({
                bindto: '#chart_opp_lead',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listLeadTitle)); @endphp],
                        ['Lead', {{implode(",",$listLeadNumber)}}],
                    ],
                    types: {
                        'Nh??m lead ??ang ch??m s??c': 'bar',
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


            var chart_lead_tuong_tac = c3.generate({
                bindto: '#chart_tuong_tac_2_chieu',
                data: {
                    x : 'x',
                    columns: [
                        ['x', {!!implode(",",$daysListData)!!}],
                        ['T????ng t??c', {{implode(",",$oldLeadOldDays)}}],
                        ['Kh??ch reply', {{implode(",",$newLeadReplyDays)}}]
                    ],
                    types: {
                        'T??? l??? ch??m s??c lead': 'bar',
                        
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

            
            var chart_order = c3.generate({
                bindto: '#chart_order_client',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listClientTitle)); @endphp],
                        ['Order', {{implode(",",$listOrderNumber)}}],
                    ],
                    types: {
                        'T??? l??? mua h??ng c???a Kh??ch h??ng c??': 'bar',
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

            var chart_new_lead_day = c3.generate({
                bindto: '#chart_new_lead_day',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listLeadTitleDay)); @endphp],
                        ['Lead', {{implode(",",$listLeadNumberDay)}}],
                    ],
                    types: {
                        'S??? l?????ng c??c nh??m lead trong ca': 'bar',
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

            

            var chart_opp_customer = c3.generate({
                bindto: '#chart_opp_customer',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listCustomerTitle)); @endphp],
                        ['Kh??ch h??ng', {{implode(",",$listCustomerNumber)}}],
                    ],
                    types: {
                        'Nh??m kh??ch h??ng ??ang ch??m s??c': 'bar',
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

@stop