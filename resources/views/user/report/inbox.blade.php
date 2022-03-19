@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/jquery-jvectormap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
{{-- Content --}}
@section('content')
    <div class="clearfix">
        {!! Form::open(['url' => 'report/inbox', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-4" style="display:none">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('page_id',  trans('lead.page'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('page_id', $pageList, null, ['id'=>'page_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('page_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
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

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">people_outline</i>
                 Báo cáo tổng hợp số vào lúc {{date("d/m/Y H:i:s")}}

            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <div class="row mar-20">
                    <div class="col-lg-2 col-xs-6 boxreportinbox">
                        <h5>Tổng KH</h5>
                        <div class="linereport">Tổng: <span class="number">{{number_format($totalClient)}}</span></div>
                        <div class="linereport">Hôm nay: <span class="number">{{number_format($totalClientToday)}}</span></div>
                        <div class="linereport">Tuần này: <span class="number">{{number_format($totalClientWeek)}}</span></div>
                        <div class="linereport">Tuần trước: <span class="number">{{number_format($totalClientAgoWeek)}}</span></div>
                        <div class="linereport">Tháng này: <span class="number">{{number_format($totalClientThisMonth)}}</span></div>
                        <div class="linereport">Tháng trước: <span class="number">{{number_format($totalClientAgoMonth)}}</span></div>
                    </div>
                    <div class="col-lg-2 col-xs-6 boxreportinbox">
                        <h5>Inbox</h5>
                        <div class="linereport">Tổng: <span class="number">{{number_format($totalInbox)}}</span></div>
                        <div class="linereport">Hôm nay: <span class="number">{{number_format($totalInboxToday)}}</span></div>
                        <div class="linereport">Tuần này: <span class="number">{{number_format($totalInboxWeek)}}</span></div>
                        <div class="linereport">Tuần trước: <span class="number">{{number_format($totalInboxAgoWeek)}}</span></div>
                        <div class="linereport">Tháng này: <span class="number">{{number_format($totalInboxThisMonth)}}</span></div>
                        <div class="linereport">Tháng trước: <span class="number">{{number_format($totalInboxAgoMonth)}}</span></div>
                    </div>
                    <div class="col-lg-7 col-xs-12 boxreportcycle">
                        <h5>Top nhân viên</h5>
                        <div class="linereport"> 
                        @if($chatStaff)
                            @foreach($chatStaff as $chatStaffData)
                            <div class="boxperson">
                                <span class="images">@if($chatStaffData["user_avatar"]!="") <img src="//fastercrm.com/uploads/avatar/{{$chatStaffData["user_avatar"]}}" width="100px" /> @else <img src="//fastercrm.com//uploads/avatar/user.png" width="100px" /> @endif
                                <span class="number">{{$chatStaffData["totalInbox"]}}</span>
                                </span>
                                <span  class="fullname">{{$chatStaffData["first_name"]}} {{$chatStaffData["last_name"]}}</span>
                            </div>
                            @endforeach
                        @endif
                        </div>
                    
                    </div>
                </div>
                <div class="row  mar-20">
                    <div class="col-md-12">  
                        <h5>{{trans('report.reportmesstitle')}}<h5>
                        <div class="linereport">
                            <div id="totalInboxSixMonth">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mar-20">
                  
                    <div class="col-md-12">  
                        <div class="col-md-4 boxreportdetail"> 
                            <div class="linereport" style="background:#041b8e">{{trans('report.phone')}} <span class="number">{{number_format($totalPhone)}}</span></div>
                            <div class="linereport" style="background:#0d9e5f">{{trans('report.newsms')}} <span class="number">{{number_format($totalSMS)}}</span></div>
                            <div class="linereport" style="background:#146c06">{{trans('report.oldclient')}} <span class="number">{{number_format($totalSmsOld)}}</span></div>
                            <div class="linereport" style="background:#1d553d">{{trans('report.newclient')}} <span class="number">{{number_format($totalNewClient)}}</span></div>
                        </div>
                        <div class="col-md-8">
                            <div class="linereport">
                                <div id="totalInboxSixMonthDetail">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('js/jquery-jvectormap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.v3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/c3.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/countUp.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
    <script>
        /*c3 line chart*/
        $(function () {
            @if(isset($arraySixMonth))
            var chartSixMonth = c3.generate({
                bindto: '#totalInboxSixMonth',
                data: {
                    x : 'x',
                    columns: [  
                        ['x', @for($i=0;$i<count($arraySixMonth);$i++) "{{$arraySixMonth[$i]}}", @endfor],
                        ['{{trans('report.reportmessclient')}}',  @if($arraySixMonth) @for($i=0;$i<count($arraySixMonth);$i++) @if(isset($listSixMonthData[$arraySixMonth[$i]])){{$listSixMonthData[$arraySixMonth[$i]]}}, @else 0, @endif @endfor @endif
                        ],
                    ],
                    /*
                    types: {
                        '{{trans('report.timeavg')}}': 'bar',
                    } */
                    type: 'bar'
                },
                bar: {
                    width: {
                        ratio: 0.5 // this makes bar width 50% of length between ticks
                    }
                    // or
                    //width: 100 // this makes bar width 100px
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });
            @endif
            var chart = c3.generate({
                bindto: '#totalInboxSixMonthDetail',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @for($i=0;$i<count($arraySixMonth);$i++) "{{$arraySixMonth[$i]}}", @endfor],
                        ['{{trans('report.phone')}}',  @if($arraySixMonth) @for($i=0;$i<count($arraySixMonth);$i++) @if(isset($listPhoneMonthData[$arraySixMonth[$i]])){{$listPhoneMonthData[$arraySixMonth[$i]]}}, @else 0, @endif @endfor @endif
                        ],
                        ['{{trans('report.newsms')}}',  @if($arraySixMonth) @for($i=0;$i<count($arraySixMonth);$i++) @if(isset($listSmsNewSixData[$arraySixMonth[$i]])){{$listSmsNewSixData[$arraySixMonth[$i]]}}, @else 0, @endif @endfor @endif
                        ],
                        ['{{trans('report.oldclient')}}',  @if($arraySixMonth) @for($i=0;$i<count($arraySixMonth);$i++) @if(isset($listSmsOldSixData[$arraySixMonth[$i]])){{$listSmsOldSixData[$arraySixMonth[$i]]}}, @else 0, @endif @endfor @endif
                        ],
                        ['{{trans('report.newclient')}}',  @if($arraySixMonth) @for($i=0;$i<count($arraySixMonth);$i++) @if(isset($listClientNewSixData[$arraySixMonth[$i]])){{$listClientNewSixData[$arraySixMonth[$i]]}}, @else 0, @endif @endfor @endif
                        ]
                    ],
                    types: {
                        '{{trans('report.phone')}}': 'area-spline',
                        '{{trans('report.newsms')}}': 'area-spline',
                        '{{trans('report.oldclient')}}': 'area-spline',
                        '{{trans('report.newclient')}}': 'area-spline',
                        // 'line', 'spline', 'step', 'area', 'area-step' are also available to stack
                    },
                    groups: [['{{trans('report.phone')}}', '{{trans('report.newsms')}}', '{{trans('report.oldclient')}}', '{{trans('report.newclient')}}']],
                    colors: {
                        '{{trans('report.phone')}}':'#041b8e',
                        '{{trans('report.newsms')}}':'#0d9e5f',
                        '{{trans('report.oldclient')}}':'#146c06',
                        '{{trans('report.newclient')}}':'#1d553d'
                    },
                    labels: true
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });

        });
    </script>
@stop
