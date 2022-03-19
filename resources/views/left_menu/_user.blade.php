<ul class="sidebar-menu" data-widget="tree" >
   @if((isset($roledashboard) && $roledashboard==1) || $user_data->inRole('admin'))
    @if(isset($user_data) && ($user_data->hasAccess(['dashboard.read']) || $user_data->inRole('admin')))    
        <li {!! (Request::is( '/') ? 'active' : '') !!}>
            <a href="{{url('/')}}">
                <span class="nav-icon">
            <i class="material-icons">dashboard</i>
            </span>
                <span class="nav-text"> {{trans('left_menu.dashboard')}}</span>
            </a>
        </li>
        @else
        <li {!! (Request::is( '/') ? 'active' : '') !!}>
            <a href="{{url('/staff/'.$user_data->id.'/dashboard')}}">
                <span class="nav-icon">
            <i class="material-icons">dashboard</i>
            </span>
                <span class="nav-text"> {{trans('left_menu.dashboard')}}</span>
            </a>
        </li>
        @endif
    @endif
    <!-- 
    @if(isset($user_data) && ($user_data->hasAccess(['opportunities.read']) || $user_data->inRole('admin')))
    <li {!! (Request::is( 'opportunity*') || Request::is( 'opportunity') ? 'active' : 'class=""') !!}>
        <a href="{{url('opportunity')}}">
            <span class="nav-icon">
         <i class="material-icons ">event_seat</i>
        </span>
            <span class="nav-text">{{trans('left_menu.opportunities')}}</span>
        </a>
    </li>
    @endif 
-->
    <?php //if(isset($_GET["test"])){?>

    @if((isset($rolelead) && $rolelead==1) || $user_data->inRole('admin'))
    <li {!! (Request::is( 'lead*') || Request::is( 'lead/*') || Request::is( 'lead') || Request::is( 'deal') || Request::is( 'deal/*') ? 'class="active treeview"' : 'class="treeview"') !!}>
        <a>
            <span class="nav-caret pull-right"><i class="fa fa-angle-right"></i> </span>
            <span class="nav-icon">
                <i class="material-icons ">thumb_up</i>
            </span>
            <span class="nav-text">Chăm sóc Khách hàng</span>
        </a>
        <ul class="treeview-menu">
            
            <li {!! ((Request::is( 'lead') || Request::is( 'lead/home')) ? 'active' : '') !!}>
                <a href="{{url('lead')}}">
                    <i class="material-icons ">thumb_up</i>
                    <span class="nav-text">{{trans('left_menu.leads')}}</span></a>
            </li>
            <li {!! (Request::is('lead/chat') ? 'active' : '') !!}>
                <a href="{{url('lead/chat')}}">
                    <i class="material-icons">message</i>
                    <span class="nav-text">{{trans('left_menu.chamsocquamessenger')}}</span></a>
            </li>
            <li {!! (Request::is('lead/import') ? 'active' : '') !!}>
                <a href="{{url('lead/import')}}">
                    <i class="material-icons">email</i>
                    <span class="nav-text">{{trans('left_menu.leadsimport')}}</span></a>
            </li>
            <li {!! (Request::is('lead/assign') ? 'active' : '') !!}>
                <a href="{{url('lead/assign')}}">
                    <i class="material-icons">assignment_ind</i>
                    <span class="nav-text">{{trans('left_menu.assign')}}</span></a>
            </li>
            <li {!! (Request::is('lead/assignto') ? 'active' : '') !!}>
                <a href="{{url('lead/assignto')}}">
                    <i class="material-icons">assignment_turned_in</i>
                    <span class="nav-text">{{trans('left_menu.assignto')}}</span></a>
            </li>
            
            <li {!! (Request::is( 'deal') || Request::is( 'deal/*') ? 'active' : '') !!}>
                <a href="{{url('deal')}}">
                    <i class="material-icons ">thumb_up</i>
                    <span class="nav-text">{{trans('left_menu.deal')}}</span></a>
            </li>
             <li {!! (Request::is('deal/import') ? 'active' : '') !!}>
                <a href="{{url('deal/import')}}">
                    <i class="material-icons">email</i>
                    <span class="nav-text">{{trans('left_menu.dealimport')}}</span></a>
            </li>
            <li {!! ((Request::is( 'deal/plan')) ? 'active' : '') !!}>
                <a href="{{url('deal/plan')}}">
                    <i class="material-icons ">thumb_up</i>
                    <span class="nav-text">Kế hoạch</span></a>
            </li>
            
            
        </ul>
    </li>
    @endif
<?php // }?>
<!-- 
    @if(isset($user_data) && ($user_data->hasAccess(['leads.read']) || $user_data->inRole('admin')))
    <li {!! (Request::is( 'lead*') || Request::is( 'leadcall/*') || Request::is( 'lead') ? 'class="active treeview"' : 'class="treeview"') !!}>
        <a>
            <span class="nav-caret pull-right"><i class="fa fa-angle-right"></i> </span>
            <span class="nav-icon">
                <i class="material-icons ">thumb_up</i>
            </span>
            <span class="nav-text">{{trans('left_menu.list')}}</span>
        </a>
        <ul class="treeview-menu">
            <li {!! (Request::is( 'lead') || Request::is( 'leadcall/*') ? 'active' : '') !!}>
                <a href="{{url('lead')}}">
                    <i class="material-icons ">thumb_up</i>
                    <span class="nav-text">{{trans('left_menu.leads')}}</span></a>
            </li>
            @if(isset($user_data) && ($user_data->hasAccess(['leads.read']) && $user_data->hasAccess(['leads.write']) || $user_data->inRole('admin')))
            <li {!! (Request::is('lead/import') ? 'active' : '') !!}>
                <a href="{{url('lead/import')}}">
                    <i class="material-icons">email</i>
                    <span class="nav-text">{{trans('left_menu.leadsimport')}}</span></a>
            </li>
            @endif
            
        </ul>
    </li>
    @endif

    -->
    @if(isset($user_data) && ($user_data->hasAccess(['sales_team.read']) || $user_data->inRole('admin')))
    <li {!! (Request::is( 'salesteam/*') || Request::is( 'salesteam') ? 'active' : 'class=""') !!}>
        <a href="{{url('salesteam')}}">
            <span class="nav-icon"><i class="material-icons ">groups</i> </span>
            <span class="nav-text"> {{trans('left_menu.salesteam')}}</span>
        </a>
    </li>
    @endif 
    
    @if((isset($loggedcalls) && $loggedcalls==1) || $user_data->inRole('admin'))
    <!-- if(isset($user_data) && ($user_data->hasAccess(['logged_calls.read']) || $user_data->inRole('admin')))-->
        <li {!! (Request::is( 'call/*') || Request::is( 'call') ? 'active' : '') !!}>
            <a href="{{url('call')}}">
                <span class="nav-icon">
            <i class="material-icons ">phone</i>
            </span>
                <span class="nav-text">{{trans('left_menu.reportcalls')}}</span>
            </a>
        </li>
        <li {!! (Request::is( 'call/*') || Request::is( 'call') || Request::is( 'call/*') || Request::is( 'call') ? 'class="active treeview"' : 'class="treeview"') !!}>
            <a>
                <span class="nav-caret pull-right"><i class="fa fa-angle-right"></i></span>
                <span class="nav-icon"><i class="material-icons">table_chart</i></span>
                <span class="nav-text">{{trans('left_menu.report')}}</span>
            </a>
            <ul class="treeview-menu">
            <li >
                    
                </li>
                <li >
                    
                </li>
                <li >
                    
                </li>
                <li >
                    
                </li> 
            </ul>
        </li>
    @endif
    @if($user_data->inRole('admin'))
    <!-- if(isset($user_data) && ($user_data->hasAccess(['sales_orders.read']) || $user_data->inRole('admin'))) -->
    <li {!! (Request::is( 'sales_order/*') || Request::is( 'sales_order') || Request::is('salesorder_delete_list') || Request::is('salesorder_invoice_list') ? 'active' : '') !!}>
        <a href="{{url('sales_order')}}">
            <span class="nav-icon"><i class="material-icons">attach_money</i></span>
            <span class="nav-text">{{trans('left_menu.sales_order')}}</span>
        </a>
    </li>
    @endif 
    @if((isset($productrole) && $productrole==1) || $user_data->inRole('admin'))
<!-- if(isset($user_data) && ($user_data->hasAccess(['products.read']) || $user_data->inRole('admin')))-->
    <li {!! (Request::is( 'product/*') || Request::is( 'product') || Request::is( 'category/*') || Request::is( 'category') ? 'class="active treeview"' : 'class="treeview"') !!}>
        <a>
            <span class="nav-caret pull-right"><i class="fa fa-angle-right"></i></span>
            <span class="nav-icon"><i class="material-icons ">shopping_basket</i> </span>
            <span class="nav-text">{{trans('left_menu.products')}}</span>
        </a>
        <ul class="treeview-menu">
            <li {!! (Request::is( 'product/*') || Request::is( 'product') ? 'active' : '') !!}>
                
            </li>
            <li {!! (Request::is( 'category/*') || Request::is( 'category') ? 'active' : '') !!}>
                
            </li>
        </ul>
    </li>
    @endif

    @if((isset($rolecalendar) && $rolecalendar==1) || $user_data->inRole('admin'))
    <li {!! (Request::is( 'calendar/*') || Request::is( 'calendar') ? 'active' : '') !!}>
        <a href="{{url('calendar')}}">
            <span class="nav-icon">
        <i class="material-icons">event_note</i>
        </span>
            <span class="nav-text">{{trans('left_menu.calendar')}}</span>
        </a>
    </li>
    @endif

    @if((isset($roleconfig) && $roleconfig==1) || $user_data->inRole('admin'))
    <!-- if(isset($user_data) && ($user_data->hasAccess(['contacts.read']) || $user_data->inRole('admin'))) -->
    <li  style="display:none" {!! (Request::is( 'customer/*') || Request::is( 'customer') || Request::is( 'company/*') || Request::is( 'company') ? 'class="active treeview"' : 'class="treeview"') !!}>
        <a>
            <span class="nav-caret pull-right">
          <i class="fa fa-angle-right"></i>
        </span>
            <span class="nav-icon">
           <i class="material-icons">person_pin</i>
        </span>
            <span class="nav-text">{{trans('left_menu.companies')}}</span>
        </a>
        <ul class="treeview-menu"> 
            <li {!! (Request::is( 'company/*') || Request::is( 'company') ? 'active' : '') !!}>
                <a href="{{url('company')}}">
                    <i class="material-icons ">flag</i>
                    <span class="nav-text">{{trans('left_menu.company')}}</span>
                </a>
            </li>
            <li {!! (Request::is( 'customer/*') || Request::is( 'customer') ? 'active' : '') !!}>
                <a href="{{url('customer')}}">
                    <i class="material-icons ">person</i>
                    <span class="nav-text">{{trans('left_menu.agent')}}</span>
                </a>
            </li>
        </ul>
    </li>
    @endif
    <!-- 
    @if(isset($user_data) && ($user_data->hasAccess(['meetings.read']) || $user_data->inRole('admin')))
    <li {!! (Request::is( 'meeting/*') || Request::is( 'meeting') ? 'active' : 'class=""') !!}>
        <a href="{{url('meeting')}}">
            <span class="nav-icon">
         <i class="material-icons">radio</i>
        </span>
            <span class="nav-text">{{trans('left_menu.meetings')}}</span>
        </a>
    </li>
    @endif

    -->
    @if((isset($roletask) && $roletask==1) || $user_data->inRole('admin'))
    <li {!! (Request::is( '/task/*') || Request::is( 'task') ? 'class="actives"' : 'class=""') !!}>
        <a href="{{url('/task')}}">
            <span class="nav-icon">
         <i class="material-icons">event_task</i>
        </span>
            <span class="nav-text"> {{trans('left_menu.tasks')}}</span>
        </a>
    </li>
    @endif

    @if((isset($roletask) && $roletask==1) || $user_data->inRole('admin'))

    <!-- if((isset($rolestaff) && $rolestaff==1) || $user_data->inRole('admin')) -->
    <li {!! (Request::is( 'attendance/*') || Request::is( 'attendance') ? 'class="active treeview"' : 'class="treeview"') !!}>
        <a>
            <span class="nav-caret pull-right"><i class="fa fa-angle-right"></i></span>
            <span class="nav-icon"><i class="material-icons ">timer</i> </span>
            <span class="nav-text">Chấm công</span>
        </a>
        <ul class="treeview-menu">
            <li {!! (Request::is( 'attendance/*') || Request::is( 'attendance') ? 'active' : '') !!}>
                <a href="{{url('attendance')}}">
                    <i class="material-icons">timer</i>
                    <span class="nav-text">Chấm công</span>
                </a>
            </li>
            <li {!! (Request::is( 'notstafflist/*') || Request::is( 'notstafflist') ? 'active' : '') !!}>
                <a href="{{url('category')}}">
                    <i class="material-icons">list</i>
                    <span class="nav-text">Danh sách chưa xác định nhân viên</span>
                </a>
            </li>
        </ul>
    </li>
    @endif


    <!-- if(isset($user_data) && $user_data->hasAccess(['staff.read']) || $user_data->inRole('admin'))-->
    @if((isset($roleconfig) && $roleconfig==1) || $user_data->inRole('admin'))
    <li><a><b>Configuration</b></a></li>
    <li {!! (Request::is('tags') ? 'active' : '') !!}>
                
    </li>
    <li {!! (Request::is('source') ? 'active' : '') !!}>
                <a href="{{url('')}}">
                    <i class="material-icons">input</i> 
                    <span class="nav-text">Source</span></a>
    </li>
 
    <li {!! (Request::is('groupuser') ? 'active' : '') !!}>
                
    </li>
    <li {!! (Request::is( 'staff/*') || Request::is( 'staff') ? 'active' : 'class=""') !!}>
        
    </li>
    <li {!! (Request::is( 'branch/*') || Request::is( 'branch') ? 'active' : 'class=""') !!}>

        
    </li>
    <li {!! (Request::is( 'smsconfig/*') || Request::is( 'smsconfig') ? 'active' : 'class=""') !!}>

        
    </li>
    <li {!! (Request::is('groupclient') ? 'active' : '') !!}>
                
    </li>
    <li {!! (Request::is('clientstatus') ? 'active' : '') !!}>
        
    </li>
    <li {!! (Request::is('lead/importemail') ? 'active' : '') !!}>
               
    </li>
    <li {!! (Request::is('getdata') ? 'active' : '') !!}>
       
    </li>

    

    <li {!! (Request::is('logsaccess') ? 'active' : '') !!}>
        
    </li>
    <li {!! (Request::is('logsaccess') ? 'active' : '') !!}>
       
    </li>
    <li {!! (Request::is('contentautomation') ? 'active' : '') !!}>
        <a href="{{url('contentautomation')}}">
            <i class="material-icons">settings</i>
            <span class="nav-text">Nội dung trả lời tự động</span></a>
    </li>

    <li {!! (Request::is( 'sms/*') || Request::is( 'sms')  || Request::is( 'smsreply') ? 'class="active treeview"' : 'class="treeview"') !!}>
        <a href="{{url('sms')}}">
            <span class="nav-caret pull-right"><i class="fa fa-angle-right"></i></span>
            <span class="nav-icon">
         <i class="material-icons ">email</i>
        </span>
            <span class="nav-text">{{trans('left_menu.reportsms')}}</span>
        </a>
        <ul class="treeview-menu">
            <li {!! (Request::is( 'sms/marketing') || Request::is( 'sms') ? 'active' : '') !!}>
               
            </li>
            <li {!! (Request::is( 'sms/reply') ? 'active' : '') !!}>
                
            </li>
        </ul>
    </li>
    @endif
   

    @if(isset($user_data) && $user_data->inRole('admin'))
    <li {!! (Request::is( 'option/*') || Request::is( 'option') ? 'active' : 'class=""') !!}>
        
    </li>
    <li {!! (Request::is( 'email_template/*') || Request::is( 'email_template') ? 'active' : 'class=""') !!}>
        
    </li>
    <li {!! (Request::is( 'qtemplate/*') || Request::is( 'qtemplate') ? 'active' : 'class=""') !!}>
        
    </li>
    <li {!! (Request::is( 'setting/*') || Request::is( 'setting') ? 'active' : 'class=""') !!}>
        
        
    </li>
    <li {!! (Request::is( 'backup/*') || Request::is( 'backup') ? 'active' : 'class=""') !!}>
           
    </li>
    <li {!! (Request::is( 'partner/*') || Request::is( 'partner') ? 'active' : 'class=""') !!}>
            
    </li>

    
@endif
    <li class="">
        <a href="{{url('logout')}}" class="dropdown-item">{{trans('left_menu.logout')}}</a>
    </li>
</ul>
