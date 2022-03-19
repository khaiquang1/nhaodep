@section('styles')
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" type="text/css">
@stop
<div class="panel panel-primary">
    <div class="panel-body">
        <div class="nav-tabs-custom" id="user_tabs">
            <ul class="nav nav-tabs Set-list">
                <li class="active">
                    <a href="#general"
                       data-toggle="tab" title="{{ trans('staff.info') }}"><i
                                class="material-icons md-24">info</i></a>
                </li>
                <li>
                    <a href="#logins"
                       data-toggle="tab" title="{{ trans('staff.login') }}"><i
                                class="material-icons md-24">lock</i></a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="general">
                    <div class="row">
                        <div class="col-sm-5 col-md-4 col-lg-3 m-t-20">
                            <div class="fileinput fileinput-new">
                                <div class="fileinput-preview thumbnail form_Blade">
                                    @if(isset($staff->avatar))
                                        <img src="{{ $staff->avatar }}" alt="avatar" width="300">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-7 col-md-8 col-lg-9 m-t-20">
                            <div class="form-group">
                                <label class="control-label" for="title">{{trans('staff.full_name')}}</label>
                                : {{ $staff->full_name }}
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="title">{{trans('staff.phone_number')}}</label>
                                : {{ $staff->phone_number }}
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="title">{{trans('staff.email')}}</label>
                                : {{ $staff->email }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 m-t-10">
                            <div class="panel-content">
                                <h4>{{trans('staff.permissions')}}</h4>
                                <div class="row">
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.sales_teams')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="sales_team.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['sales_team.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="sales_team.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['sales_team.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="sales_team.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['sales_team.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.leads')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="leads.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['leads.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="leads.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['leads.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="leads.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['leads.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.opportunities')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="opportunities.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['opportunities.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="opportunities.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['opportunities.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="opportunities.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['opportunities.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.logged_calls')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="logged_calls.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['logged_calls.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="logged_calls.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['logged_calls.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="logged_calls.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['logged_calls.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.meetings')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="meetings.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['meetings.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="meetings.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['meetings.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="meetings.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['meetings.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.products')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="products.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['products.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="products.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['products.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="products.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['products.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.quotations')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="quotations.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['quotations.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="quotations.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['quotations.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="quotations.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['quotations.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.sales_orders')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="sales_orders.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['sales_orders.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="sales_orders.write"
                                                       disabled
                                                       class='icheckblue'
                                                       @if(isset($staff) && $staff->hasAccess(['sales_orders.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="sales_orders.delete"
                                                       disabled
                                                       class='icheckred'
                                                       @if(isset($staff) && $staff->hasAccess(['sales_orders.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.invoices')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="invoices.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['invoices.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="invoices.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['invoices.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="invoices.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['invoices.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.staff')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="staff.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['staff.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="staff.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['staff.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="staff.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['staff.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-2">
                                        <h5 class="m-t-20">{{trans('staff.companies')}}</h5>
                                        <div class="input-group">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="contacts.read"
                                                       class='icheckgreen' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['contacts.read'])) checked @endif>
                                                <i class="success"></i> {{trans('staff.read')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="contacts.write"
                                                       class='icheckblue' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['contacts.write'])) checked @endif>
                                                <i class="warning"></i> {{trans('staff.write')}} </label>
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="contacts.delete"
                                                       class='icheckred' disabled
                                                       @if(isset($staff) && $staff->hasAccess(['contacts.delete'])) checked @endif>
                                                <i class="danger"></i> {{trans('staff.delete')}} </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="logins">
                    <div class="m-t-30">
                        <table id="login_details" class="table table-striped table-bordered dataTable no-footer">
                            <thead>
                            <th>{{trans('staff.date_time')}}</th>
                            <th>{{trans('staff.ip_address')}}</th>
                            </thead>
                            <tbody>
                            @foreach($staff->logins as $login )
                                <tr>
                                    <td>{{$login->created_at->format(config('settings.date_format').' '. Settings::get('time_format'))}}</td>
                                    <td>{{$login->ip_address}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 m-t-10">
                <div class="form-group">
                    <div class="controls">
                        @if (@$action == 'show')
                            <a href="{{ url($type) }}" class="btn btn-warning m-t-10"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        @else
                            <button type="submit" class="btn btn-danger m-t-10"><i
                                        class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                            <a href="{{ url($type) }}" class="btn btn-warning m-t-10"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>

                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/icheck.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.icheckgreen').iCheck({
                checkboxClass: 'icheckbox_minimal-green',
                radioClass: 'iradio_minimal-green'
            });
            $('.icheckblue').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
            $('.icheckred').iCheck({
                checkboxClass: 'icheckbox_minimal-red',
                radioClass: 'iradio_minimal-red'
            });
            $(".icheckbox_minimal-red.checked,.icheckbox_minimal-green.checked,.icheckbox_minimal-blue.checked").removeClass("disabled")
            $('#login_details').DataTable({
                "pagination": true
            });
        });
    </script>
@stop
