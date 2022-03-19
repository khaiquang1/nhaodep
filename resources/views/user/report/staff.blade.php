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
        {!! Form::open(['url' => 'report/staff', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
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
                             <option value="0">Tất cả</option>
                             <option value="7" @if(isset($daterange) && $daterange==0) selected @endif>Hom nay</option>
                             <option value="15"  @if(isset($daterange) && $daterange==1) selected @endif>Hôm qua</option>
                             <option value="30" @if(isset($daterange) && $daterange==7) selected @endif>Trong 7 ngày</option>
                             <option value="60" @if(isset($daterange) && $daterange==30) selected @endif>Tháng này</option>
                        </select>     
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
                Báo cáo nhân viên
            </h4>
        </div>

        

        <div class="panel-body">
            <div class="table-responsive">
                <div class="row mar-20">
                    <div class="col-lg-6 col-xs-12 boxreportcycle">
                        <div class="box1 opport">
                            <h4>{{trans('report.userreport')}}</h4>
                            <div id="userreport"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xs-12 boxreportcycle">
                        <div class="box1 opport">
                            <h4>{{trans('report.timetracking')}}</h4>
                            <div id="trackingreport"></div>
                        </div>
                    </div>

                </div>
                <div class="row  mar-20">
                    <div class="col-md-12">
                        <h4>Chi tiết<h3>
                    </div>
                </div>
                <div class="row mar-20">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>{{ trans('report.stt') }}</th>
                            <th>{{ trans('report.staff') }}</th>
                            <th>Tổng lead</th>
                            <th>{{ trans('report.tag') }}</th>
                            <th>{{ trans('report.number_messenger')}}</th>
                            <th>{{ trans('report.response')}}</th>
                            <th>{{ trans('report.phone')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($detailStaff)
                            @php $i=0;@endphp
                            @foreach($detailStaff as $listDataStaff)
                            @php $i++;@endphp
                            <tr>
                            <td>{{$i}}</td>
                            <td>{{$listDataStaff["fullname"]}}</td>
                            <td class="number">{{$listDataStaff["totalLead"]}}</td>
                            <td class="number">{{$listDataStaff["tags"]["totaltag"]}}</td>
                            <td class="number">{{$listDataStaff["totalMess"]["totalMess"]}}</td>
                            <td class="number">{{number_format($listDataStaff["agvprocess"]["timeadv"]/60,1)}}</td>
                            <td class="number">{{$listDataStaff["phoneGet"]["totalPhone"]}}</td>
                            </tr>

                            @endforeach
                        @endif
                        </tbody>
                    </table>
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

    @if($timeTracking)
        @php $listName=null; $listDataNumber=null; @endphp
        @foreach($timeTracking as $values)
            @php 
                $listName[]='"'.$values["fullname"].'"';
                $listDataNumber[]=number_format($values["number_lead"]["timeadv"]/60,1);
            @endphp
        @endforeach
    @endif
    <script>
        /*c3 line chart*/
        $(function () {
            @if($userList)
            var chart = c3.generate({
                bindto: '#userreport',
                data: {
                    columns: [
                            @foreach($userList as $dataGroup)
                            ['{{$dataGroup["fullname"]}}', {{$dataGroup["number_lead"]}}],
                            @endforeach    
                    ],
                    type: 'pie',
                    colors: {
                            @php 
                            $listcolor=array('#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09', '#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09', '#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09','#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09');
                            $i=0;
                            @endphp
                            @foreach($userList as $dataGroup)
                            @php $i++; @endphp
                            '{{$dataGroup["fullname"]}}':'@php echo $listcolor[$i]; @endphp',
                            @endforeach
                    },
                    labels: true
                },
                /*
                pie: {
                        label: {
                            format: function (value, ratio, id) {
                                return d3.format('.1f')(value);
                            }
                        }
                    }
                    */
            });
            setTimeout(function () {
                chart.resize();
            }, 2000);
            @endif

            //c3 customisation
            @if(isset($timeTracking) && isset($listName) && count($listName)>0)
            var chart_staff = c3.generate({
                bindto: '#trackingreport',
                data: {
                    x : 'x',
                    columns: [
                        ['x', @php echo html_entity_decode(implode(",",$listName)); @endphp],
                        ['{{trans('report.timeavg')}}', {{implode(",",$listDataNumber)}}],
                    ],
                    types: {
                        '{{trans('report.timeavg')}}': 'bar',
                    }
                },
                color: {
                        @if($timeTracking)
                            @php 
                            $listcolor=array('#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09', '#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09', '#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09','#041b8e', '#0d9e5f', '#146c06', '#1d553d', '#8b078d', '#c8af09');
                            $i=0;
                            @endphp
                            @foreach($timeTracking as $dataGroup)
                            @php $i++; @endphp
                            '{{$dataGroup["fullname"]}}':'@php echo $listcolor[$i]; @endphp',
                            @endforeach
                      @endif
                   // pattern: ['#FD9883', '#4FC1E9']
                },
                axis: {
                    x: {
                        type: 'category' // this needed to load string x value
                    }
                }
            });
            setTimeout(function () {
                chart_staff.resize();
            }, 2000);
            @endif
            
            
        });
    </script>
@stop
