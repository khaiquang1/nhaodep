@extends('layouts.user')

@section('title')
    {{ $title }}
@stop
@section('content')
   <div class="calendar">
       <div class="page-header clearfix">
           <div class="pull-right">
               @if($user_data->hasAccess(['meetings.read']) || $user_data->inRole('admin'))
                   <a href="{{ url($type) }}" class="btn btn-success">
                       <i class="fa fa-list"></i> {{ trans('opportunity.lists') }}</a>
               @endif
               @if($user_data->hasAccess(['meetings.write']) || $user_data->inRole('admin'))
                   <a href="{{ url($type.'/create') }}" class="btn btn-primary">
                       <i class="fa fa-plus-circle"></i> {{ trans('table.new') }}</a>
               @endif
           </div>
       </div>
       <div id="calendar"></div>
       <div id="fullCalModal" class="modal fade">
           <div class="modal-dialog">
               <div class="modal-content">
                   <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span>
                           <span class="sr-only">close</span></button>
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
@stop
@section('scripts')
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                "header": {
                    "left": "prev,next today",
                    "center": "title",
                    "right": "month,agendaWeek,agendaDay"
                },
                "eventLimit": true,
                "firstDay": 1,
                "eventClick": function (event) {
                    $('#modalTitle').html(event.title);
                    $('#modalBody').html(event.description);
                    $('#fullCalModal').modal();
                },
                "eventSources": [
                    {
                        url: "{{url('meeting/calendarData')}}",
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
@stop
