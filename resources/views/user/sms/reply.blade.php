@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
<div class="clearfix">
    {!! Form::open(['url' => 'sms', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
              
                <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('name',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                    <div class="controls">
                        {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('name',  trans('sms.status'), ['class' => 'control-label required', 'placeholder' => 'Tình trạng']) !!}
                    <div class="controls">
                        {!! Form::select('status', $statusList, null, ['id'=>'status', 'class' => 'form-control select_status']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>

                </div>
            </div>
            <div class="col-md-3">
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
                <i class="material-icons">thumb_up</i>
                {{ $title }} (Tổng số @if($totalSMS) {{$totalSMS}} @endif)
            </h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th width="5%">{{ trans('sms.order') }}</th>
                            <th width="15%">{{ trans('sms.device') }}</th>
                            <th width="10%">{{ trans('sms.phone') }}</th>
                            <th width="45%">{{ trans('sms.decription') }}</th>
                            <th width="15%">{{ trans('sms.date') }}</th>
                            <th width="10%">{{ trans('sms.status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($smslist)
                        @php $i=0; @endphp
                        @foreach($smslist as $smsDataList)
                        @php $i++; @endphp
                        <tr>
                            <td class="number">{{ $i }}</td>
                            <td>{{ trim($smsDataList["device"]) }}</td>
                            <td><a href="tel:{{ trim($smsDataList["phone"]) }}" target="_blannk">{{ trim($smsDataList["phone"]) }}</a></td>
                            <td><div class="descsmsscroll">{{ trim($smsDataList["description"]) }}</div></td>
                            <td>{{ trim($smsDataList["created_at"]) }}</td>
                            <td><a href="javascript:void(0);" id="xemtraloi{{$smsDataList["id"]}}" onclick="showSMSReply('{{$smsDataList["id"]}}', '{{$smsDataList["phone"]}}', '{{$smsDataList["datesend"]}}');">Xem trả lời</a> <a href="javascript:void(0);" id="an{{$smsDataList["id"]}}" onclick="hideresult('{{$smsDataList["id"]}}');" style="display:none">Ẩn</a></td>
                        </tr>
                        <tr>
                            <td colspan="6" id="smsdata{{$smsDataList["id"]}}"></td>
                        </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                        @include('layouts.paging', ['paginator' => $smsData])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

    function showSMSReply(idsms, $phone, $date){
        $.ajax({
            type: "GET",
            url: baseUrl+"/sms/reply_sms?phone="+$phone+"&date="+$date,
            success: function (result) {
                $('#smsdata'+idsms).html("");
                $.each(result, function (i, item) {
                    $('#smsdata'+idsms).append("<div class='todolist_list showactions list1' id='"+ item.id +"'>" +
                        "<div class='col-md-12 col-sm-12 col-xs-12 nopadmar custom_textbox1'><div class='col-md-12 col-sm-12 col-xs-12'> "+ item.id+"&nbsp;&nbsp;" + item.description + "</div></div>");
                });
                $("#xemtraloi"+idsms).hide();//{{$smsDataList["id"]}}" 
                $("#an"+idsms).show();//{{$smsDataList["id"]}}" 
                
            }
        });
    }
    function hideresult(idsms){
        $("#an"+idsms).hide();//{{$smsDataList["id"]}}" 
        $("#xemtraloi"+idsms).show();//{{$smsDataList["id"]}}" 
        $('#smsdata'+idsms).html("");
    }

       </script>
@stop
