<div class="topnav">
        <div class="container-fluid">
            <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    
                <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">
                    @if((isset($roledashboard) && $roledashboard==1) || $user_data->inRole('admin'))
                        @if(isset($user_data) && ($user_data->hasAccess(['dashboard.read']) || $user_data->inRole('admin')))    
                        <li class="nav-item dropdown {!! (Request::is( '/') ? 'active' : '') !!}" >
                            <a href="{{url('/')}}"  class="nav-link dropdown-toggle arrow-none">
                                <span> {{trans('left_menu.dashboard')}}</span>
                            </a>
                        </li>
                        @else
                        <li class="nav-item dropdown {!! (Request::is( '/') ? 'active' : '') !!}" >
                            <a href="{{url('/report/summary')}}" class="nav-link dropdown-toggle arrow-none" >
                                <span> {{trans('left_menu.dashboard')}}</span>
                            </a>
                        </li>
                        @endif
                    @endif
                    @if((isset($rolelead) && $rolelead==1) || $user_data->inRole('admin'))
                    <li  class="nav-item dropdown {!! (Request::is( 'lead*') || Request::is( 'lead/*') || Request::is( 'lead') || Request::is( 'deal') || Request::is( 'deal/*') ? 'active treeview' : 'treeview') !!}" >
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-uielement" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-tone mr-2"></i> Khách hàng <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                                aria-labelledby="topnav-uielement">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div>
                                            <a href="{{url('lead')}}"  class="dropdown-item {!! ((Request::is( 'lead') || Request::is( 'lead/home')) ? 'active' : '') !!}"> {{trans('left_menu.leads')}}</a>
                                            <a href="{{url('lead/chat')}}"  class="dropdown-item {!! (Request::is('lead/chat') ? 'active' : '') !!}">Tương tác KH</a>
                                            <a href="{{url('lead/import')}}" class="dropdown-item {!! (Request::is('lead/import') ? 'active' : '') !!}">{{trans('left_menu.leadsimport')}}</a>
                                            <a href="{{url('lead/assign')}}" class="dropdown-item {!! (Request::is('lead/assign') ? 'active' : '') !!}">{{trans('left_menu.assign')}}</a>
                                            
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div>
                                            <a href="{{url('lead/assignto')}}" class="dropdown-item {!! (Request::is('lead/assignto') ? 'active' : '') !!}">{{trans('left_menu.assignto')}}</a>
                                            <a href="{{url('deal')}}" class="dropdown-item {!! (Request::is( 'deal') || Request::is( 'deal/*') ? 'active' : '') !!}">{{trans('left_menu.deal')}}</a>
                                            <a href="{{url('deal/import')}}" class="dropdown-item {!! (Request::is('deal/import') ? 'active' : '') !!}">{{trans('left_menu.dealimport')}}</a>
                                            <a href="{{url('deal/plan')}}" class="dropdown-item {!! ((Request::is( 'deal/plan')) ? 'active' : '') !!}">Kế hoạch</a>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div>
                                            <a href="{{url('deal/email')}}" class="dropdown-item {!! (Request::is('deal/email') ? 'active' : '') !!}">Email</a>
                                            
                                    </div>
                                </div>
                        </div>
                    </li>
                    @endif
                    @if((isset($loggedcalls) && $loggedcalls==1) || $user_data->inRole('admin'))
                    <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-uielement" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-tone mr-2"></i>Báo cáo<div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                                aria-labelledby="topnav-uielement">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div>
                                            <a href="{{url('report/summary')}}"  class="dropdown-item {!! (Request::is( 'report/summary') ? 'active' : '') !!}">
                                                <span>Tổng hợp</span>
                                            </a>
                                            <a href="{{url('report/tags')}}" class="dropdown-item {!! (Request::is( 'report/tags') ? 'active' : '') !!}">
                                                <span>{{trans('left_menu.tags')}}</span>
                                            </a>
                                            <a href="{{url('report/staff')}}"  class="dropdown-item {!! (Request::is( 'report/staff') ? 'active' : '') !!}">
                                                <span>{{trans('left_menu.staff')}}</span>
                                            </a>
                                            <a href="{{url('report/inbox')}}"  class="dropdown-item {!! (Request::is( 'report/inbox') ? 'active' : '') !!}">
                                                <span>{{trans('left_menu.inbox')}}</span>
                                            </a>

                                        </div>
                                    </div>
                                </div>
                            </div>
                    </li>
                    @endif 
                    
                    @if((isset($productrole) && $productrole==1) || $user_data->inRole('admin'))
                    <li class="nav-item dropdown {!! (Request::is( 'product/*') || Request::is( 'product') || Request::is( 'category/*') || Request::is( 'category') ? 'active' : '') !!}">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-customize mr-2"></i>{{trans('left_menu.products')}} <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                                aria-labelledby="topnav-uielement">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div> 
                                            <a href="{{url('product')}}" class="dropdown-item {!! (Request::is( 'product/*') || Request::is( 'product') ? 'active' : '') !!}">
                                                <span>{{trans('left_menu.products')}}</span>
                                            </a>
                                            <a href="{{url('category')}}" class="dropdown-item {!! (Request::is( 'category/*') || Request::is( 'category') ? 'active' : '') !!}">
                                                <span>{{trans('left_menu.category')}}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </li>
                    @endif
                    @if((isset($rolecalendar) && $rolecalendar==1) || $user_data->inRole('admin'))
                    <li class="nav-item dropdown" class="nav-link dropdown-toggle arrow-none" >
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-work" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-customize mr-2"></i>Công việc <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                            aria-labelledby="topnav-uielement">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div> 
                                        <a href="{{url('/task')}}" class="dropdown-item" >{{trans('left_menu.tasks')}}</a>
                                        <a href="{{url('calendar')}}" class="dropdown-item">{{trans('left_menu.calendar')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    @if((isset($roletask) && $roletask==1) || $user_data->inRole('admin'))
                    <li class="nav-item dropdown  {!! (Request::is( 'attendance/*') || Request::is( 'attendance') ? 'active' : '') !!}">
                        <a href="{{url('attendance')}}" class="nav-link dropdown-toggle arrow-none" id="topnav-attendance">
                            <i class="bx bx-customize mr-2"></i> Chấm công <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                            aria-labelledby="topnav-attendance">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div> 
                                        <a  class="dropdown-item" href="{{url('attendance')}}">
                                            <span>Chấm công</span>
                                        </a>
                                        <a  class="dropdown-item" href="{{url('attendance')}}">
                                            <span>Nhân viên vào cty</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    @if((isset($roleconfig) && $roleconfig==1) || $user_data->inRole('admin'))
                    <li class="nav-item dropdown {!! (Request::is( 'product/*') || Request::is( 'product') || Request::is( 'category/*') || Request::is( 'category') ? 'active' : '') !!}">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-config" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-customize mr-2"></i>Cấu hình <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-config">
                                            @if(isset($user_data) && ($user_data->hasAccess(['sales_team.read']) || $user_data->inRole('admin')))
                                            <a class="dropdown-item" href="{{url('salesteam')}}">{{trans('left_menu.salesteam')}}</a>
                                            @endif 
                                            <a href="{{url('tags')}}" class="dropdown-item {!! (Request::is('tags') ? 'active' : '') !!}"><span>Tags</span></a>
                                            <a href="{{url('source')}}" class="dropdown-item {!! (Request::is('source') ? 'active' : '') !!}"><span>Source</span></a>
                                            <a  class="dropdown-item" href="{{url('groupuser')}}"><span>Group nhân viên</span></a>
                                            <a class="dropdown-item" href="{{url('staff')}}">
                                                <span>{{trans('left_menu.staff')}}</span>
                                            </a>
                                            <a class="dropdown-item" href="{{url('branch')}}"><span>{{trans('left_menu.branch')}}</span></a>
                                            <a class="dropdown-item" href="{{url('smsconfig')}}"><span>{{trans('left_menu.smsconfig')}}</span></a>
                                            <a class="dropdown-item" href="{{url('groupclient')}}"><span>Nhóm khách hàng</span></a>
                                            <a class="dropdown-item" href="{{url('clientstatus')}}"><span>Tình trạng khách hàng</span></a>
                                            <a class="dropdown-item" href="{{url('lead/importemail')}}"><span>Xác thực</span></a>
                                            <a class="dropdown-item" href="{{url('getdata')}}"><span>Cấu hình lấy data</span></a>
                                            <a class="dropdown-item" href="{{url('logsaccess')}}"><span>Kiểm tra truy cập</span></a>
                                            <a class="dropdown-item" href="{{url('libcontent')}}"><span>Thư viện nội dung</span></a>
                                            <a class="dropdown-item" href="{{url('contentautomation')}}"><span>Nội dung trả lời tự động</span></a>
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-smsreport"
                                            role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            {{trans('left_menu.reportsms')}} <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-smsreport">
                                                <a class="dropdown-item" href="{{url('sms/marketing')}}" class="dropdown-item">
                                                    <span>{{trans('left_menu.reportsms')}}</span>
                                                </a>
                                                <a class="dropdown-item" href="{{url('sms/reply')}}">
                                                    <span>{{trans('left_menu.reportsmsreply')}}</span>
                                                </a>
                                            </div>
                            </div>
                    </li>
                    @endif

                    @if(isset($user_data) && $user_data->inRole('admin'))
                    <li class="nav-item dropdown {!! (Request::is( 'product/*') || Request::is( 'product') || Request::is( 'category/*') || Request::is( 'category') ? 'active' : '') !!}">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-customize mr-2"></i>Cấu hình <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-uielement">
                                <a  class="dropdown-item" href="{{url('option')}}"><span>{{trans('left_menu.options')}}</span></a>
                                <a  class="dropdown-item" href="{{url('email_template')}}"><span>{{trans('left_menu.email_template')}}</span></a>
                                <a class="dropdown-item" href="{{url('qtemplate')}}"><span>{{trans('left_menu.quotation_template')}}</span></a>
                                <a class="dropdown-item" href="{{url('setting')}}"><span>{{trans('left_menu.settings')}}</span></a>
                                <a  class="dropdown-item" href="{{url('backup')}}"><span>{{trans('left_menu.backup')}}</span></a>
                                <a  class="dropdown-item" href="{{url('partner')}}"><span>{{trans('left_menu.partner')}}</span></a>
                                      
                            </div>
                    </li>
                    @endif
    
                    </ul>
                </div>
            </nav>
        </div>
    </div>