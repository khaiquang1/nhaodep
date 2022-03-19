@extends('layouts.chat')
{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
<audio id='bgAudio' style="display:none"> <source src='//api2.fastercrm.com/audio/boom.mp3?v=1' type='audio/mpeg'> </audio>

    @if(isset($approve) && $approve!="" && $leadDetail!="")
        <dialog id="confirm-accept" class="site-dialog">
            <header class="dialog-header">
                <h1>Bạn nhận được 1 đề nghị chăm sóc Khách hàng</h1>
            </header>
            <div class="dialog-content">
                <p>Người chuyển: <strong>{{$leadDetail["assign_from_name"]}}</strong></p>
                @if($leadDetail["opportunity"]!="")
                <p>Tên khách hàng: <strong>{{$leadDetail["opportunity"]}}</strong></p>
                @endif
                <p>Tình trạng khách hàng <strong>{{$leadDetail["statusclient"]}}</strong></p>
                @if($leadDetail["function"]!="")
                <p>Nguồn khách hàng: <strong>{{$leadDetail["function"]}}</strong></p>
                @endif
                @if($leadDetail["opportunity"]!="")
                <p>Sản phẩm quan tâm: <strong>{{$leadDetail["product_name"]}}</strong></p>
                @endif
                <p><strong>Trong 30s bạn không nhận chúng tôi sẽ chuyển người khác</strong></p>
            </div>
            <div class="btn-group cf">
                <button class="btn btn-danger" id="accept" onclick="return acceptlead('{{$approve}}')">Chấp nhận</button> &nbsp;
                <button class="btn btn-cancel" id="cancel">Cancel</button>
            </div>
        </dialog>
    @endif
    <div class="clearfix">
    {!! Form::open(['url' => 'lead/chat', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('page_id',  trans('lead.page'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        @if(isset($pageList) && $pageList!="")
                        {!! Form::select('page_id', $pageList, null, ['id'=>'page_id', 'class' => 'form-control select_function']) !!}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        @if(isset($salesList) && $salesList!="")
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'sales_id', 'class' => 'form-control select_function']) !!}
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('tags') ? 'has-error' : '' }}">
                    {!! Form::label('status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        @if(isset($statusList) && $statusList!="")
                        {!! Form::select('status', $statusList, null, ['id'=>'status', 'class' => 'form-control select_function']) !!}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('keyword',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                    <div class="controls">
                        {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <div class="panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">thumb_up</i>
                {{ $title }}  | <a href="/lead/comment" class="boxchattitle">KH comment</a>
            </h4>
            <div class="title_staffOther dropdown" >
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <span class="username" id="usercare">Chuyển đến nhân viên khác</span>
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu extended">
                    <div class="log-arrow-up"></div>
               
                </ul>
            </div>
        </div>
        <div class="panel-body">
         <div class="row">
            <div class="col-md-12">
                <div class="contenchat">
                    <div class="split left">
                        <ul class="filter">
                            <li><a href="javascript:void(0);" class="filterdata" id="filter1" onclick="filter(1);">Tất cả</a></li>
                            <li><a href="javascript:void(0);" class="filterdata"  id="filter2" onclick="filter(2);">Chưa đọc</a></li>
                            <li><a href="javascript:void(0);" class="filterdata"   id="filter3" onclick="filter(3);">Đã xử lý</a></li>

                        </ul>
                        <div id="userlist">
                            <div id="userload"></div>
                        </div>
                        <span class="loadingbox" style="display:none" onclick="pageloaduser();">Xêm thêm</span>

                        <div class="row">
                            <div class="col-sm-12">
                                <form action="" id="userlistForm" class="form-container" method="post" style="display:none">
                                    <input type="hidden" id="pageloading" value="1" />
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="drop" class="drop-area ui-widget-header" style="display:none">
                        <div class="drop-area-label">Drop image here</div>
                    </div>
                    <div class="split center">
                        <div id="chatWeb">
                            <h3 class="titletuongtac"><span id="stafftalk">Tương tác với khách hàng</span></h3>
                            <div class="boxchat">
                                <div class="linechat" id="idchat0"></div>
                            </div>
                            <div class="lastrecord" id="chatLastRecord"></div>
                            <div class="formchatfix">
                                <form action="" id="messenger" class="form-container" method="post">
                                    <input type="hidden" id="lasttimechat" value="0" />
                                    <input type="hidden" id="lastIdchat" value="0" />
                                    <input type="hidden" id="psid" value="" />
                                    <input type="hidden" id="page_messenger_id" value="" />
                                    <input type="hidden" id="phonemain" value="" />
                                    <input type="hidden" id="fullname" value="" />
                                    <input type="hidden" id="lead_id" value="" />
                                    <input type="hidden" id="task_id" value="" />
                                    <input type="hidden" id="user_id" value="{{$user_data->id}}" />
                                    <input type="hidden" id="is_user_chat" value="" />
                                    <div class="boxtextchat">
                                    <input type="file" name="files" id="file_upload_id" multiple="true" accepts="image/*"  style="display:none" />
                                   <!-- <input type="textbox" placeholder="Type message.." name="msg" id="chatdesc"  required /> -->
                                   <textarea class="scrollEd ng-pristine ng-valid ng-valid-maxlength ng-touched" maxlength="1000" id="chatdesc" ng-model="currentChat.message" ng-model-options="{'updateOn': 'default blur', 'debounce': {'default': 100, 'blur': 0}}" ng-focus="currentChat.unreadMessage = 0" row="1" placeholder="Tin nhắn phản hồi"></textarea>

                                    <a href="#" class="content" id="copycontent"><img src="//api2.fastercrm.com/upload/photos/icons_question.png" width="40px" id="icon_content" class="fa fa-upload"></a>
                                    <a href="#" class="iconupload" onclick="_upload()"><img src="//api2.fastercrm.com/upload/photos/icon_upload.png" width="40px" id="icon_upload" class="fa fa-upload"></a>
                                    </div>
                                    <button type="submit" class="btn" id="chatwithuser" name="chatwithuser">Gởi đi</button>
                                
                                    
                                    <ul class="gallery-image-list" id="uploads">
                                        <input type="hidden" name="photos" value="" />
                                        <!-- The file uploads will be shown here -->
                                    </ul>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="split right">
                        <div class="" id="detaillead">
                            
                                <div class="boxtag">
                                    <h3>Thông tin Khách hàng</h3>
                                    <div class="iconsphoto infobox"><span id="info_photo"></span></div>
                                    <div class="iconsemail infobox"><span id="info_fullname"></span></div>
                                    <div class="iconsemail infobox">Email: <span id="info_email"></span></div>
                                    <div class="iconsphone infobox">SĐT: <span id="info_phone"></span> <a href="javascript:void(0);" onclick="return showboxphone();" class="showboxphone_text" id="showboxphone">+</a><span class="boxphoneupdate"> <input type="number" value="" id="new_phone" />&nbsp;<span id="updatephone" onclick="return updatenewphone();" >Cập nhật</span> <a href="javascript:void(0);" onclick="return hideboxphone();" class="showboxphone_text" id="hideboxphone">-</a></span></div>
                                    <div class="linkfacebook infobox"><span id="info_link"></span></div>
                                    <div class="info_page infobox">Trang: <span id="info_page"></span></div>
                                    <div class="info_viewmore infobox"><span id="info_viewmore"></span></div>
                                    <div class="info_viewmore infobox"><span id="ghimlead">Ghim user</span> <span id="clockuser">Chặn User</span> @if(in_array($user_group,array(44, 43, 46)))<span id="reporttags"><a href='javascript:void(0)'  onclick='return reporttags();'>Báo gắn thẻ sai</a></span> @endif </div>
                                    <button type="button" class="btn" id="createorder" name="createorder">Tạo đơn hàng</button>
                                </div>
                                <div class="boxtag">
                                    <h3>Trạng thái khách hàng</h3>
                                    <div class="liststatusclient"></div>
                                </div>

                                <div class="boxtag"  style="display:none">
                                    <h3>Tags khách hàng</h3>
                                    <div class="listtagclient"></div>
                                </div>

                                <div class="asignbox" style="display:none">
                                    <h3>Chuyển Khách hàng <a href="javascript:void(0);" class="showhideelement" onclick="return ShowHide('transferclienting', 'showhideelement');">[ + ]</a></h3>
                                    <form action="" id="asignbox" class="form-container" method="post">
                                    <div class="row" id="transferclienting">
                                        <div class="col-md-12 marginbottom5px">
                                            <div class="controls">
                                            {!! Form::text('task_title', null, ['class' => 'form-control', 'id'=>'task_title','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề công việc']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-12 marginbottom5px">
                                            <div class="controls">
                                            <textarea class="form-control" id="task_description" row="5" placeholder="Nội dung công việc" name="task_description" cols="50" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {!! Form::label('group_user_id',  trans('lead.select_group_user'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                                <div class="controls">
                                                    {!! Form::select('group_user_id', $groupStaff, null, ['id'=>'group_user_id', 'class' => 'form-control select_function', 'onchange'=>'showuser(this.value)']) !!}
                                                    <span class="help-block">{{ $errors->first('page_id', ':message') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {!! Form::label('user_id',  trans('staff.staffs'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                                <div class="controls">
                                                    <select name="user_id_data_asign" id="user_id_data_asign" class="form-control select_function">
                                                        <option>Chọn nhân viên</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 marginbottom5px">
                                            <div class="controls">
                                            {!! Form::label('task_deadline', trans('task.timeline')) !!}
                                            {!! Form::text('task_deadline', null, ['class' => 'form-control datetime','id'=>'task_deadline']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group" id="assignstatus">
                                                <div id="assigning"></div>
                                                <button onclick="assignUser();" type="button" class="btn" id="chatwithuser" name="chatwithuser">Chuyển Khách hàng</button>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="transferclienting_text"></div>
                                    </form>
                                </div>
                                <div class="addtag" id="addtag"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>


    <!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <form id="searchkeyword">
        <span class="close">&times;</span>
        <input type="text" id="keyword_search" name="keyword" value="" onchange="showKeyword(this.value);"/>
        <div id="listkeyword"></div>
    </form>
    </div>
</div>


    <script language="javascript">
    window.skipped_once = false;
    function pushApp($id, $partner_id){
        $( "#lead_status"+$id).addClass( "show" );
        $( "#lead_status"+$id).removeClass( "hide" );
        $( "#boxLead"+$id).hide();
        $.ajax({
            method: "POST",
            url: "https://api.fastercrm.com/api/push_app",
            data: {lead_id: $id, partner_id: $partner_id},
            success: function(data) {
                $( "#lead_status"+$id).addClass( "hide" );
                 $( "#callstatus"+$id).html("Đã chuyển lead cho "+data.notification);
            }
        });
    }
    $(document).ready(function () {
    var dateTimeFormat = 'Y-m-d H:i';
        flatpickr("#task_deadline", {
            minDate: '{{  now() }}',
            dateFormat: dateTimeFormat,
            enableTime: true,
        });
    });
    $loadingchat=0;
    function clickChat($psid, $pageid, $lead_id){
        $getOldPSID=$('#psid').val();
        if($loadingchat==0 && $getOldPSID!=$psid){
            $(".title_staffOther ul.dropdown-menu").empty()
            $(".boxchat").empty();
            $("#is_user_chat").val(0);
            $(".boxlead").removeClass('active');
            $(".boxchat").html("<div style='float:left; width:100%; text-align:center'><img src='//api.fastercrm.com/images/loadding.gif' /></div>");
            $('#lead_id').val($lead_id);
            $loadingchat=1;
            @if($listUserAssign)
                @foreach($listUserAssign as $values)
                    @if($values["id"]!="")
                    $(".title_staffOther ul.dropdown-menu").append( "<li class=\"eborder-top\" id=\"userassign{{$values["id"]}}\"><a href=\"javascript:void(0)\" onclick=\"return transferLead('{{$values["id"]}}', '{{$values["first_name"]}} {{$values["last_name"]}}');\">{{$values["first_name"]}} {{$values["last_name"]}}</a></li>");
                    @endif
                @endforeach
            @endif
            if($lead_id>0){
                $(".asignbox").show();
                $(".boxtag").show();
                $(".psid"+$psid+" .unreadyet").remove();
                $(".psid"+$psid+" .boxinfo").removeClass('selectunread');
                $("#transferclienting").hide();
                $("#transferclienting_text").html('');
                $("#info_link ").html('');
                $.ajax({
                    type: "GET",
                    url: baseUrl+"/lead/detaillead",
                    data: {'lead_id': $lead_id, _token: '{{ csrf_token() }}'},
                    success: function (result) {
                        if(result!=""){
                            $statusList=result.statuslist;
                        $tagList=result.tagList;
                        //var res = $tag.split(",");
                        $(".psid"+$psid).remove(".unreadyet");
                        $(".psid"+$psid+" .boxinfo").removeClass('selectunread');
                        $('#task_id').val(result.task_id);
                        $('#phonemain').val(result.lead.phone);
                        $('#info_phone').html(result.lead.phone);
                        $('#info_email').html(result.lead.email);
                        $('#info_fullname').html(result.lead.opportunity);
                        if(result.lead.staff_first_name!=""  && result.lead.staff_first_name!=null){
                            $('#stafftalk').html("<span style='color:yellow'>"+result.lead.staff_first_name+" "+result.lead.staff_last_name+"</span> đang tương tác với khách hàng");
                        }else{
                            $('#stafftalk').html("Chưa có nhân viên trả lời");
                        }
                        $('#clockuser').html("<a href='javascript:void(0)'  onclick='return lockuser("+result.lead.id+");'>Chặn User này</a>");

                        if(result.lead.gim==0){
                            $('#ghimlead').html("<a href='javascript:void(0)' onclick='return ghimlead("+result.lead.id+", 1);'>Ghim Lead</a>");
                        }else{
                            $('#ghimlead').html("<a href='javascript:void(0)'  onclick='return ghimlead("+result.lead.id+", 0);'>Bỏ Ghim</a>");

                        }
                        if(result.lead.staff_care!="" && result.lead.staff_care!=null){
                            $('#userassign'+result.lead.staff_care).remove();
                        }
                        if (typeof result.pagedetail != "undefined" && result.pagedetail!="") {

                            if(result.pagedetail.title!="" && result.pagedetail.title!=null){
                                $('#info_page').html("<a href='https://fb.com/"+result.lead.page_id+"' target='_blank'>"+result.pagedetail.title+"</a>");
                            }else{
                                $('#info_page').html("<a href='https://fb.com/"+result.lead.page_id+"' target='_blank'>Link page</a>");
                            }
                        }

                        $('#info_viewmore').html("<a href='/lead/"+result.lead.id+"/edit' target='_blank'>Xem chi tiết</a>");
                        if(result.lead.URL!="" && result.lead.URL!=null){
                            $('#info_link').html("<a href='https://facebook.com"+result.lead.URL+"' target='_blank'>Chat trực tiếp trên page</a>");
                        }
                        if(result.lead.photos!="" && result.lead.photos!=null){
                            $('#info_photo').html("<img src='"+result.lead.photos+"' /> ");
                        }else{
                            $('#info_photo').html("");
                        }
                        if(result.lead.phone!=""){
                            $("#createorder").show();
                        }else{
                            $("#createorder").hide();

                        }
                        $('#fullname').val(result.lead.opportunity);
                        $('.liststatusclient').empty();
                        $.each($statusList, function (i, item) {
                            $itemStatus="<div id=\"linestatus"+item.id+"\" class=\"linestatus linestatus"+item.id+" "+item.active+"\"><span class=\"boxstatus \"><a href=\"javascript:void(0)\" onclick=\"return updateCallStatus('status', '"+item.id+"', '"+item.title+"', '"+$lead_id+"');\">"+item.title+"</a></span></div>";
                            $('.liststatusclient').append($itemStatus);
                        });

                        $('.listtagclient').empty();
                        $.each($tagList, function (i, item) {
                            $tagremove=0;
                            if(item.active=='active'){
                                $tagremove=1;
                            }
                            $itemTag="<div id=\"linetag"+item.id+"\" class=\"linestatus linestag"+item.id+" "+item.active+"\"><span class=\"boxstatus\"><a href=\"javascript:void(0)\" onclick=\"addTag('"+item.id+"', '"+item.title+"', '"+$tagremove+"');\">"+item.title+"</a></span></div>";
                            $('.listtagclient').append($itemTag);
                        });
                        if($psid>0){
                            $("#messenger").show();
                            $('#psid').val($psid);
                            $('#page_messenger_id').val(result.lead.page_id);
                            showHistoryChat($psid, 0, 0, 1, 0, 1);
                            $(".psid"+$psid).addClass('active');
                        }
                        }else{
                            alert("Lead này không tồn tại hoặc được chuyển cho người khác");
                            return false;
                        }
                       
                    }
                });
                $loadingchat=0;
            }
        }else{
            alert('Bạn đang chọn User này');
            return false;
        }

    }
    function lockuser($lead, $psid){
        if($lead!=""){
            $.ajax({
                method: "POST",
                url: "{{ url('lead/lockedupdate')}}",
                data: {lead_id: $lead, _token: '{{ csrf_token() }}'},
                success: function(data) {
                    $(".psid"+$psid).remove();
                    location.reload();
                }
             });
        }
        
    }
    function addTag($tag_id=0, $tagtext="", $remove=0){
        if($tagtext==""){
            $tag=$("#tags_text").val();
        }else{
            $tag=$tagtext;
        }
        $lead=$('#lead_id').val();
        if($lead!="" &&  $tag!=""){
            $.ajax({
                method: "POST",
                url: "{{ url('lead/addtags')}}",
                data: {lead_id: $lead, tags: $tag, tag_id: $tag_id, remove: $remove, _token: '{{ csrf_token() }}'},
                success: function(data) {
                    
                    if($tag_id>0){
                        $tagid=$tag_id;
                        $("#linetag"+$tag_id).addClass('active');
                    }else{
                        $tagid=data.tag_id;
                        $(".listtagclient").append('<div id="linetag'+data.tag_id+'" class="linestatus active linestag'+data.tag_id+'"><span class="boxstatus "><a href="javascript:void(0)">'+$tag+'</a></span></div>');
                    }
                    if($remove==1){
                        $("#linetag"+$tagid).removeClass('active');
                        $removenext=0;
                    }else{
                        $removenext=1;
                    } 
                    $("#linetag"+$tagid+" .boxstatus").html("<a href=\"javascript:void(0)\" onclick=\"addTag('"+$tagid+"', '"+$tag+"', '"+$removenext+"');\">"+$tag+"</a>");
                    $("#tags_text").val('');
                }
             });
        }
        
    }

    $(document).ready(function() {
        var table = $('#data').removeAttr('width').DataTable( {
            scrollY:        "450px",
            scrollX:        true,
            scrollCollapse: true,
            paging:         false,
            searching:      false,
            fixedColumns:   {
                leftColumns: 3,
            }
        });
    } );
    function showHistoryChat($psid, $lasttime, $lastIdChat, $scroll=0, $timeload, $firstload=0) {
        $last=0;
        $chat=0;
        $getValues=$("#is_user_chat").val();
        if($getValues==0 || $getValues==""){
            $('.formchatfix').hide();
        }
        $.ajax({
            type: "GET",
            url: baseUrl+"/lead/historychat",
            data: {'psid': $psid, 'lasttime': $lasttime, 'lastIdChat': $lastIdChat, 'timeload':$timeload, _token: '{{ csrf_token() }}'},
            dataType: 'json',
            success: function (result) {
                $lastitem=0;
                $lastId=0;
                if($firstload==1){
                    $(".boxchat").empty();
                    $(".boxchat").append('<div class="linechat" id="idchat0"></div>');
                }
                $chat=0;
                $.each(result, function (i, item) {
                    if($('#chatWeb .boxchat #idchat'+item.id).length <= 0){
                        $dataLine="";
                        if(item.title!="" && item.title!=null){
                            $messenger=item.title;
                        }else{
                            $messenger=item.messenger;
                        }
                        if(item.sender_id==$psid){
                            $light="";
                            if(item.read==0){
                                $light="boxlight";
                            }
                            $addphone="";
                            $addemail="";
                            $phonemain=$("#phonemain").val();
                            
                            if(item.extention!=""){
                                if(item.extention!=$phonemain){
                                    if($phonemain==""){
                                        updatephone(item.extention, item.id);
                                    }
                                    $addphone="&nbsp<span class='number"+item.id+"'><a href='javascript:void(0);' onclick=\"return updatephone('"+item.extention+"', "+item.id+")\" title='Thêm số điện chính cho khách hàng' class='iconsadd '>[ + Phone ]</a></span>";
                                }else if($phonemain!="" && item.extention==$phonemain){
                                    $addphone="&nbsp<a href='javascript:void(0);' class='iconsadd mainnumber'>[Số chính]</a>";
                                } 
                               
                            }

                            if(item.email!=""){
                                $addemail="&nbsp&nbsp<span class='lineemail"+item.id+"'><a href='javascript:void(0);' onclick=\"return updateemail('"+item.email+"', "+item.id+")\" title='Thêm email cho khách hàng' class='iconsadd '>[ + Email ]</a></span>";
                            }

                            fullname=$("#fullname").val();
                            $dataLine+="<div class=\"linechat client "+$light+"\" id=\"idchat"+item.id+"\">";
                            $dataLine+="<span class=\"name\"> "+fullname+" <span class=\"date\">"+item.date+"</span></span>";
                            $dataLine+="<span class=\"content\">"+$messenger+" "+$addphone+$addemail+"</span>";
                            $dataLine+="</div>";
                            //if (window.skipped_once == true) {
                                //document.getElementById('bgAudio').play();
                            //}
                            $chatnow=item.chatnow;
                            if($chatnow==1){
                                $chat=1;
                                $("#is_user_chat").val(1);
                            }
                        }else{
                            $dataLine+="<div class=\"linechat user\" id=\"idchat"+item.id+"\">";
                            $dataLine+="<span class=\"name\">{{$user_data["first_name"]}} <span class=\"date\">"+item.date+"</span></span>";
                            $dataLine+="<span class=\"content\">"+$messenger+"</span>";
                            $dataLine+="</div>";
                        }
                        if(item.idpre!="" && item.idpre>0 && $('#idchat'+item.idpre).length > 0){
                            if(item.idpre>item.id){
                                $('#chatWeb #idchat'+item.idpre).before($dataLine);
                            }else{
                                $('#chatWeb #idchat'+item.idpre).after($dataLine);
                            } 
                        }else{
                            $('#chatWeb #idchat0').before($dataLine);
                        }
                        $last++;
                    }
                    $lastitem=item.lasttime;
                    $lastId=item.id;
                });
                $getValuesShow=$("#is_user_chat").val();
                if($getValuesShow==1){
                    $('.formchatfix').show();
                }

                window.skipped_once = true;
                if($lastitem>0){
                    $('#chatWeb #lasttimechat').val($lastitem);
                }
                if($lastId>0){
                    $('#chatWeb #lastIdchat').val($lastId);
                }
                if($scroll==1 && $last>0){
                        /*
                        $('div#chatWeb').animate({
                            scrollTop: $(".boxchat .linechat").last().offset().top
                        },'slow'); */
                        $('.boxchat').animate({scrollTop: $('.boxchat').prop("scrollHeight")}, 500);
                } 

            }
    });
    }
    var facebook_messenger_id = $('#psid').val();
    $lastLoad=0;
    $loaded=0;
        setInterval(function(){
                var lasttimechat = $('#lasttimechat').val();
                //var lastIdChat = $('#lastIdchat').val();
                var lastIdChat = $('#lastIdchat').val();
                var facebook_messenger_id = $('#psid').val();
                if(facebook_messenger_id!="" && facebook_messenger_id!=0){
                    showHistoryChat(facebook_messenger_id, lasttimechat, lastIdChat, 0, $loaded);
                }
                $loaded++;
        }, 3000);

        setInterval(function(){
            var facebook_messenger_id = $('#psid').val();
            if(facebook_messenger_id!="" && facebook_messenger_id!=0){
                    $(".boxchat .linechat").removeClass("boxlight");
            }
        }, 10000);

        setInterval(function(){
            AutoLoadPageloadNewSMS();
        }, 5000);
   
    function submitMessenger(){ 
            var facebook_messenger_id = $('#psid').val();
            var page_id = $('#page_messenger_id').val();
            var partner_id = '{{$user_data["partner_id"]}}';
            var content = $('#chatdesc').val();
            var lasttimechat = $('#lasttimechat').val();
            var lastIdChat = $('#lastIdchat').val();
            var user_id = $('#user_id').val();
            var lead_id = $('#lead_id').val();

            content = content.replace(/(?:\r\n|\r|\n)/g, '<br>');
            var content = content.replaceAll("<br>", "\\n");
            var content = content.replaceAll("<br >", "\\n");
            //var photos = $('input[name="photos[]"]').val();
            var photos = $('input[name="photos"]').map(function(){return $(this).val();}).get();
            // var photos = $('#photos').val();
            if(photos!=""){
                $("#uploads").html("<li>Đang gởi hình</li>");
            }
            $("#chatdesc").val("");
            //$('.psid'+facebook_messenger_id).remove();
            //$( "#userlist" ).prepend($($itemdata));
            if(content!="" || photos!=""){

                $.ajax({
                    type: 'POST',
                    url: '/api/addchatbox',
                    data: {'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': content, 'lead_id': lead_id, 'user_id': user_id, 'photos': photos, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        item=data;
                        $dataLine="";
                        if(data.id>0){
                            lastIdChat=data.id;
                            $('#lastIdchat').val(lastIdChat);
                        }
                        $messenger=content;
                        $dataLine+="<div class=\"linechat user\" id=\"idchat"+item.id+"\">";
                        $dataLine+="<span class=\"name\">{{$user_data["first_name"]}} <span class=\"date\">"+item.date+"</span></span>";
                        $dataLine+="<span class=\"content\"><div class='linechatcontent'>"+item.messenger+"</div></span>";
                        $dataLine+="</div>";
                        $('#chatWeb #idchat0').before($dataLine);
                        $lastitem=item.lasttime;
                        $lastId=item.id;
                        $('.boxchat').animate({scrollTop: $('.boxchat').prop("scrollHeight")}, 500);
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: 'https://api.fastercrm.com/api/sendmessenger',
                    data: {'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': content, 'user_id': user_id, 'photos': photos, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                    }
                });
            }
    }
    function submitMessengerPhoto($photo){ 
            var facebook_messenger_id = $('#psid').val();
            var page_id = $('#page_messenger_id').val();
            var partner_id = '{{$user_data["partner_id"]}}';
            var content = $('#chatdesc').val();
            var lasttimechat = $('#lasttimechat').val();
            var lastIdChat = $('#lastIdchat').val();
            var user_id = $('#user_id').val();
            var lead_id = $('#lead_id').val();
            //var photos = $('input[name="photos[]"]').val();
            var photos = $photo;
            // var photos = $('#photos').val();
            if(photos!=""){
                $("#uploads").html("<li>Đang gởi hình</li>");
            }
            $("#chatwithuser").hide();
            $("#chatdesc").val("");
            /*
            if(content!="" || photos!=""){ 
                $.ajax({
                type: 'POST',
                url: 'https://api.fastercrm.com/api/sendmessenger',
                data: {'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': "", 'user_id': user_id, 'photos[]': photos, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $("#uploads li").remove();
                    if(data.id>0){
                        lastIdChat=data.id;
                        $('#lastIdchat').val(lastIdChat);
                    }
                    //showHistoryChat(facebook_messenger_id, lasttimechat, lastIdChat, 1,2);
                    $("#chatwithuser").show();
                    $('.boxchat').animate({scrollTop: $('.boxchat').prop("scrollHeight")}, 500);
                }
             });
            } */
            $.ajax({
                type: 'POST',
                url: '/api/addchatbox',
                data: {'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': content, 'user_id': user_id, 'lead_id': lead_id, 'photos': photos, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    item=data;
                    $dataLine="";
                    if(data.id>0){
                        lastIdChat=data.id;
                        $('#lastIdchat').val(lastIdChat);
                    }
                    $dataLine+="<div class=\"linechat user\" id=\"idchat"+item.id+"\">";
                    $dataLine+="<span class=\"name\">{{$user_data["first_name"]}} <span class=\"date\">"+item.date+"</span></span>";
                    $dataLine+="<span class=\"content\"><div class='linechatcontent'><img src='"+item.photos+"' style=\"max-width:350px!important\"/></div></span>";
                    $dataLine+="</div>";
                    $('#chatWeb #idchat0').before($dataLine);
                    $lastitem=item.lasttime;
                    $lastId=item.id;
                    $("#chatwithuser").show();
                    $("#uploads").empty();
                        

                    $('.boxchat').animate({scrollTop: $('.boxchat').prop("scrollHeight")}, 500);
                }
            });
            $.ajax({
                type: 'GET',
                url: 'https://api.fastercrm.com/api/sendmessengerphoto',
                data: {'receive_id':facebook_messenger_id, _token: '{{ csrf_token() }}'},
                success: function (data) {}
            });
    }
    
    $('#chatdesc').bind('keypress', function(e) {

        if (e.keyCode == 13) {
            if (e.shiftKey) {
                var scope = $('#chatdesc').val();
                var content = scope.currentChat.message;
                var caret = getCaret(this);
                this.value = content.substring(0, caret) + "\n" + content.substring(caret, content.length - 1);
                e.stopPropagation();
            } else {
                e.preventDefault();
                submitMessenger();
            }
        }
           // 
    });
    $(function () {
            $('#chatwithuser').bind('click', function (event) {
                // using this page stop being refreshing 
                event.preventDefault();
                submitMessenger();
            });
            $('#createorder').bind('click', function (event) {
                $lead=$('#lead_id').val();
                $phone=$('#phonemain').val();
                
                if($phone!="")
                            window.open("https://crm.lavendervn.com/sales/create?cus="+$lead,'_blank');
                else
                    alert("Bạn cần có số điện thoại KH để tạo đơn hàng ");
                    return false;
                endif
            });

    });
    function updateCallStatus($function, $id, $title, $lead_id){
        if($lead_id==0 || $lead_id==""){
            $lead_id=$('#lead_id').val();
        }
        if($id!="" &&  $lead_id!=""){
            $(".linestatus").removeClass("active");
            $.ajax({
                type: "post",
                url: '{{ url('lead/updateclientauto')}}',
                data: {'lead_id': $lead_id, 'id': $id, 'function': $function, 'title': $title, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $(".linestatus"+$id).addClass("active");
                }
            });
        }
    }

    function filter(filter=""){
        $("#userlist").html("<div style='float:left; width:100%; text-align:center'><img src='//api.fastercrm.com/images/loadding.gif?v=11' /></div>");
        $(".filterdata").removeClass("active");
        $("#filter"+filter).addClass("active");
        $page_id=$("#page_messenger_id").val();
        $page=1;//$("#pageloading").val();
        $status=$("#status").val();
        $sales_id=$("#sales_id").val();
        $keyword=$("#keyword").val();
        $(".loadingbox").hide();
        if(filter==""){
            filter=1;
        }
        if($page_id>0){
            $.ajax({
                type: "GET",
                url: baseUrl+"/lead/pageloading",
                data: {'profilter': filter, 'page': $page, 'page_id':$page_id, 'status':$status, 'sales_id':$sales_id, 'keyword':$keyword, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $leadsListData=result.leadsList;
                    pagenext=result.pagenext;
                    $('#userlist .boxlead').remove();
                    $.each($leadsListData, function (i, item) {
                        $itemdata="";
                        $photo=item.photos;
                        $photoLink="";
                        if($photo!="" && $photo!="0" && $photo!=null){
                            $photoLink="<img src='"+$photo+"' height='45px' />";
                        }
                        $linebox="";
                         $selectbox="";
                        if(item.read!=null && item.read!=''){
                            if(item.read.read==0 ){
                                $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                $selectbox="selectunread";
                            }
                        }
                        $itemdata="<div class=\"boxlead psid"+item.psid+"\" onclick=\"clickChat('"+item.psid+"', '"+item.page_id+"', '"+item.id+"');\">"+$linebox+"<div class=\"images\">"+$photoLink+"</div><div class=\"boxinfo "+$selectbox+"\">";
                        if(item.opportunity!=""){
                            $itemdata+="<span class=\"name\">"+item.opportunity+"</span>";
                        }else{
                            $itemdata+="<span class=\"name\">Chưa xác định</span>";
                        }
                        $itemdata+="<span class=\"note\">"+item.messenger+"</span>";
                            
                        $itemdata+="<span class=\"phone\">"+item.phone+"</span></div></div>";
                        $('#userlist').prepend($itemdata);
                    });
                    $("#pageloading").val(pagenext);
                    $(".loadingbox").show();
                }
            });
        }

    }


    function pageloaduser(fillter="", $pageload=0){
        $("#userload").html("<div style='float:left; width:100%; text-align:center'><img src='//api.fastercrm.com/images/loadding.gif?v=1' /></div>");
        $page_id=$("#page_messenger_id").val();
        $page=$("#pageloading").val();
        $status=$("#status").val();
        $sales_id=$("#sales_id").val();
        $keyword=$("#keyword").val();
        $(".loadingbox").hide();
        if($pageload==1){
            $page=1;
        }else{
            if($page==1){
                $page=2;
            }
        }

        if(fillter=="" || fillter=="1"){
            fillter=1;
        }
        $(".loadingbox").hide();

       // if($page_id>0){
            $.ajax({
                type: "GET",
                url: baseUrl+"/lead/pageloading",
                data: {'fillter': fillter, 'page': $page, 'page_id':$page_id, 'status':$status, 'sales_id':$sales_id, 'keyword':$keyword, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $leadsListData=result.leadsList;
                    pagenext=result.pagenext;
                    $('#userload').empty();
                    $i=0;
                    $psid0=0;
                    $item0=0;
                    $.each($leadsListData, function (i, item) {
                         $i++;
                        if($i==1){
                            $psid0=item.psid;
                            $item0=item.id;

                        }

                        $itemdata="";
                        $photo=item.photos;
                        $photoLink="";
                        if($photo!="" && $photo!="0" && $photo!=null){
                            $photoLink="<img src='"+$photo+"' height='45px' />";
                        }
                        $linebox="";
                         $selectbox="";
                        if(item.new_inbox!=null && item.new_inbox!=''){
                            if(item.new_inbox==1 ){
                                $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                $selectbox="selectunread";
                            }
                        }
                        $itemdata="<div class=\"boxlead psid"+item.psid+"\" onclick=\"clickChat('"+item.psid+"', '"+item.page_id+"', '"+item.id+"');\">"+$linebox+"<div class=\"images\">"+$photoLink+"</div><div class=\"boxinfo "+$selectbox+"\">";
                        if(item.opportunity!=""){
                            $itemdata+="<span class=\"name\">"+item.opportunity+"</span>";
                        }else{
                            $itemdata+="<span class=\"name\">Chưa xác định</span>";
                        }
                        $itemdata+="<span class=\"note\">"+item.messenger+"</span>";
                        $itemdata+="<span class=\"phone\">"+item.phone+"</span></div></div>";
                        
                        if($('.psid'+item.psid).length <= 0){
                           $('#userload').before($itemdata);
                        }
                        /*
                        else{
                            if($('.psid'+item.psid).hasClass( "active" )){
                                if($('.psid'+item.psid+' .unreadyet').length <= 0){
                                    $('.psid'+item.psid).append($linebox);
                                }
                            }else{
                                $('.psid'+item.psid).append($linebox); 
                            }
                        }  */


                        
                        
                    });
                    if($psid0>=0 && $item0>0){
                        clickChat($psid0, $page_id, $item0);
                    }
                    $("#pageloading").val(pagenext);
                   
                    //if(pagenext<=totalpage){
                        $(".loadingbox").show();
                    //}
                }
            });
      //  }

    }

    function AutoLoadPageload(){
        $page_id=$("#page_messenger_id").val();
        $status=$("#status").val();
        $sales_id=$("#sales_id").val();
        $keyword=$("#keyword").val();
        if($keyword==""){
            $page=1;
        // $(".loadingbox").hide();
        // if($page_id>0){
                $.ajax({
                    type: "GET",
                    url: baseUrl+"/lead/pageloading",
                    data: {'page': $page, 'page_id':$page_id, 'status':$status, 'sales_id':$sales_id, 'keyword':$keyword, _token: '{{ csrf_token() }}'},
                    success: function (result) {
                        $leadsListData=result.leadsList;
                        pagenext=result.pagenext;
                        $boom=0;
                        $.each($leadsListData, function (i, item) {
                            $itemdata="";
                            $photo=item.photos;
                            $photoLink="";
                            if($photo!="" && $photo!="0" && $photo!=null){
                                $photoLink="<img src='"+$photo+"' height='45px' />";
                            }
                            $linebox="";
                            $selectbox="";
                            /*
                            if(item.read!=null && item.read!=''){
                                console.log(item.read.read);
                                if(item.read.read==0 ){
                                    $boom++;
                                    $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                    $selectbox="selectunread";
                                }
                            }*/

                            //if(item.read!=null && item.read!=''){
                                if(item.read==1){
                                    $boom++;
                                    $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                    $selectbox="selectunread";
                                }
                        //  }
                            
                            $itemdata="<div class=\"boxlead psid"+item.psid+"\" onclick=\"clickChat('"+item.psid+"', '"+item.page_id+"', '"+item.id+"');\">"+$linebox+"<div class=\"images\">"+$photoLink+"</div><div class=\"boxinfo "+$selectbox+"\">";
                            if(item.opportunity!=""){
                                $itemdata+="<span class=\"name\">"+item.opportunity+"</span>";
                            }else{
                                $itemdata+="<span class=\"name\">Chưa xác định</span>";
                            }
                            $itemdata+="<span class=\"note\">"+item.messenger+"</span>";
                            
                            $itemdata+="<span class=\"phone\">"+item.phone+"</span></div></div>";
                            $( "#userlist" ).prepend($($itemdata));
                            
                            if($('.psid'+item.psid).hasClass( "active" )){
                                $('.psid'+item.psid).remove();
                            }

                            /*
                            if($('.psid'+item.psid).length <= 0){
                                $('#userlist .active').before($itemdata);
                            }else{
                                if($('.psid'+item.psid).hasClass( "active" )){
                                    if($('.psid'+item.psid+' .unreadyet').length <= 0){
                                        $('.psid'+item.psid).append($linebox);
                                    }
                                }else{
                                    $('.psid'+item.psid).append($linebox); 
                                }
                            } */
                        });
                        if (window.skipped_once == true && $boom>=1) {
                            document.getElementById('bgAudio').play();
                        }
                        // $("#pageloading").val(pagenext);
                        // $(".loadingbox").show();
                    }
                });
        // }
        }

    }
    pageloaduser(1, 1);

    function AutoLoadPageloadNewSMS(){
        $page_id=$("#page_messenger_id").val();
        $status=$("#status").val();
        $sales_id=$("#sales_id").val();
        $keyword=$("#keyword").val();
        if($keyword==""){
            $page=1;
                $.ajax({
                    type: "GET",
                    url: baseUrl+"/lead/messengerloading",
                    data: {'page': $page, 'page_id':$page_id, 'status':$status, 'sales_id':$sales_id, 'keyword':$keyword, _token: '{{ csrf_token() }}'},
                    dataType: 'json',
                    success: function (result) {
                        $leadsListData=result.leadsList;
                        pagenext=result.pagenext;
                        $boom=0;
                        $.each($leadsListData, function (i, item) {
                            $itemdata="";
                            $photo=item.photos;
                            $photoLink="";
                            if($photo!="" && $photo!="0" && $photo!=null){
                                $photoLink="<img src='"+$photo+"' height='45px' />";
                            }
                            $linebox="";
                            $selectbox="";
                            //if(item.read!=null && item.read!=''){
                            if(item.read==1){
                                $boom++;
                                $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                $selectbox="selectunread";
                            }
                            $psidrun=$("#psid").val();
                            if($psidrun==item.psid){
                                $("#is_user_chat").val(1);
                            }
                        //  }
                            
                            $itemdata="<div class=\"boxlead psid"+item.psid+"\" onclick=\"clickChat('"+item.psid+"', '"+item.page_id+"', '"+item.id+"');\">"+$linebox+"<div class=\"images\">"+$photoLink+"</div><div class=\"boxinfo "+$selectbox+"\">";
                            if(item.opportunity!=""){
                                $itemdata+="<span class=\"name\">"+item.opportunity+"</span>";
                            }else{
                                $itemdata+="<span class=\"name\">Chưa xác định</span>";
                            }
                            if(item.title!=""){
                                $itemdata+="<span class=\"note\">"+item.title+"</span>";
                            }else{
                                $itemdata+="<span class=\"note\">"+item.messenger+"</span>";
                            }
                            $itemdata+="<span class=\"phone\">"+item.phone+"</span></div></div>";
                            $('.psid'+item.psid).remove();
                            //$('#userlist').before($itemdata);
                            $( "#userlist" ).prepend($($itemdata));
                            /*
                            if($('.psid'+item.psid).length <= 0){
                            //  $( "#userlist" ).prepend($($itemdata));
                                $('#userlist .active').before($itemdata);
                            }else{
                                if($('.psid'+item.psid).hasClass( "active" )){
                                    if($('.psid'+item.psid+' .unreadyet').length <= 0){
                                        $('.psid'+item.psid).append($linebox);
                                    }
                                }else{
                                    $('.psid'+item.psid).append($linebox); 
                                }
                            }*/
                        });
                        if (window.skipped_once == true && $boom>=1) {
                            document.getElementById('bgAudio').play();
                        }
                    //  $("#pageloading").val(pagenext);
                        //$(".loadingbox").show();
                    }
                });
        // }
        }

    }


    function showuser($groupid){
        if($groupid!=""){
            $("#user_id_data_asign").empty();
            $.ajax({
                method: "get",
                url: "{{ url('groupuser/user_group')}}",
                data: {group_id: $groupid, _token: '{{ csrf_token() }}'},
                success: function(data) {
                    if(data){
                        $.each(data, function (i, item) {
                            $("#user_id_data_asign").append('<option value="'+item.id+'">'+item.fullname+'</option>');
                        })
                    }
                }
             });
        }
        
    }
    function assignUser(){
        $("#transferclienting").hide();
        $("#transferclienting_text").html("Đang chuyển khách hàng");
        $user_fullname=$("#user_id_data_asign :selected").text(); // The text content of the selected option
        $user_to=$("#user_id_data_asign :selected").val(); 
        $lead=$('#lead_id').val();
        $task_title=$('#task_title').val();
        $task_description=$('#task_description').val();
        $task_deadline=$('#task_deadline').val();
        $task_id=$('#task_id').val();
        
        $group_id=$("#group_user_id :selected").val(); 
        if($lead!="" &&  $user_to!=""){
            $.ajax({
                type: "post",
                url: '{{ url('lead/assignlead')}}',
                data: {'task_from_id': $task_id, 'lead_id': $lead, 'user_to': $user_to, 'group_id': $group_id, 'user_fullname': $user_fullname, 'task_title': $task_title, 'task_description': $task_description, 'task_deadline': $task_deadline, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $loaded=0;
                    setInterval(function(){
                        if($loaded<=20){
                            checkstatusassign($loaded);
                        }else{
                        }
                        $loaded++;
                    }, 10000);
                    $("#transferclienting_text").html("Đang đợi chấp nhận chúng tôi sẽ thông báo cho bạn khi "+$user_fullname+" chấp nhận yêu cầu. Hoặc xem tình trạng tai <a href='/lead/assign'>đây</a>");
                }
            });
        }else{
            return false;
        }
       
    }

    function transferLead($userid, $userfull){
        $user_fullname=$userfull; // The text content of the selected option
        $('.title_staffOther .dropdown-menu').removeClass('selectassign');
        $user_to=$userid; 
        $lead=$('#lead_id').val(); 
        if($lead!="" &&  $user_to!=""){
            $.ajax({
                type: "post",
                url: '{{ url('lead/assignlead')}}',
                data: {'task_from_id': '0', 'lead_id': $lead, 'type_assign': 0, 'user_to': $user_to, 'group_id': '{{$user_data["group_id"]}}', 'user_fullname': $user_fullname, 'task_title': "{{trans('lead.chat_title_assign')}}", 'task_description': "{{trans('lead.chat_desc_assign')}}", 'task_deadline': "{{date('Y-m-d H:i:s')}}", _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $loaded=0;
                    $('#userassign'+$userid).html('Đã chuyển lead');
                    $('#userassign'+$userid).addClass('selectassign');
                }
            });
        }else{
            return false;
        }
       
    }

    function checkstatusassign($timecheck){
        $lead=$('#lead_id').val();
        if($lead>0){
            $.ajax({
                method: "POST",
                url: "{{ url('lead/checksattusassign')}}",
                data: {lead_id: $lead, timecheck: $timecheck, _token: '{{ csrf_token() }}'},
                success: function(data) {
                    if(data.status>0){
                        $("#assignstatus").html("Hoàn thành");
                        $("#assigning").html("");
                    }
                }
             });
        }
    }


    function updatephone($phone, $idline){
            var lead_id = $('#lead_id').val();
            var user_id = '{{$user_data["id"]}}';
            var partner_id = '{{$user_data["partner_id"]}}';
            var phone = $phone;
            if(phone!=""){
                $.ajax({
                type: 'POST',
                url: '{{ url('lead/updatephone')}}',
                data: {'lead_id': lead_id, 'phone': phone, 'partner_id': partner_id, 'user_id': user_id, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('.number'+$idline).html('<a href="javascript:void(0);" title="Số DT chính" class="iconsadd">[Số KH]</a>');
                    $(".mainnumber").val(phone);
                    $('#info_phone').html(phone);
                    return true;
                }
             });
            }
            
    }

    function showboxphone(){
        $(".boxphoneupdate").show();
        $("#showboxphone").hide();
    }
    function hideboxphone(){
        $(".boxphoneupdate").hide();
        $("#showboxphone").show();
    }

    function updatenewphone(){
        var phone = $('#new_phone').val();
        var lead_id = $('#lead_id').val();
        var user_id = '{{$user_data["id"]}}';
        var partner_id = '{{$user_data["partner_id"]}}';
        if(phone!=""){
            $.ajax({
            type: 'POST',
            url: '{{ url('lead/updatephone')}}',
            data: {'lead_id': lead_id, 'phone': phone, 'partner_id': partner_id, 'user_id': user_id, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $(".mainnumber").val(phone);
                $('#info_phone').html(phone);
                hideboxphone();
                return true;
            }
            });
        }else{
            alert("Số điện thoại không được rỗng");
            return false;
        }
    }

    function updateemail($email, $idline){
            var lead_id = $('#lead_id').val();
            var user_id = '{{$user_data["id"]}}';
            var partner_id = '{{$user_data["partner_id"]}}';
            var email = $email;
            if(email!=""){
                $.ajax({
                type: 'POST',
                url: '{{ url('lead/updateemail')}}',
                data: {'lead_id': lead_id, 'email': email, 'partner_id': partner_id, 'user_id': user_id, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('#info_email').html(email);
                    $('.lineemail'+$idline).html('<a href="javascript:void(0);" title="Email KH" class="iconsadd">[Email KH]</a>');
                    return true;
                }
             });
            }
            
    }
    
    function updateStatus($function, $id, $title, $lead_id){
        if($id!="" &&  $lead_id!=""){
            $(".linestatus").removeClass("active");
            $.ajax({
                type: "post",
                url: '{{ url('lead/updateclientauto')}}',
                data: {'lead_id': $lead_id, 'id': $id, 'function': $function, 'title': $title, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $(".linestatus"+$id).addClass("active");
                }
            });
        }
    }
    
    @if(isset($psid0) && $psid0!="")
    clickChat('{{trim($psid0)}}', '{{$pages_id0}}', '{{$lead_id0}}');
    @endif

    function acceptlead($assignid){
        var $accountAcceptDialog = $('#confirm-accept');
        $accountAcceptDialog[0].close();
        if($assignid>0){
            $.ajax({
                method: "POST",
                url: "{{ url('lead/receivelead')}}",
                data: {approve: $assignid,  _token: '{{ csrf_token() }}'},
                success: function(data) {
                    
                    if(data.success==1){
                        if(data.leadDetail.psid>0){
                            clickChat(data.leadDetail.psid, data.leadDetail.page_id,  data.leadDetail.id);
                        }else{
                            location.href="{{ url('lead')}}/chat?lead="+data.leadDetail.id;
                        }
                    }else{
                        alert(data.messenger);
                        location.href="{{ url('lead')}}/chat";
                    } 
                }
            });
        }
    }
    @if(isset($approve) && $approve!="" && $leadDetail!="")
    (function($) {
        'use strict';
        var $accountAcceptDialog = $('#confirm-accept');
        $accountAcceptDialog[0].showModal();
        $('#cancel').on('click', function() {
            $accountAcceptDialog[0].close();
        });

    })(jQuery);
    @endif

    function _upload(){
        document.getElementById('file_upload_id').click();
    } 

    function deleteimages($name, $date, $id){
        if($name!="" && $date!="" && $id!=""){
            $("#"+$id).remove();
            $.ajax({
                type: "POST",
                url:  "https://api2.fastercrm.com/upload/delete.php",
                data: {'name': $name, 'date': $date, 'id': $id},
                dataType: "json",
                success: function (data) {
                }
            });
        }else{
            return false;
        }
        return true;
    }
    var display = $("#uploads");
    var droppable = $("#drop")[0];
    $.ajaxSetup({
        context: display,
    // contentType:"application/json",
    // dataType:"json",
        beforeSend: function (jqxhr, settings) {
        }
    });

    var processFiles = function processFiles(event) {
        event.preventDefault();
        var form_data = new FormData();
        var files = event.target.files || event.dataTransfer.files;
        var images = $.map(files, function (file, i) {
            var reader = new FileReader();
            var dfd = new $.Deferred();
            reader.onload = function (e) {
                dfd.resolveWith(file, [e.target.result])
            };
            reader.readAsDataURL(new Blob([file], {
                "type": file.type
            }));
            return dfd.then(function (data) {
                form_data.append('file', data);
                return $.ajax({
                    type: "POST",
                    url: "https://api2.fastercrm.com/upload/index.php",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    dataType: "json",
                    xhr: function () {
                        var uploads = this.context;
                        var progress = this.context.find("progress:last");
                        var xhrUpload = $.ajaxSettings.xhr();
                        if (xhrUpload.upload) {
                            xhrUpload.upload.onprogress = function (evt) {
                                progress.attr({
                                        "max": evt.total,
                                        "value": evt.loaded
                                })
                            };
                            xhrUpload.upload.onloadend = function (evt) {
                                var progressData = progress.eq(-1)
                                var img = new Image;
                                $(img).addClass(progressData.eq(-1)
                                .attr("class"));
                                img.onload = function () {
                                    if (this.complete) {
                                    console.log(
                                        progressData.data("name")
                                        + " preview loading..."
                                    );
                                    };

                                };
                            // uploads.append("<br /><li>", img, "</li><br />");
                            };
                        }
                        return xhrUpload;
                    }
                })
                .then(function (data, textStatus, jqxhr) {
                    //$("#uploads").append("<li id='"+data.id+"'><input type='hidden' name='photos[]' value='"+data.photo+"'/>"+data.name+" <a href=\"javascript:void(0);\" onclick=\"deleteimages(\'"+data.name+"\', '"+data.date+"', '"+data.id+"');\" class=\"deleteimages\">Xóa</a></li>");
                    submitMessengerPhoto(data.photo);
                    return data;
                }, function (jqxhr, textStatus, errorThrown) {
                    console.log(errorThrown);
                    return errorThrown
                });
            })
        });
        $.when.apply(display, images).then(function () {
            var result = $.makeArray(arguments);
            console.log(result.length, "uploads complete");
        }, function err(jqxhr, textStatus, errorThrown) {
            console.log(jqxhr, textStatus, errorThrown)
        })
    };
    $(document).on("change", "input[name^=file]", processFiles);



    function showKeyword($keyword) {
        $last=0;
        $("#listkeyword").html();
        $.ajax({
            type: "GET",
            url: baseUrl+"/libcontent/searchcontent",
            data: {'keyword': $keyword, _token: '{{ csrf_token() }}'},
            success: function (result) {
                $lastitem=0;
                $lastId=0;
                datalist=result.data;
                $listkeyword="";
                $.each(datalist, function (i, item) {
                    $id=item.id;
                    $title=item.title;
                    $type=item.type;
                    $content=item.content; 
                    $listkeyword+="<p id='desc_content"+$id+"' style='display:none'>"+$content+"</p><p>"+$title+" <a href=\"javascript:void(0)\" onclick=\"return copyToClipboard(\'#desc_content"+$id+"\');\">Chọn nội dung</a></p>";
                });
                $("#listkeyword").html($listkeyword);
            }
        });
    }

    // Get the modal
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("copycontent");
    var span = document.getElementsByClassName("close")[0];
    btn.onclick = function() {
        modal.style.display = "block";
        //showKeyword('');
    }
    span.onclick = function() {
        modal.style.display = "none";
    }
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    var div = document.querySelector('.boxtextchat');
    var ta =  document.querySelector('textarea#chatdesc');
    ta.addEventListener('keydown', autosize);
    function autosize() {
        setTimeout(function() {
            ta. style.cssText = 'height:0px';
            var height = Math.min(20 * 5, ta.scrollHeight);
            div.style.cssText = 'height:' + height + 'px';
            ta.style.cssText = 'height:' + height + 'px';
        },0);
    } 
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).html()).select();
        document.execCommand("copy");
        $temp.remove();
        content=$(element).html();
        content = content.replace(/(?:\r\n|\r|\n)/g, '<br>');
        var content = content.replaceAll("<br>", "\n");
        var content = content.replaceAll("<br >", "\n");
        document.getElementById("chatdesc").value= content;
    }

    function getCaret(el) {
        if (el.selectionStart) {
            return el.selectionStart;
        } else if (document.selection) {
            el.focus();
            var r = document.selection.createRange();
            if (r == null) {
                return 0;
            }
            var re = el.createTextRange(),
                rc = re.duplicate();
            re.moveToBookmark(r.getBookmark());
            rc.setEndPoint('EndToStart', re);
            return rc.text.length;
        }
        return 0;
    }

    function custrtring($text, $char) {
        return $text; 
        /*
        if($text!=""){

            if($char=="" || $char==0){
            $char=20;
            }
            text = $text.substring(0, $char);
        }else{
            $text="";
        }
        return text; */
    }
    function ghimlead($lead_id, $ghim=0) {
        $last=0;
        $("#listkeyword").html();
        $.ajax({
            type: "POST",
            url: baseUrl+"/lead/ghimlead",
            data: {'lead_id': $lead_id, 'ghim': $ghim, _token: '{{ csrf_token() }}'},
            success: function (result) {
                if(result.success==0){
                    alert(result.mess);
                    return false;
                }else{
                    if($ghim==1){
                        $('#ghimlead').html("<a href='javascript:void(0)'  onclick='return ghimlead("+$lead_id+", 0);'>Bỏ ghim</a>");
                    }else{
                        $('#ghimlead').html("<a href='javascript:void(0)'  onclick='return ghimlead("+$lead_id+", 1);'>Ghim lead</a>");

                    }
                }
                


            }
        });
    }

    function reporttags() {
        var $lead_id = $('#lead_id').val();
        $last=0;
        if($lead_id=="" || $lead_id==0){
            alert("Chưa chọn lead");
        }else{
            $.ajax({
                type: "POST",
                url: baseUrl+"/lead/reporttags",
                data: {'lead_id': $lead_id, _token: '{{ csrf_token() }}'},
                success: function (result) {
                        $('#reporttags').html("");
                }
            });
        }
      
    }
    
$(document).ready(function() { 
    $keyword=$("#keyword_search").val();
    if($keyword==""){
        showKeyword('');
    }
 });

    
    </script>
@stop