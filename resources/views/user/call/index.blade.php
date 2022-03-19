@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">thumb_up</i>
                {{ $title }}
            </h4>
                                
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>{{ trans('call.number') }}</th>
                            <th>{{ trans('call.salesperson') }}</th>
                            <th>{{ trans('call.available_rate') }}</th>
                            <th>{{ trans('call.lead_acceptance_rate') }}</th>
                            <th>{{ trans('call.avg_first_response_time') }}</th>
                            <th>{{ trans('call.avg_calls_per_lead') }}</th>
                            <!-- <th> // trans('call.avg_call_duration') </th> -->
                            <th>{{ trans('lead.total') }}</th>

                        </tr>
                        </thead>
                        <tbody>
                        @if($callsData)
                        @php $i=0; @endphp
                        @foreach($callsData as $callData)
                        @php $i++; @endphp
                        <tr>
                            <td class="number">{{ $i }}</td>
                            <td><a href="{{ url('staff/' . $callData['id'] .'/dashboard' ) }}">{{ trim($callData["first_name"]." ".$callData["last_name"]) }}</a></td>
                            <td class="number">{{ round((abs(strtotime($callData["time_end"]) - strtotime($callData["time_start"]))/(3600*24))*100)  }}%</td>
                            <td class="number">@if($callData["totalLeadAssign"]>0) {{ round(($callData["leadAccept"]/$callData["totalLeadAssign"])*100) }} @endif%</td>
                            <td class="number">@if($callData["totalLeadAssign"]>0) {{ round(($callData["totalLeadAcceptResponTime"]/$callData["totalLeadAssign"])*100) }} @endif %</td>
                            <td class="number">@if(($callData["leadAccept"]+$callData["totalLeadFollow"])>0) {{ round($callData["totalTimeCall"]/($callData["leadAccept"]+$callData["totalLeadFollow"])) }} @endif</td>

                           <!--  <td class="number">if(($callData["leadAccept"]+$callData["totalLeadFollow"])>0)  date("i:s",round($callData["totalCallLead"]/($callData["leadAccept"]+$callData["totalLeadFollow"])))  endif</td> -->
                            <td class="number">{{ $callData["totalLead"]}}</td>

                        </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop
