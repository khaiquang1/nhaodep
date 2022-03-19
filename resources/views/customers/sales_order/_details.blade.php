<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('sales_order.sale_number')}}</label>
                    <div class="controls">
                        {{ $saleorder->sale_number }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('customer', trans('quotation.agent_name'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ is_null($saleorder->customer)?"":$saleorder->customer->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('sales_team_id', trans('quotation.sales_team_id'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ is_null($saleorder->salesTeam)?"":$saleorder->salesTeam->salesteam }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('sales_person_id', trans('salesteam.main_staff'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ is_null($saleorder->salesPerson)?"":$saleorder->salesPerson->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.date')}}</label>
                    <div class="controls">
                        {{ $saleorder->start_date }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.exp_date')}}</label>
                    <div class="controls">
                        {{ $saleorder->exp_date }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.payment_term')}}</label>
                    <div class="controls">
                        {{ $saleorder->payment_term }} {{trans('quotation.days')}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div>
                        <label class="control-label">{{trans('quotation.products')}}</label>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr style="font-size: 12px;">
                                    <th>{{trans('quotation.product')}}</th>
                                    <th>{{trans('quotation.description')}}</th>
                                    <th>{{trans('quotation.quantity')}}</th>
                                    <th>{{trans('quotation.unit_price')}}</th>
                                    <th>{{trans('quotation.subtotal')}}</th>
                                </tr>
                                </thead>
                                <tbody id="InputsWrapper">

                                @if(isset($saleorder)&& $saleorder->salesOrderProducts->count()>0)
                                    @foreach($saleorder->salesOrderProducts as $index => $variants)
                                        <tr class="remove_tr">
                                            <td>
                                                {{$variants->product_name}}
                                            </td>
                                            <td>
                                                {{$variants->description}}
                                            </td>
                                            <td>
                                                {{$variants->pivot->quantity}}
                                            </td>
                                            <td>
                                                {{$variants->pivot->price}}
                                            </td>
                                            <td>
                                                {{$variants->pivot->quantity*$variants->pivot->price}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.total')}}</label>
                    <div class="controls">
                        {{ $saleorder->total }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.tax_amount')}}</label>
                    <div class="controls">
                        {{ $saleorder->tax_amount }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.grand_total')}}</label>
                    <div class="controls">
                        {{ $saleorder->grand_total }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.discount').' %'}}</label>
                    <div class="controls">
                        {{ $saleorder->discount }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.final_price')}}</label>
                    <div class="controls">
                        {{ $saleorder->final_price }}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.terms_and_conditions')}}</label>
                    <div class="controls">
                        {{ $saleorder->terms_and_conditions }}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                <a href="{{ url()->previous() }}" class="btn btn-warning"><i
                            class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
            </div>
        </div>
    </div>
</div>