<div class="panel panel-primary">
    <div class="panel-body">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('contract.start_date')}}</label>
            <div class="controls">
                    {{ $contract->start_date }}
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('contract.end_date')}}</label>
            <div class="controls">
                    {{ $contract->end_date }}
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('contract.description')}}</label>
            <div class="controls">
                    {{ $contract->description }}
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('contract.resp_staff_id')}}</label>
            <div class="controls">
                {{ is_null($contract->responsible)?"":$contract->responsible->full_name }}
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('contract.company')}}</label>
            <div class="controls">
                {{ is_null($contract->company)?"":$contract->company->name }}
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url($type) }}" class="btn btn-primary"><i
                                class="fa fa-arrow-left"></i> {{trans('table.close')}}</a>
                @else
                    <a href="{{ url($type) }}" class="btn btn-primary"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>