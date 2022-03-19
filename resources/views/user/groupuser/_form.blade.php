<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($groupuser))
            {!! Form::model($groupuser, ['url' => $type . '/' . $groupuser->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('groupuser') ? 'has-error' : '' }}">
                        {!! Form::label('name', trans('groupuser.name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('description', trans('groupuser.description'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('description', null, ['class' => 'form-control resize_vertical','placeholder'=>'Nội dung cần tư vấn trong nhóm']) !!}
                            <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($groupuser) && $groupuser->status==1)checked @endif>{{trans('groupuser.status')}} </label>
                    </div>
                </div>
            </div>

            <div class="panel-content permission">
                    <h4>{{trans('groupuser.permissions')}}</h4>
                    <div class="row">
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('groupuser.dashboard')}}</h5>
                            <div class="input-group">
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="dashboard")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                                <!-- 
                                <label>
                                    <input type="checkbox" name="permissions[]" value="group.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['group.full'])) checked @endif>
                                    {{trans('groupuser.full')}} </label>
                                <label>
                                    <input type="checkbox" name="permissions[]" value="group.view_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['group.view_other'])) checked @endif>
                                    {{trans('groupuser.view_other')}} 
                                </label>
                                <label>
                                    <input type="checkbox" name="permissions[]" value="group.view_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['group.view_person'])) checked @endif>
                                    {{trans('groupuser.view_person')}} 
                                </label> -->
                            </div>
                        </div>
                        <!-- 
                        <div class="col-sm-4 col-lg-2">
                            <h5 class="m-t-20">{{trans('staff.sales_teams')}}</h5>
                            <div class="input-group">
                                <label>
                                    <input type="checkbox" name="permissions[]" value="sales_teams.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_teams.all'])) checked @endif>
                                    {{trans('groupuser.all')}} </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="sales_teams.ingroup"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_teams.ingroup'])) checked @endif>
                                    {{trans('groupuser.ingroup')}} 
                                </label>
                            </div>
                        </div> -->
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('staff.leads')}}</h5>
                            <div class="input-group">
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="lead")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                                <!--
                                <label>
                                    <input type="checkbox" name="permissions[]" value="leads.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['leads.full'])) checked @endif>
                                    {{trans('groupuser.full')}} 
                                </label>

                                <label>
                                    <input type="checkbox" name="permissions[]" value="leads.view_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['leads.view_other'])) checked @endif>
                                    {{trans('groupuser.view_other')}}
                                </label>
                                <label>
                                    <input type="checkbox" name="permissions[]" value="leads.view_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['leads.view_person'])) checked @endif>
                                    {{trans('groupuser.view_person')}}
                                </label>

                                <label>
                                    <input type="checkbox" name="permissions[]" value="leads.delete_person"
                                           class='icheckblue' @if(isset($groupuser) && $groupuser->hasAccess(['leads.delete_person'])) checked @endif>
                                    {{trans('groupuser.delete_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="leads.delete_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['leads.delete_other'])) checked @endif>
                                    {{trans('groupuser.delete_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="leads.edit_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['leads.edit_other'])) checked @endif>
                                    {{trans('groupuser.edit_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="leads.edit_owner"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['leads.edit_owner'])) checked @endif>
                                    {{trans('groupuser.edit_owner')}}
                                </label> -->
                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('staff.logged_calls')}}</h5>
                            <div class="input-group">
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="logged_calls")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                               
                            </div>

                            <h5 class="m-t-20">{{trans('staff.chatwidthuser')}}</h5>
                            <div class="input-group">
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="messenger")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                               
                            </div>

                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('staff.products')}}</h5>
                            <div class="input-group">
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="products")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                                <!-- 
                                <label>
                                    <input type="checkbox" name="permissions[]" value="products.write"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['products.write'])) checked @endif>
                                    {{trans('groupuser.write')}} 
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="products.view"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['products.view'])) checked @endif>
                                    {{trans('groupuser.view')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="products.delete"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['products.delete'])) checked @endif>
                                    {{trans('groupuser.delete')}}
                                </label> -->
                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('groupuser.sales_order')}}</h5>
                            <div class="input-group">
                            @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="sales_order")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                                <!-- 
                                <label>
                                    <input type="checkbox" name="permissions[]" value="sales_order.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.full'])) checked @endif>
                                    {{trans('groupuser.full')}} </label>
                                <label>
                                    <input type="checkbox" name="permissions[]" value="sales_order.view_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.view_other'])) checked @endif>
                                    {{trans('groupuser.view_other')}} 
                                </label>
                                <label>
                                    <input type="checkbox" name="permissions[]" value="sales_order.person"
                                           class='icheckblue' @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.person'])) checked @endif>
                                    {{trans('groupuser.person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="sales_order.view_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.view_person'])) checked @endif>
                                    {{trans('groupuser.view_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="sales_order.edit_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.edit_other'])) checked @endif>
                                    {{trans('groupuser.edit_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="sales_order.edit_owner"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.edit_owner'])) checked @endif>
                                    {{trans('groupuser.edit_owner')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="sales_order.delete_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.delete_other'])) checked @endif>
                                    {{trans('groupuser.delete_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="sales_order.delete_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['sales_order.delete_person'])) checked @endif>
                                    {{trans('groupuser.delete_person')}}
                                </label>
-->

                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('groupuser.invoice')}}</h5>
                            <div class="input-group">
                                <!-- 
                                <label>
                                    <input type="checkbox" name="permissions[]" value="invoice.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.full'])) checked @endif>
                                    {{trans('groupuser.full')}} </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="invoice.view_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.view_other'])) checked @endif>
                                    {{trans('groupuser.view_other')}} 
                                </label>

                                <label>
                                <input type="checkbox" name="permissions[]" value="invoice.view_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.view_person'])) checked @endif>
                                    {{trans('groupuser.view_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="invoice.edit_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.edit_other'])) checked @endif>
                                    {{trans('groupuser.edit_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="invoice.edit_owner"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.edit_owner'])) checked @endif>
                                    {{trans('groupuser.edit_owner')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="invoice.delete_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.delete_person'])) checked @endif>
                                    {{trans('groupuser.delete_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="invoice.delete_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['invoice.delete_other'])) checked @endif>
                                    {{trans('groupuser.delete_other')}}
                                </label> -->
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="invoice")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('groupuser.calendar')}}</h5>
                            <div class="input-group">
                                <!--
                                <label>
                                    <input type="checkbox" name="permissions[]" value="calendar.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.full'])) checked @endif>
                                    {{trans('groupuser.full')}} 
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="calendar.view_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.view_other'])) checked @endif>
                                    {{trans('groupuser.view_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="calendar.view_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.view_person'])) checked @endif>
                                    {{trans('groupuser.view_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="calendar.delete_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.delete_other'])) checked @endif>
                                    {{trans('groupuser.delete_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="calendar.delete_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.delete_person'])) checked @endif>
                                    {{trans('groupuser.delete_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="calendar.edit_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.edit_other'])) checked @endif>
                                    {{trans('groupuser.edit_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="calendar.edit_owner"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['calendar.edit_owner'])) checked @endif>
                                    {{trans('groupuser.edit_owner')}}
                                </label>-->
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="calendar")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('groupuser.task')}}</h5>
                            <div class="input-group">
                                <!--
                                <label>
                                    <input type="checkbox" name="permissions[]" value="task.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.full'])) checked @endif>
                                    {{trans('groupuser.full')}} 
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="task.view_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.view_other'])) checked @endif>
                                    {{trans('groupuser.view_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="task.view_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.view_person'])) checked @endif>
                                    {{trans('groupuser.view_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="task.delete_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.delete_other'])) checked @endif>
                                    {{trans('groupuser.delete_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="task.delete_person"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.delete_person'])) checked @endif>
                                    {{trans('groupuser.delete_person')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="task.edit_other"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.edit_other'])) checked @endif>
                                    {{trans('groupuser.edit_other')}}
                                </label>
                                <label>
                                <input type="checkbox" name="permissions[]" value="task.edit_owner"
                                           class='icheckblue'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['task.edit_owner'])) checked @endif>
                                    {{trans('groupuser.edit_owner')}}
                                </label>-->
                                @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="task")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                            <h5 class="m-t-20">{{trans('groupuser.config')}}</h5>
                            <div class="input-group">
                            @if($roles)
                                    @foreach($roles as $rolesData)
                                        @if($rolesData["function"]=="config")
                                        <label>
                                            <input type="checkbox" name="permissions[]" value="{{$rolesData["slug"]}}"
                                            class='icheckgreen'
                                            @if(isset($groupuser) && $groupuser->hasAccess([$rolesData["slug"]])) checked @endif>
                                            {{$rolesData["name"]}} </label> 
                                        @endif
                                    @endforeach
                                @endif
                                <!-- 
                                <label>
                                    <input type="checkbox" name="permissions[]" value="config.full"
                                           class='icheckgreen'
                                           @if(isset($groupuser) && $groupuser->hasAccess(['config.full'])) checked @endif>
                                    {{trans('groupuser.control')}} </label>
                                </label> -->
                            </div>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
            </div>

        <!-- Form Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}
                        </button>
                        <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ./ form actions -->

{{--            {{ $newSales }}--}}

        {!! Form::close() !!}
    </div>
</div>