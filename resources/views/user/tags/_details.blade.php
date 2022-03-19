<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.salesteam')}}</label>
                    <div class="controls">
                        {{ $salesteam->salesteam }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.main_staff')}}</label>
                    <div class="controls">
                        {{ is_null($salesteam->teamLeader)?"":$salesteam->teamLeader->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.staff_members')}}</label>
                    <div class="controls">
                        @if(isset($salesteam->members))
                            @foreach($salesteam->members as $members)
                                {{$members->full_name}}
                                @if(!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.invoice_target')}}</label>
                    <div class="controls">
                        {{ $salesteam->invoice_target }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.actual_invoice')}}</label>
                    <div class="controls">
                        {{ $salesteam->actualInvoice->sum('grand_total')}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('salesteam.notes')}}</label>
                    <div class="controles">
                        {{ $salesteam->notes }}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="title">{{trans('salesteam.actual_invoices')}}</label>
            <div class="controls">
                <ul>
                    @foreach($salesteam->actualInvoice as $item)
                        <li><a href="{{url('invoice/'.$item->id.'/show')}}">{{ $item->invoice_number }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        {{--<div class="form-group">--}}
            {{--<label class="control-label" for="title">{{trans('salesteam.responsibility')}}</label>--}}
            {{--<div class="controls">--}}
                {{--<label>--}}
                    {{--<input type="checkbox" value="1" name="quotations" class='icheck' disabled--}}
                           {{--@if(isset($salesteam) && $salesteam->quotations==1) checked @endif>--}}
                    {{--{{trans('salesteam.quotations')}} </label>--}}
                {{--<label>--}}
                    {{--<input type="checkbox" value="1" name="leads" class='icheck' disabled--}}
                           {{--@if(isset($salesteam) && $salesteam->leads==1) checked @endif>--}}
                    {{--{{trans('salesteam.leads')}} </label>--}}
                {{--<label>--}}
                    {{--<input type="checkbox" value="1" name="opportunities" class='icheck' disabled--}}
                           {{--@if(isset($salesteam) && $salesteam->opportunities==1) checked @endif>--}}
                    {{--{{trans('salesteam.opportunities')}} </label>--}}
            {{--</div>--}}
        {{--</div>--}}
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
        <div class="member"></div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

        });
    </script>
@stop