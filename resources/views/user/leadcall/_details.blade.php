<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-6 col-md-3 m-t-20">
                <label class="control-label" for="title">{{trans('call.date')}}</label>
                <div class="controls">
                    @if (isset($call))
                        {{ $call->date }}
                    @endif
                </div>
            </div>
            <div class="col-sm-6 col-md-3 m-t-20">
                <label class="control-label" for="title">{{trans('call.summary')}}</label>
                <div class="controls">
                    @if (isset($call))
                        {{ $call->call_summary }}
                    @endif
                </div>
            </div>
            <div class="col-sm-6 col-md-3 m-t-20">
                <label class="control-label" for="title">{{trans('call.duration')}}</label>
                <div class="controls">
                    @if (isset($call))
                        {{ $call->duration }}
                    @endif
                </div>
            </div>
            <div class="col-sm-6 col-md-3 m-t-20">
                <label class="control-label" for="title">{{trans('salesteam.main_staff')}}</label>
                <div class="controls">
                    @if (isset($call))
                        {{ $call->resp_staff->full_name }}
                    @endif
                </div>
            </div>
        </div>
        <div class="form-group m-t-20">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url($type.'/'.$lead->id) }}"
                       class="btn btn-primary"><i class="fa fa-arrow-left"></i> {{trans('table.close')}}</a>
                @else
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                    <a href="{{ url($type.'/'.$lead->id) }}"
                    class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @endif
            </div>
        </div>
    </div>
</div>