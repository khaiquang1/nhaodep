<div class="panel panel-primary">
    <div class="panel-body">
        <div class="form-group">
            <h2>{{ $company->name }}</h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3 class="m-0 m-t-10">{{trans('company.cash_information')}}</h3>

                <div class="row">
                    <div class="col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="txt"><strong>{{trans('company.total_invoices')}}</strong></div>
                        <div class="number c-primary">
                            @if($settings['currency_position']=='left')
                                ${{isset($total_invoices)?$total_invoices:0}}
                            @else
                                {{isset($total_invoices)?$total_invoices:0}}$
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="txt"><strong>{{trans('company.open_invoices')}}</strong></div>
                        <div class="number c-green">
                            @if($settings['currency_position']=='left')
                                ${{ isset($open_invoices)?$open_invoices:0 }}
                            @else
                                {{ isset($open_invoices)?$open_invoices:0 }}$
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="txt"><strong>{{trans('company.overdue_invoices')}}</strong></div>
                        <div class="number c-red">
                            @if($settings['currency_position']=='left')
                                ${{ isset($overdue_invoices)?$overdue_invoices:0}}
                            @else
                                {{ isset($overdue_invoices)?$overdue_invoices:0}}$
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="txt"><strong>{{trans('company.paid_invoices')}}</strong></div>
                        <div class="number c-blue">
                            @if($settings['currency_position']=='left')
                                ${{ isset($paid_invoices)?$paid_invoices:0}}
                            @else
                                {{ isset($paid_invoices)?$paid_invoices:0}}$
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="txt"><strong>{{trans('company.quotations_total')}}</strong></div>
                        <div class="number c-blue">
                            @if($settings['currency_position']=='left')
                                ${{ isset($quotations_total)?$quotations_total:0}}
                            @else
                                {{ isset($quotations_total)?$quotations_total:0}}$
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="txt"><strong>{{trans('company.total_sales_orders')}}</strong></div>
                        <div class="number c-blue">
                            @if($settings['currency_position']=='left')
                                ${{ isset($salesorder_total)?$salesorder_total:0}}
                            @else
                                {{ isset($salesorder_total)?$salesorder_total:0}}$
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 m-t-10">
                <h3 class="m-0 m-t-20">{{trans('company.company_activities')}}</h3>
                <div class="widget-infobox row">
                    <div class="infobox col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="left">
                            <strong><i class="material-icons">phone</i> {{trans('company.calls')}}</strong>
                        </div>
                        <div class="right">
                            <div class="clearfix">
                                <div>
                                    <span class="c-red pull-left">{{ $calls}}</span>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="infobox col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="left">
                            <strong><i class="material-icons bg-blue">attach_money</i>{{trans('company.salesorder')}}</strong>
                        </div>
                        <div class="right">
                            <div>
                                <span class="c-primary pull-left">{{ isset($salesorder)?$salesorder:0}}</span>
                                <br>
                            </div>
                        </div>
                    </div>
                    <div class="infobox col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="left">
                            <i class="icon-note bg-purple"></i>
                        </div>
                        <div class="right">
                            <div class="clearfix">
                                <div class="txt"><strong><i class="material-icons">web</i> {{trans('company.invoices')}}</strong></div>
                                <div>
                                    <span class="c-purple pull-left">{{ isset($invoices)?$invoices:0}}</span>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="infobox col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="left">
                            <strong><i class="material-icons">receipt</i> {{trans('company.quotations')}}</strong>
                        </div>
                        <div class="right">
                            <div class="clearfix">
                                <div>
                                    <span class="c-orange pull-left">{{ isset($quotations)?$quotations:0}}</span>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="infobox col-sm-3 col-md-4 col-lg-2 m-t-20">
                        <div class="left">
                            <strong><i class="material-icons">email</i> {{trans('company.emails')}}</strong>
                        </div>
                        <div class="right">
                            <div class="clearfix">
                                <div>
                                    <span class="c-purple pull-left">{{ isset($emails)?$emails:0}}</span>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="form-group m-t-10">
                <div class="controls">
                    @if (@$action == 'show')
                        <a href="{{ url($type) }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    @else
                        <button type="submit" class="btn btn-danger"><i
                                    class="fa fa-trash"></i> {{trans('table.delete')}}
                        </button>
                        <a href="{{ url($type) }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
