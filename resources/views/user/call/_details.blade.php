<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('company.company_name')}}</label>
                    <div class="controls">
                        {{ $call->company_name }}
                        @if (isset($call))
                            @if(is_int($call->company_id) && $call->company_id>0)
                                {{ $companies[$call->company_id] }}
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('call.date')}}</label>
                    <div class="controls">
                        {{ $call->date }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('call.duration')}}</label>
                    <div class="controls">
                        {{ $call->duration }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('call.call_summary')}}</label>
                    <div class="controls">
                        {{ $call->call_summary }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.main_staff')}}</label>
                    <div class="controls">
                        @if (isset($call))
                            {{ $call->responsible->full_name }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url($type) }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @else
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}
                    </button>
                    <a href="{{ url($type) }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @endif
            </div>
        </div>
    </div>
</div>