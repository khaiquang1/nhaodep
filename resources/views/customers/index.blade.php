@extends('layouts.user')
@section('title')
    {{trans('dashboard.dashboard')}}
@stop

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="box1">
                <h4>{{trans('dashboard.invoices_my_month')}}</h4>
                <hr>
                <div id="invoice1"></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="box1">
                <h4>{{trans('dashboard.quotations')}}</h4>
                <hr>
                <div id="quotation"></div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/d3.v3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/c3.min.js')}}"></script>
    <script>
        $(function () {


            /*c3 invoice chart1*/
            var data1 = [
                ['Due by months'],
                @foreach($data as $item)
                    [{{$item['invoices_unpaid']}}],
                @endforeach
            ];
            var data2 = [
                ['Quotations'],
                    @foreach($data as $item)
                [{{$item['quotations']}}],
                @endforeach
            ];

            var chart = c3.generate({
                bindto: '#invoice1',
                data: {
                    rows: data1,
                    type: 'spline'
                },
                color: {
                    pattern: ['#4FC1E9']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonthData(d);
                            }
                        }
                    },
                    y: {
                        tick: {
                            format: d3.format("$,")
                            //format: function (d) { return "Custom Format: " + d; }
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

            var chart2 = c3.generate({
                bindto: '#quotation',
                data: {
                    rows: data2,
                    type: 'spline'
                },
                color: {
                    pattern: ['#3295ff']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonthData(d);
                            }
                        }
                    },
                    y: {
                        tick: {
                            format: d3.format("")
                            //format: function (d) { return "Custom Format: " + d; }
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


            function formatMonthData(d) {

                @foreach($data as $id => $item)
                if({{$id}}==d)
                {
                    return '{{$item['month']}}'+' '+{{$item['year']}}
                }
                @endforeach
            }

            setTimeout(function () {
                chart.resize();
            }, 2000);

            setTimeout(function () {
                chart.resize();
            }, 4000);

            setTimeout(function () {
                chart.resize();
            }, 6000);
            $("[data-toggle='offcanvas']").click(function (e) {
                chart.resize();
            });
            /*c3 invoice chart1 end*/



        })
    </script>
@stop
