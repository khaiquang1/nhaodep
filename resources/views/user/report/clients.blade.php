@extends('layouts.user')
@section('title')
    {{trans('dashboard.dashboard')}}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/jquery-jvectormap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
@section('content')
    <div class="row mar-20">
        <h3>{{trans('staff.user_report')}}: <strong style="color:red">{{$staffDetail->first_name}} {{$staffDetail->last_name}}</strong></h3>
    </div>
    
    <div class="row mar-20">
        <div id="chart_call_date"></div>
    </div>
    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 id="countno2"></h3>
                    <p>{{trans('staff.productsofStaff')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="{{url('product')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 id="leadTotal"></h3>
                    <p>{{trans('staff.leadTotal')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="{{url('lead')}}/home" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 id="totalSuccess"></h3>
                    <p>{{trans('staff.leadSuccess')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="{{url('lead/home?status=6,7')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3 id="totalCall"></h3>
                    <p>{{trans('staff.totalCall')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-call"></i>
                </div>
                <a href="{{url('call')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
    </div>

    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3 id="totalCallSuccess"></h3>
                    <p>{{trans('staff.totalCallComunication')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-call"></i>
                </div>
                <a href="{{url('call')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-navy">
                <div class="inner">
                    <h3 id="rateCall">{{$ctrcall}}%</h3>
                    <p>{{trans('dashboard.ctrconversion')}}</p>
                </div>
                <div class="icon">
                    <i class="ion"></i>
                </div>
                <a href="{{url('lead/home?status=6,7')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
           
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-light-blue">
                <div class="inner">
                    <h3 id="rateCall">{{$ctrleadregister}}%</h3>
                    <p>{{trans('dashboard.ctrlead')}}</p>
                </div>
                <div class="icon">
                    <i class="ion"></i>
                </div>
                <a href="{{url('lead/home')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
           
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{$staff}}</h3>
                    <p>{{trans('staff.staffs')}}</p>
                </div>
                <div class="icon">
                    <i class="ion"></i>
                </div>
                <a href="{{url('staff')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row mar-20">
        <div class="col-lg-8">
            <div class="box1 opp-led">
                <h4>{{trans('staff.opportunities_leads')}}</h4>
                <div id='chart_opp_lead'></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="box1 opport">
                <h4>{{trans('staff.opportunitiesOfStaff')}}</h4>
                <div id="sales"></div>
            </div>

        </div>
    </div>
    <div class="row mar-20">
        <div class="col-lg-6">
            <div class="box1 opp-led">
                <h4>{{trans('dashboard.call_rate')}}</h4>
                <h3 id='chart_call'></h3>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="box1 opport">
                <div></div>
            </div>

        </div>
    </div>
    <div class="row">

        <div class="col-md-12 col-lg-12">
            <meta name="_token" content="{{ csrf_token() }}">
            <div class="panel panel-success succ-mar">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                           data-l="true"></i>
                        {{ trans('staff.my_task_list') }}
                    </h4>
                </div>
                <div class="panel-body task-body1">
                    <div class="row list_of_items">
                    </div>
                </div>
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
            //c3 customisation
            var chart_opp_lead = c3.generate({
                bindto: '#chart_call_date',
                data: {
                    x: 'x',
                    columns: [
                            ['x', @foreach($leadAssign as $item) "{{$item["date_assign"]}}", @endforeach],
                            ['Bỏ qua',  @foreach($leadAssign as $item) {{$item["leadNoAccept"]}},  @endforeach],
                            ['Chăm sóc', @foreach($leadAssign as $item) {{$item["leadAccept"]}},  @endforeach]
                    ]
                },
                
                color: {
                    pattern: ['#ff0000', '#4FC1E9']
                },
                axis: {
                    x: {
                        type: 'timeseries',
                        tick: {
                            format: '%Y-%m-%d'
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom'
                },
                padding: {
                    top: 10
                }
            });
            //end data call

            var data_opp_lead = [
                ['Chăm sóc', 'Thành công'],
                @foreach($opportunity_leads as $item)
                [{{$item['opportunity']}}, {{$item['leads']}}],
                @endforeach
            ];
            //c3 customisation
            var chart_opp_lead = c3.generate({
                bindto: '#chart_opp_lead',
                data: {
                    rows: data_opp_lead,
                    type: 'area-spline'
                },
                color: {
                    pattern: ['#FD9883', '#4FC1E9']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonth(d);
                            }
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom'
                },
                padding: {
                    top: 10
                }
            });

            function formatMonth(d) {

                @foreach($opportunity_leads as $id => $item)
                if ('{{$id}}' == d) {
                    return '{{$item['month']}}' + ' ' + '{{$item['year']}}'
                }
                @endforeach
            }
            setTimeout(function () {
                chart_opp_lead.resize();
            }, 2000);

            setTimeout(function () {
                chart_opp_lead.resize();
            }, 4000);

            setTimeout(function () {
                chart_opp_lead.resize();
            }, 6000);
            $("[data-toggle='offcanvas']").click(function (e) {
                chart_opp_lead.resize();
            });
            /*c3 line chart end*/

            /*c3 pie chart*/
            var chart = c3.generate({
                bindto: '#sales',
                data: {
                    columns: [
                        ['Đang chăm sóc', {{$opportunity_negotiation}}],
                        ['Thành công', {{$opportunity_won}}],
                        ['Hết hạn', {{$opportunity_expired}}],
                        ['Không thành công', {{$opportunity_loss}}]
                    ],
                    type: 'pie',
                    colors: {
                        'Đang chăm sóc': '#fd9883',
                        'Thành công': '#37bc9b',
                        'Hết hạn': '#ffcc66',
                        'Không thành công': '#ff0000'
                    },
                    labels: true
                },
                pie: {
                        label: {
                            format: function (value, ratio, id) {
                                return d3.format('')(value);
                            }
                        }
                    }
            });
            $(".sidebar-toggle").on("click",function () {
                setTimeout(function () {
                    chart.resize();
                },200)
            });
            /*c3 pie chart end*/

             /* chart call*/
             var chart = c3.generate({
                bindto: '#chart_call',
                data: {
                    columns: [
                        ['Thành công', {{$callTotalSuccess}}],
                        ['Không thành công', {{$callTotalMissing}}],
                    ],
                    type: 'pie',
                    colors: {
                        'Thành công': '#37bc9b',
                        'Không thành công': '#ff0000',
                    },
                    
                },
                //labels: true,
                pie: {
                        label: {
                            format: function (value, ratio, id) {
                                return d3.format('')(value);
                            }
                        }
                    }
            });
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

            {{--var demo = new CountUp("countno1", 0, "{{$contracts}}", 0, 3, options);--}}
            {{--demo.start();--}}
            var demo = new CountUp("countno2", 0, "{{$products}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("countno3", 0, "{{$opportunities}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("countno4", 0, "{{$customers}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("leadTotal", 0, "{{$totalLead}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("totalSuccess", 0, "{{$opportunity_won}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("totalCall", 0, "{{$callTotal}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("totalCallSuccess", 0, "{{$callTotalSuccess}}", 0, 3, options);
            demo.start();
            
            /*countup end*/
            $('.task-body1').slimscroll({
                height: '363px',
                size: '5px',
                opacity: 0.2
            });

        });
    </script>

@stop