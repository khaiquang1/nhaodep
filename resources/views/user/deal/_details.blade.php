<div class="panel panel-primary">
    <div class="panel-body">

        <div class="row">
            <div class="col-md-12">
                @if($user_data->hasAccess(['logged_calls.read']) || $user_data->inRole('admin'))
                    <a href="{{ url('leadcall/' . $lead->id ) }}" class="btn btn-primary call-summary">
                        <i class="fa fa-phone"></i> <b>{{$lead->calls()->count()}}</b> {{ trans("table.calls") }}
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4 m-t-20">
                {!! Form::label('company_name', trans('lead.company_name'), ['class' => 'control-label']) !!}
                <div>{{ $lead->company_name }}</div>
            </div>
            <div class="col-sm-4 m-t-20">
                {!! Form::label('function', trans('Function Type'), ['class' => 'control-label', 'placeholder'=>'select']) !!}
                <div>{{ $lead->function }}</div>
            </div>
            <div class="col-sm-4 m-t-20">
                {!! Form::label('product_name', trans('lead.product_name'), ['class' => 'control-label' ]) !!}
                <div>{{ $lead->product_name }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 m-t-20">
                {!! Form::label('additionl_info', trans('lead.additionl_info'), ['class' => 'control-label']) !!}
                <div>{{ $lead->additionl_info }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 m-t-30">
                <h4 class="m-0">Personal Info:</h4>
            </div>
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('client_name', trans('lead.agent_name'), ['class' => 'control-label']) !!}
                <div>{{ $lead->title.' '.$lead->client_name }}</div>
            </div>
            @if(isset($lead->country) && $lead->country!="")
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('country_id', trans('lead.country'), ['class' => 'control-label']) !!}
                <div>{{ $lead->country->name }}</div>
            </div>
            @endif
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('state_id', trans('lead.state'), ['class' => 'control-label']) !!}
                <div>{{ @$lead->state->name }}</div>
            </div>
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('city_id', trans('lead.city'), ['class' => 'control-label']) !!}
                <div>{{ @$lead->city->name }}</div>
            </div>
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('phone', trans('lead.phone'), ['class' => 'control-label']) !!}
                <div>{{ @$lead->phone }}</div>
            </div>
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('mobile', trans('lead.mobile'), ['class' => 'control-label']) !!}
                <div>{{ @$lead->mobile }}</div>
            </div>
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('email', trans('lead.email'), ['class' => 'control-label']) !!}
                <div>{{ @$lead->email }}</div>
            </div>
            <div class="col-sm-6 col-lg-3 m-t-20">
                {!! Form::label('priority', trans('lead.priority'), ['class' => 'control-label']) !!}
                <div>{{ @$lead->priority }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 m-t-20">
                {!! Form::label('address', trans('lead.address'), ['class' => 'control-label']) !!}
                <div>{{ $lead->address }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 margin-top">
                <div class="form-group">
                    <div class="controls">
                        @if ($action == 'show')
                            <a href="{{ url($type) }}" class="btn btn-warning"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        @else
                            <button type="submit" class="btn btn-danger"><i
                                        class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                            <a href="{{ url($type) }}" class="btn btn-warning"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>