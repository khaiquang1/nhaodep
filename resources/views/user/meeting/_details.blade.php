<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.meeting_subject')}}</label>
                    <div class="controls">
                        {{ $meeting->meeting_subject }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.main_staff')}}</label>
                    <div class="controls">
                        {{ isset($meeting->responsible->full_name)?$meeting->responsible->full_name:null }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.company_attendees')}}</label>
                    <div class="controls">
                        @if(isset($meeting))
                            @foreach(explode(',',$meeting->company_attendees) as $company_attendees)
                                {{ $user->where('id',$company_attendees)->first()->full_name }}
                                @if(!@$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.staff_attendees')}}</label>
                    <div class="controls">
                        @if(isset($meeting))
                            @foreach(explode(',',$meeting->staff_attendees) as $staff_attendees)
                                @if(!empty($staff_attendees))
                                    {{ isset($staff_attendees)?$user->where('id',$staff_attendees)->first()->full_name:'' }}
                                    @endif
                                @if(!@$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.starting_date')}}</label>
                    <div class="controls">
                        {{ $meeting->meeting_starting_date }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.ending_date')}}</label>
                    <div class="controls">
                        {{ $meeting->meeting_ending_date }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.location')}}</label>
                    <div class="controls">
                        {{ $meeting->location }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.privacy')}}</label>
                    <div class="controls">
                        {{ $meeting->privacy }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.show_time_as')}}</label>
                    <div class="controls">
                        {{ $meeting->show_time_as }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('meeting.meeting_description')}}</label>
                    <div class="controls">
                        {{ $meeting->meeting_description }}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @else
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                    <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @endif
            </div>
        </div>
    </div>
</div>