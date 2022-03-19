<!DOCTYPE html>
<html>
<head>
    @include('layouts._meta')
    @include('layouts._assets')
    @yield('styles')
</head>
<body class="skin-blue" >
<div id="app">

<div class="wrapper row-offcanvas row-offcanvas-left">
            <aside class="content-side" >
                <header class="header clearfix">
                    <nav class="navbar navbar-static-top" role="navigation">
                        <div class="navbar-left textbox">
                                <a href="/lead" class="topbox">
                                    Danh sách khách hàng
                                </a>
                            </div>
                        <div class="navbar-right">
                            
                            @if(Sentinel::check())
                            <span class="fullname">Chào {{ $user_data->first_name }} {{ $user_data->last_name }}! Bây giờ là ngày: {{date("H:i:s d/m/Y")}}. [<a href="/logout">Hết ca làm việc</a>]</span>
                            @endif 

                            <div class="top-nav notification-row">
                                <!-- notificatoin dropdown start-->
                                <ul class="nav pull-right top-menu">
                                <!-- task notificatoin start -->
                                <!-- 
                                <li id="task_notificatoin_bar" class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                    <i class="icon-task-l"></i>
                                                    <span class="badge bg-important">6</span>
                                                </a>
                                    <ul class="dropdown-menu extended tasks-bar">
                                    <div class="notify-arrow notify-arrow-blue"></div>
                                    <li>
                                        <p class="blue">You have 6 pending letter</p>
                                    </li>
                                    
                                    <li>
                                        <a href="#">
                                        <div class="task-info">
                                            <div class="desc">
                                            Project 1
                                            </div>
                                            <div class="percent">30%</div>
                                        </div>
                                        <div class="progress progress-striped">
                                            <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 30%">
                                            <span class="sr-only">30% Complete (warning)</span>
                                            </div>
                                        </div>
                                        </a>
                                    </li>
                                    <li class="external">
                                        <a href="#">See All Tasks</a>
                                    </li>
                                    </ul>
                                </li> 
                                <li id="mail_notificatoin_bar" class="dropdown" >
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon-envelope-l"></i><span class="badge bg-important" class="inboxnumber">0</span></a>
                                    <ul class="dropdown-menu extended inbox" id="inbox">
                                        <div class="notify-arrow notify-arrow-blue"></div>
                                        <li>
                                            <div class="blue">Bạn có <span class="inboxnumber"></span></div>
                                        </li>
                                        <li class="firstinboxnumber"></li>
                                        <li class="inboxlastitem"> </li>
                                    </ul>
                                </li>
                                -->
                                <!-- inbox notificatoin end -->
                                <!-- alert notification start-->
                                <li id="alert_notificatoin_bar" class="dropdown">
                                    <input type="hidden" id="notificationslast" value="" />
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                        <i class="material-icons">notifications</i>
                                        <span class="badge bg-important notinumber">0</span>
                                    </a>
                                    <ul class="dropdown-menu extended notification">
                                    <div class="notify-arrow notify-arrow-blue"></div>
                                    <li>
                                        <div class="blue">Bạn có <span class="notinumber"></span></div>
                                    </li>
                                    <li class="firstnotinumber"></li>
                                    <li class="notilastitem"></li>
                                    </ul>
                                </li>
                                <!-- alert notification end-->
                                <!-- user login dropdown start-->
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                    <span class="username">{{ $user_data->first_name }} {{ $user_data->last_name }}</span>
                                                    <b class="caret"></b>
                                                </a>
                                    <ul class="dropdown-menu extended logout">
                                    <div class="log-arrow-up"></div>
                                    <li class="eborder-top">
                                        <a href="/profile"><i class="icon_profile"></i> Tài khoản</a>
                                    </li>
                                    <li class="eborder-top">
                                        <a href="/logout"><i class="icon_profile"></i> Thoát</a> 
                                    </li>
                                    <li class="eborder-top" id="receptleadpost">
                                        @if($user_data->received_lead==1)
                                            <a href="javascript:void(0);"  onclick="return updatenewlead(0);">Ngưng nhận lead </a>
                                        @else
                                            <a href="javascript:void(0);" onclick="return updatenewlead(1);">Nhận lead </a>
                                        @endif
                                    </li>
                                    </ul>
                                </li>
                                <!-- user login dropdown end -->
                                </ul>
                                <!-- notificatoin dropdown end-->
                            </div>

                        </div>
                       

                    </nav>
                </header>
                <section class="content">
                    @yield('content')
                </section>
            </aside>
    <!-- /.right-side -->
</div>
<!-- /.right-side -->
<!-- ./wrapper -->
</div>
<!-- global js -->
@include('layouts._assets_footer')
@include('layouts.pusherjs')

@yield('scripts')
<script>
    function loadNotification(){
            $user_noti_id='{{ $user_data->id }}';
            $partner_noti_id='{{ $user_data->partner_id }}';
            $idlast=$('#notificationslast').val();
            $.ajax({
                type: "GET",
                url: "https://api.fastercrm.com/api/listnoti", 
                data: {'user_id': $user_noti_id, 'partner_id': $partner_noti_id, 'idlast': $idlast, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $notificationlist=result.notification;
                    //var res = $tag.split(",");
                    $('.firstnotinumber').empty();
                    $('.notinumber').html($notificationlist.length);
                    $.each($notificationlist, function (i, item) {
                        if(item.id>$idlast){
                            $idlast=item.id;
                        }
                        $class="";
                        if(item.status==0){
                            $class="bold";
                        }
                        $link=item.url;
                        $itemNotification="<div id=\"linenoti"+item.id+"\" class=\"linenoti linenoti"+item.id+" "+$class+"\" onclick=\"updatenotification('status', '"+item.id+"', '"+$link+"');\"><span>"+item.title+"</span><span class=\"small italic pull-right\"> "+item.created_at+"</span></div>";
                        $('.firstnotinumber').append($itemNotification);
                    });
                    $('#notificationslast').val($idlast);

                    
                }
            });
    }
    function updatenotification($status, $id, $link){
        if($id>0){
            $.ajax({
                type: "POST",
                url: "https://api.fastercrm.com/api/updatenotification",
                data: {'id': $id, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    if(result.success==1){
                        $("#linenoti"+$id).removeClass("bold");
                        if($link!=""){
                            location.href=$link;
                        }
                    }else{
                        alert('Có lỗi xảy ra');
                        return false;
                    }
                   
                }
            });
        }
    }
    $loaded=0;
    /*
    setInterval(function(){
        loadNotification();
    }, 1000);  */

    function ShowHide(idbox, classelement) {
        var x = document.getElementById(idbox);
        if (x.style.display === "none") {
            x.style.display = "block";
            $('.'+classelement).html('[ - ]');
        } else {
            x.style.display = "none";
            $('.'+classelement).html('[ + ]');
        }
    }
    function updatenewlead($type){
        if($type>=0){
            $.ajax({
                type: "POST",
                url: "/lead/acceptlead",
                data: {'received_lead': $type, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    if(result.success==1){
                        if($type==1){
                            alert('Bạn đã mở lại nhận lead');
                            $("#receptleadpost").html('<a href="javascript:void(0);"  onclick="return updatenotification(0);">Ngưng nhận lead </a>');
                        }else{
                            alert('Bạn đã ngưng nhận lead');
                            $("#receptleadpost").html('<a href="javascript:void(0);"  onclick="return updatenotification(1);">Nhận lead</a>');
                        }
                        return true;
                    }else{
                        alert('Có lỗi xảy ra');
                        return false;
                    }
                   
                }
            });
        }
    }
</script>
</body>
</html>
