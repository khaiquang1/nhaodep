<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.meeting_subject')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->meeting_subject }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.main_staff')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
{{--                            {{ $meeting->responsible->full_name }}--}}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.company_attendees')}}</label>
                    <div class="controls">
                        @if(isset($meeting))
                            @foreach(explode(',',$meeting->company_attendees) as $company_attendees)
                                {{ $opportunity->user->where('id',$company_attendees)->first()->full_name }}
                                @if(!@$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.staff_attendees')}}</label>
                    <div class="controls">
                        @if(isset($meeting))
                            @foreach(explode(',',$meeting->staff_attendees) as $staff_attendees)
                                {{ $staff_attendees ? $staffs[$staff_attendees]:null }}
                                @if(!@$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.starting_date')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->meeting_starting_date }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.ending_date')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->meeting_ending_date }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.location')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->location }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.privacy')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->privacy }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.show_time_as')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->show_time_as }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.meeting_description')}}</label>
                    <div class="controls">
                        @if (isset($meeting))
                            {{ $meeting->meeting_description }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        @if (@$action == 'show')
                            <a href="{{ url($type.'/'.$opportunity->id) }}"
                               class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.close')}}</a>
                        @else
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                            <a href="{{ url($type.'/'.$opportunity->id) }}"
                               class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>