<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('product_name', trans('opportunity.opportunity_name'), ['class' => 'control-label']) !!}
                    <div>
                        {{ $opportunity->opportunity }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('stages', trans('opportunity.stages'), ['class' => 'control-label']) !!}
                    <div>
                        {{ $opportunity->stages }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                {!! Form::label('stages', trans('opportunity.expected_revenue'), ['class' => 'control-label']) !!}
                    <div>
                        {{ $opportunity->expected_revenue }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('stages', trans('opportunity.probability'), ['class' => 'control-label']) !!}
                    <div>
                        {{ $opportunity->probability }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('stages', trans('company.company_name'), ['class' => 'control-label']) !!}
                    <div>
                        {{ isset($opportunity->companies->name)?$opportunity->companies->name:null }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('stages', trans('lead.agent_name'), ['class' => 'control-label']) !!}
                    <div>
                        {{ isset($opportunity->customer->full_name)?$opportunity->customer->full_name:null }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('sales_team_id', trans('salesteam.sales_team_id'), ['class' => 'control-label']) !!}
                    <div>
                        {{ isset($opportunity->salesTeam->salesteam)?$opportunity->salesTeam->salesteam:null }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('stages', trans('salesteam.main_staff'), ['class' => 'control-label']) !!}
                    <div>
                        {{ isset($opportunity->staffs->full_name)?$opportunity->staffs->full_name:null }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group ">
                    {!! Form::label('additional_info', trans('opportunity.next_action'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $opportunity->next_action_date }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group ">
                    {!! Form::label('additional_info', trans('opportunity.expected_closing'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $opportunity->expected_closing_date }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('stages', trans('opportunity.lost_reason'), ['class' => 'control-label']) !!}
                    <div>
                       {{ $opportunity->lost_reason }}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group ">
                    {!! Form::label('additional_info', trans('opportunity.additional_info'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $opportunity->additional_info }}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
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
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function(){
           $("#lost_reason").select2({
               theme:"bootstrap",
               placeholder:"{{ trans('opportunity.lost_reason') }}"
           });
        });
    </script>
    @endsection