@extends('layouts.user')
@section('title')
    {{trans('dashboard.dashboard')}}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/jquery-jvectormap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
@section('content')
    {!! Form::open(['url' => 'home', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
    <div class="row  mar-20">
        <div class="col-md-4">
            <div class="form-group required {{ $errors->has('report_date') ? 'has-error' : '' }}">
                {!! Form::label('report_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                <div class="controls">
                    {!! Form::text('report_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group required {{ $errors->has('report_date') ? 'has-error' : '' }}">
                {!! Form::label('product',  trans('product.products'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                <div class="controls">
                    {!! Form::select('product_id', $productSelectData, null, ['id'=>'product_id', 'class' => 'form-control select_function']) !!}
                    <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
                 <div class="form-group required {{ $errors->has('UTM_Source') ? 'has-error' : '' }}">
                    {!! Form::label('UTM_Source',  trans('lead.source'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('UTM_Source', $leadGroupListSearch, null, ['id'=>'UTM_Source', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('UTM_Source', ':message') }}</span>
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
    @if($cookieSummary)
    <div class="row mar-20">
        <h4>&nbsp;&nbsp;{{trans('dashboard.trafic')}}</h4>
        <div id="chart_tracfic"></div>
    </div>
    @endif
    @if($leadAssign)
    <div class="row mar-20">
        <h4>&nbsp;&nbsp;{{trans('dashboard.statusRecivedLead')}}</h4>
        <div id="chart_call_date"></div>
    </div>
    @endif
    <div class="row mar-20">
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 id="countno2"></h3>
                    <p>{{trans('left_menu.products')}}</p>
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
                    <p>{{trans('dashboard.leadTotal')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="{{url('lead')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 id="totalSuccess"></h3>
                    <p>{{trans('dashboard.leadSuccess')}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-contacts"></i>
                </div>
                <a href="{{url('lead?status=6,7')}}" class="small-box-footer">
                    View Detail<i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6 boxreport">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3 id="totalCall"></h3>
                    <p>{{trans('dashboard.totalCall')}}</p>
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
                    <p>{{trans('dashboard.totalCallComunication')}}</p>
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
                <a href="{{url('lead?status=6,7')}}" class="small-box-footer">
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
                <a href="{{url('lead')}}" class="small-box-footer">
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
                <h4>{{trans('dashboard.opportunities_leads')}}</h4>
                <div id='chart_opp_lead'></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="box1 opport">
                <h4>{{trans('dashboard.opportunities')}}</h4>
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
                <h4>{{trans('dashboard.source')}}</h4>
                <h3 id='chart_source'></h3>
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
                        {{ trans('dashboard.historyLogs') }}
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

        $.ajax({
            type: "GET",
            url: baseUrl+"/logs",
            success: function (result) {
                $.each(result, function (i, item) {
                    $('.list_of_items').append("<div class='todolist_list showactions list1' id='"+ item.id +"'>" +
                        "<div class='col-md-12 col-sm-12 col-xs-12 nopadmar custom_textbox1'><div class='col-md-12 col-sm-12 col-xs-12'> "+ item.id+"&nbsp;&nbsp;" + item.description + "</div></div>");
                });
            }
        });

    });

        /*c3 line chart*/
        $(function () {
            var chart_opp_lead = c3.generate({
                bindto: '#chart_call_date',
                data: {
                    x: 'x',
                    columns: [
                            ['x', @foreach($leadAssign as $item) "{{$item["date_assign"]}}", @endforeach],
                            ['B??? qua',  @foreach($leadAssign as $item) {{$item["leadNoAccept"]}},  @endforeach],
                            ['Ch??m s??c', @foreach($leadAssign as $item) {{$item["leadAccept"]}},  @endforeach]
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
                ['????ng k??', 'Th??nh c??ng'],
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
                        ['M???i', {{$opportunity_new}}],
                        ['??ang ch??m s??c', {{$opportunity_negotiation}}],
                        ['Th??nh c??ng', {{$opportunity_won}}],
                        ['H???t h???n', {{$opportunity_expired}}],
                        ['Kh??ng th??nh c??ng', {{$opportunity_loss}}]
                    ],
                    type: 'pie',
                    colors: {
                        'M???i': '#4fc1e9',
                        '??ang ch??m s??c': '#fd9883',
                        'Th??nh c??ng': '#37bc9b',
                        'H???t h???n': '#ffcc66',
                        'Kh??ng th??nh c??ng': '#ff0000'
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

            var chart = c3.generate({
                bindto: '#chart_source',
                data: {
                    columns: [
                        @if($leadGroupSource)
                            @foreach($leadGroupSource as $leadCount)
                            ['@if($leadCount["UTM_Source"]!="") {{$leadCount["UTM_Source"]}} @else Direct @endif', {{$leadCount["totalUTM"]}}],
                            @endforeach
                        @endif
                    ],
                    type: 'pie',
                    colors: {},
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
                        ['Th??nh c??ng', {{$callTotalSuccess}}],
                        ['Kh??ng th??nh c??ng', {{$callTotalMissing}}],
                    ],
                    type: 'pie',
                    colors: {
                        'Th??nh c??ng': '#37bc9b',
                        'Kh??ng th??nh c??ng': '#ff0000',
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
            // Chart dashboard trafic

            
            @if($cookieSummary)
            var chart_trafic = c3.generate({
                bindto: '#chart_tracfic',
                data: {
                    x: 'x',
                    columns: [
                            ['x', @foreach($cookieSummary as $item) "{{$item['date_sum']}}", @endforeach],
                            ['Session',  @foreach($cookieSummary as $item) {{$item['totalCookie']}},  @endforeach],
                            ['????ng k??', @foreach($cookieSummary as $item) {{$item['leadTotal']}},  @endforeach],
                            ['Th??nh c??ng', @foreach($cookieSummary as $item) {{$item['leadTotalSuccess']}},  @endforeach]
                    ]
                },
                
                color: {
                    pattern: ['#0a3fb7', '#4FC1E9', '#10c410']
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
            @endif


        });
    </script>

@stop