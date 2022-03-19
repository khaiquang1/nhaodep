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
            {!! Form::open(['url' => 'report/tags', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
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
                    {{ $title }}
                </h4>
            </div>

            <div class="panel-body">
                <div class="table-responsive">

                    <div class="row mar-20">
                        <div class="col-lg-3 col-xs-12 boxreportcycle">
                            <!-- small box -->
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3 id="countno2">{{$totalTag}}</h3>
                                    <p>{{trans('report.totaltag')}}</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pricetag"></i>
                                </div> 
                            </div> 
                            <div class="clear" style="margin:10px 0"></div>
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3 id="leadTotal">{{$totalTagAdd}}</h3>
                                    <p>{{trans('report.tagadd')}}</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-shuffle"></i>
                                </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-9 col-xs-12 boxreportcycle">
                            <div class="box1 opport">
                                <h4>{{trans('report.toptag')}}</h4>
                                <div id="tagreport"></div>
                            </div>
                        </div>

                    </div>
                    <div class="row  mar-20">
                        <div class="col-md-12">
                            <h4>Chi tiết<h3>
                        </div>
                    </div>
                    <div class="row mar-20">
                        <table class="table table-bordered" id="data">
                            <thead>
                            <tr>
                                <th width="200">{{ trans('report.tag') }}</th>
                                <th  width="100">{{date("d/m",strtotime($starting_date))}}</th>
                                @if($dateNumber)
                                @for($i=1;$i<=$dateNumber;$i++)
                                <th  width="50">{{date('d/m', strtotime($starting_date.' +'.$i.' day'))}}</th>
                                @endfor
                                @endif
                                <th  width="50">{{ trans('report.total')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($totalTagGroup)
                            @foreach($totalTagGroup as $totalTagGroupData)

                                <tr>
                                    <td><span style="background:{{$totalTagGroupData["color_bg"]}}; color: {{$totalTagGroupData["color_text"]}}; padding: 5px 10px; border-radius: 5px; font-weight: bold;">@if(isset($totalTagGroupData["title"])){{$totalTagGroupData["title"]}}@endif</span></td>
                                    <td class="number">

                                    @if(isset($listNumberDate) && $listNumberDate!="" && isset($listNumberDate[$totalTagGroupData["id"]][$starting_date]))
                                        {{$listNumberDate[$totalTagGroupData["id"]][$starting_date]}}
                                    @endif
                                    </td>
                                    @if($dateNumber)
                                        @for($k=1;$k<=$dateNumber;$k++)
                                        <td class="number">
                                            @php
                                                $datesearch=date('Y-m-d',strtotime($starting_date.' +'.$k.' day'));
                                            @endphp
                                            @if(isset($listNumberDate) && $listNumberDate!="" && isset($listNumberDate[$totalTagGroupData["id"]][$datesearch]))
                                                {{$listNumberDate[$totalTagGroupData["id"]][$datesearch]}}
                                            @endif
                                        </td>
                                        @endfor
                                    @endif
                                    <td class="number">{{ $totalTagGroupData["totalTagsGroup"] }}</td>
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

    <script>
        $(document).ready(function() {
            var table = $('#data').removeAttr('width').DataTable( {
                scrollY:        "450px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                fixedColumns:   {
                    leftColumns: 1,
                    rightColumns: 1,
                }
            });
        } );

        /*c3 line chart*/
        $(function () {
            var chart = c3.generate({
                bindto: '#tagreport',
                data: {
                    columns: [
                        @if($totalTagGroup)
                            @foreach($totalTagGroup as $dataGroup)
                                @php
                                    $percen=(int)(($dataGroup["totalTagsGroup"]/$totalTagAdd)*100);
                                @endphp
                                @if(isset($dataGroup["title"]) && $dataGroup["title"]!="")
                                ['{{$dataGroup["title"]}}', {{$percen}}],
                                @endif
                            @endforeach
                        @endif
                    ],
                    type: 'pie',
                    colors: {
                        @if($totalTagGroup)
                            @foreach($totalTagGroup as $dataGroup)
                            '@if(isset($dataGroup["title"])){{$dataGroup["title"]}}@endif':'{{$dataGroup["color_bg"]}}',
                            @endforeach
                        @endif
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
        });
    </script>
@stop
