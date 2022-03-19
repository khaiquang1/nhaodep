@extends('layouts.user')

@section('title')
    {{ $title }}
@stop

@section('content')
<div class="calendar">
    {!! Form::open(['url' => 'calendar', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
    <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                        {!! Form::label('user_id',  trans('product.user_care'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                        <div class="controls">
                            {!! Form::select('user_id', $staff_care, null, ['id'=>'user_id', 'class' => 'form-control select_function']) !!}
                            <span class="help-block">{{ $errors->first('user_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                        {!! Form::label('keyword',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                        <div class="controls">
                            {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="search" class="control-label">&nbsp;</label>
                    <div class="controls">
                        <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                    </div>
                </div>
            </div>
    </div>
    {!! Form::close() !!}
    <div class="row">
        <div class="col-md-12">
            <div class="calendar_box">
                <div id="calendar"></div>
                    <div id="fullCalModal" class="modal fade">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span> <span class="sr-only">close</span></button>
                                    <h4 id="modalTitle" class="modal-title"></h4>
                                </div>

                                <div id="modalBody" class="modal-body"></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{trans('table.close')}}</button>
                                </div>
                            </div>

                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
    <script>
        $(document).ready(function () {
            var user=$( "#user_id").val();
            var keyword=$( "#keyword" ).val();
            $('#calendar').fullCalendar({
                "header": {
                    "left": "prev,next today",
                    "center": "title",
                    "right": "month,agendaWeek,agendaDay"
                },
                "eventLimit": true,
                "firstDay": 1,
                "timeFormat": 'HH:mm',
                "eventClick": function (event) {
                    $('#modalTitle').html(event.title);
                    $('#modalBody').html(event.description);
                    $('#fullCalModal').modal();
                },
                "eventColor": event.color,
                "eventSources": [
                    {
                        url:"{{url('calendar/events')}}?user_id="+user+"&keyword="+keyword,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        error: function () {
                            alert('there was an error while fetching events!');
                        }
                    }
                ]
               
            });
        });
    </script>
    <script>
    /*
        document.addEventListener('DOMContentLoaded', function() {
            var date = new Date();
            var d = date.getDate(),
                m = date.getMonth(),
                y = date.getFullYear();
            var dateNow=date.getFullYear()+"-"+date.getMonth()+"-"+date.getDate();
            var user=$( "#user_id").val();
            var keyword=$( "#keyword" ).val();

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [ 'interaction', 'dayGrid' ],
                defaultDate: "@php echo date('Y-m-d'); @endphp",
                eventLimit: true, // allow "more" link when too many events
                "header": {
                    "left": "prev,next today",
                    "center": "title",
                    "right": "month,agendaWeek,agendaDay"
                },
                "eventLimit": true, 
                "firstDay": 1,
                "eventClick": function(event){
                    $('#modalTitle').html(event.event.title);
                    $('#modalBody').html(event.event.extendedProps.description);
                    $('#fullCalModal').modal();
                },
                "eventSources": [
                    {
                        url:"{{url('calendar/events')}}?user_id="+user+"&keyword="+keyword,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        error: function() {
                            alert('there was an error while fetching events!');
                        }
                    }
                ]

            });
            calendar.render();
        });
        */
    </script>
@stop
