@extends('layouts.chat')
{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
<audio id='bgAudio' style="display:none"> <source src='//api2.fastercrm.com/audio/boom.mp3' type='audio/mpeg'> </audio>

    <div class="clearfix">
    {!! Form::open(['url' => 'lead/comment', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
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
                Comment của khách hàng | <a href="/lead/chat"  class="boxchattitle">Chat với khách hàng</a>
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
                        <div id="userlist">
                            
                            @if($leadsList)
                                <?php $i=0;
                                $psid0="";
                                $pages_id0="";
                                $lead_id0="";
                                ?>
                                @foreach($leadsList as $listData)
                                @php 
                                if($i==0){
                                    $psid0=$listData["psid"];
                                    $pages_id0=$listData["page_id"];
                                    $lead_id0=$listData["id"];
                                    $commentid0=$listData["comment_id"];
                                    $link0=$listData["permalink_url"];

                                }
                                $i++;
                                $photoLink="";
                                if($listData["photos"]!="" && $listData["photos"]!="0"){
                                    $photoLink="<img src='".$listData["photos"]."' height='45px' />";
                                }
                                $readuncheck="";
                                if(isset($listData["read"]) && $listData["read"]==0){
                                    $readuncheck="selectunread";
                                }
                                @endphp
                                <div class="boxlead commentid{{$listData["comment_id"]}}" onclick="clickChat('{{trim($listData["comment_id"])}}', '{{trim($listData["psid"])}}', '{{$listData["page_id"]}}', '{{$listData["id"]}}', '{{$listData["permalink_url"]}}');">
                                    @if(isset($listData["read"]) && $listData["read"]==0)
                                    <span class='unreadyet'>&nbsp;</span>
                                    @endif
                                    <div class="images">{!!$photoLink!!}</div>
                                    <div class="boxinfo {{$readuncheck}}">
                                        <span class="name">@if($listData["opportunity"]!="") {{ $listData["opportunity"] }}  @else Chưa xác định @endif</span>
                                        <span class="note"> {{substr(strip_tags($listData["message"]),0,30)}}</span>
                                    </div>
                                </div>
                                <textarea style="display:none" id="content_{{trim($listData["comment_id"])}}">{{$listData["message"]}}</textarea>

                                @endforeach
                            @endif

                            <div id="userload"></div>
                           
                        </div>
                        @if($pagenext>0)
                            <span class="loadingbox" onclick="pageloaduser();">Xêm thêm</span>
                        @endif
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
                        

                        <div id="commentbox"></div>
                        <div id="chatWeb">
                            <h3 class="titlecomment"><span id="stafftalk">Trả lời KH</span></h3>
                            <div class="boxchat">
                                <div class="linechat" id="idchat0"></div>
                            </div>
                            <div class="lastrecord" id="chatLastRecord"></div>
                            <div class="formchatfix">
                                <form action="" id="messenger" class="form-container" method="post" style="display:none">
                                    <input type="hidden" id="lasttimechat" value="0" />
                                    <input type="hidden" id="lastIdchat" value="0" />
                                    <input type="hidden" id="psid" value="" />
                                    <input type="hidden" id="comment_id" value="" />

                                    <input type="hidden" id="page_messenger_id" value="" />
                                    <input type="hidden" id="phonemain" value="" />
                                    <input type="hidden" id="fullname" value="" />
                                    <input type="hidden" id="lead_id" value="" />
                                    <input type="hidden" id="task_id" value="" />
                                    <input type="hidden" id="user_id" value="{{$user_data->id}}" />
                                       
                                    <div class="boxtextchat">
                                        <input type="file" name="files" id="file_upload_id" multiple="true" accepts="image/*"  style="display:none" />
                                        <!-- <input type="textbox" placeholder="Type message.." name="msg" id="chatdesc"  required /> -->
                                        <textarea class="scrollEd ng-pristine ng-valid ng-valid-maxlength ng-touched" maxlength="1000" id="chatdesc" ng-model="currentChat.message" ng-model-options="{'updateOn': 'default blur', 'debounce': {'default': 100, 'blur': 0}}" ng-focus="currentChat.unreadMessage = 0" row="1" placeholder="Tin nhắn phản hồi"></textarea>

                                        <a href="#" class="content" id="copycontent"><img src="//api2.fastercrm.com/upload/photos/icons_question.png" width="40px" id="icon_content" class="fa fa-upload"></a>
                                    <!-- <a href="#" class="iconupload" onclick="_upload()"><img src="//api2.fastercrm.com/upload/photos/icon_upload.png" width="40px" id="icon_upload" class="fa fa-upload"></a> -->
                                    </div>
                                    <button type="submit" class="btn" id="chatwithuser" name="chatwithuser">Gởi đi</button>
                                    <input type="radio" name="comment_type" value="1" >Comment</input>
                                    <span id="comment_type_2"><input type="radio" name="comment_type" value="2">Nhắn tin</input></span>
                                    <!-- <span id="comment_type_3"><input type="radio" name="comment_type" value="3">Cả 2</input></span>-->
                                    <span id="comment_type_4"><input type="radio" name="comment_type" value="4" checked>Ẩn và trả lời Comment</input></span>

                                    <ul class="gallery-image-list" id="uploads">
                                        <input type="hidden" name="photos[]" value="" />
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
                                    <div class="iconsphone infobox">SĐT: <span id="info_phone"></span></div>
                                    <div class="linkfacebook infobox"><span id="info_link"></span></div>
                                    <div class="info_page infobox">Trang: <span id="info_page"></span></div>

                                    <div class="info_viewmore infobox"><span id="messenger_info"></span></div>

                                    
                                    <div class="info_viewmore infobox"><span id="info_viewmore" class="linkarticle"></span></div>
                                    
                                </div>
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
        <input type="text" name="keyword" value="" onchange="showKeyword(this.value);"/>
        <div id="listkeyword"></div>
    </form>
    </div>
</div>


    <script language="javascript">
    window.skipped_once = false;
    function pushApp($id, $partner_id){
        $( "#lead_status"+$id).addClass( "show" );
        $( "#lead_status"+$id).removeClass( "hide" );
        $( "#commentid"+$id).hide();
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
    function clickChat($comment_id, $psid, $pageid, $lead_id, $link_baiviet){
        $getOldCommentId=$('#comment_id').val();
        if($getOldCommentId!=$comment_id){
            $(".title_staffOther ul.dropdown-menu").empty()
            $(".boxchat").empty();
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
                $(".commentid"+$comment_id+" .unreadyet").remove();
                $(".commentid"+$comment_id+" .boxinfo").removeClass('selectunread');
                $("#transferclienting").hide();
                $("#transferclienting_text").html('');
                $("#info_link ").html('');
                $.ajax({
                    type: "GET",
                    url: baseUrl+"/lead/detailcomment",
                    data: {'comment_id': $comment_id, _token: '{{ csrf_token() }}'},
                    success: function (result) {
                        $("#commentbox").html(result.comment.message);
                        if(result.comment.reply_inbox==1){
                            $("#comment_type_2").hide();
                            $("#comment_type_3").hide();
                        }else{
                            $("#comment_type_2").show();
                            $("#comment_type_3").show();
                        }
                        $('#messenger_info').html(result.messenger);
                    }
                });

               // var contentcomment = $("#content_"+$comment_id).val()
                //var contentcomment = document.getElementById("content_"+$comment_id).val();
                $.ajax({
                    type: "GET",
                    url: baseUrl+"/lead/detaillead",
                    data: {'lead_id': $lead_id, _token: '{{ csrf_token() }}'},
                    success: function (result) {
                        $statusList=result.statuslist;
                        $tagList=result.tagList;
                        //var res = $tag.split(",");
                        $(".commentid"+$comment_id).remove(".unreadyet");
                        $(".commentid"+$comment_id+" .boxinfo").removeClass('selectunread');
                        $('#task_id').val(result.task_id);
                        $('#phonemain').val(result.lead.phone);
                        $('#info_phone').html(result.lead.phone);
                        $('#comment_id').val($comment_id);

                        $('#info_email').html(result.lead.email);
                        $('#info_fullname').html(result.lead.opportunity);
                        if(result.lead.staff_first_name!=""  && result.lead.staff_first_name!=null){
                            $('#stafftalk').html("<span style='color:yellow'>"+result.lead.staff_first_name+" "+result.lead.staff_last_name+"</span> đang tương tác với khách hàng");
                        }else{
                            $('#stafftalk').html("Chưa có nhân viên trả lời");
                        }
                        
                        $('#clockuser').html("<a href='javascript:void(0)'  onclick='return lockuser("+result.lead.id+");'>Chặn User này</a>");
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
                        
                        $('#info_viewmore').html("<a href='"+$link_baiviet+"' target='_blank'>Link bài viết</a>");
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
                        if($comment_id!=""){
                            $("#messenger").show();
                            $('#psid').val($psid);
                            $('#comment_id').val($comment_id);
                            //contentcomment
                            $('#page_messenger_id').val(result.lead.page_id);
                            showHistoryComment($comment_id, $psid, 0, 0, 1, 0, 1);
                            $(".commentid"+$comment_id).addClass('active');
                        }
                    }
                });

                $.ajax({
                    type: "post",
                    url: '{{ url('lead/updatecommentread')}}',
                    data: {'comment_id': $comment_id,  _token: '{{ csrf_token() }}'},
                    success: function (data) {
                    }
                });
                $loadingchat=0;
            }
        }else{
            alert('Đang quá trình xử lý KH');
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

    function showHistoryComment($coment_id, $psid, $lasttime, $lastIdChat, $scroll=0, $timeload, $firstload=0) {
        $last=0;
        $.ajax({
            type: "GET",
            url: baseUrl+"/lead/history_comment",
            data: {'comment_id': $coment_id, 'lasttime': $lasttime, 'lastIdChat': $lastIdChat, 'timeload':$timeload, _token: '{{ csrf_token() }}'},
            success: function (result) {
                $lastitem=0;
                $lastId=0;
                if($firstload==1){
                    $(".boxchat").empty();
                    $(".boxchat").append('<div class="linechat" id="idchat0"></div>');
                }
                console.log(result);
                $.each(result, function (i, item) {
                    if($('#chatWeb .boxchat #idchat'+item.id).length <= 0){
                        $dataLine="";
                        if(item.title!="" && item.title!=null){
                            $messenger=item.title;
                        }else{
                            $messenger=item.messenger;
                        }
                        if(item.psid==$psid){
                            $light="";
                            if(item.read==0){
                                $light="boxlight";
                            }
                            $addphone="";
                            $addemail="";
                            $phonemain=$("#phonemain").val();
                            
                            if(item.email!=""){
                                $addemail="&nbsp&nbsp<span class='lineemail"+item.id+"'><a href='javascript:void(0);' onclick=\"return updateemail('"+item.email+"', "+item.id+")\" title='Thêm email cho khách hàng' class='iconsadd '>[ + Email ]</a></span>";
                            }

                            fullname=$("#fullname").val();
                            $dataLine+="<div class=\"linechat client "+$light+"\" id=\"idchat"+item.id+"\">";
                            $dataLine+="<span class=\"name\"> "+fullname+" <span class=\"date\">"+item.date+"</span></span>";
                            $dataLine+="<span class=\"content\">"+$messenger+" "+$addphone+$addemail+"</span>";
                            $dataLine+="</div>";
                            if (window.skipped_once == true) {
                                document.getElementById('bgAudio').play();
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
                var lastIdChat = $('#lastIdchat').val();
                var facebook_messenger_id = $('#psid').val();
                var comment_id = $('#comment_id').val();

                if(comment_id!="" && comment_id!=0){
                    showHistoryComment(comment_id, facebook_messenger_id, lasttimechat, lastIdChat, 0, $loaded);
                }
                $loaded++;
        }, 15000);
        
        setInterval(function(){
            var comment_id = $('#comment_id').val();
            if(comment_id!="" && comment_id!=0){
                    $(".boxchat .linechat").removeClass("boxlight");
            }
        }, 1000);

        setInterval(function(){
            AutoLoadPageloadSMS();
        }, 10000);
   
    function submitMessenger(){ 
            var facebook_messenger_id = $('#psid').val();
            var page_id = $('#page_messenger_id').val();
            var comment_id = $('#comment_id').val();
            var partner_id = '{{$user_data["partner_id"]}}';
            var content = $('#chatdesc').val();
            var lasttimechat = $('#lasttimechat').val();
            var lastIdChat = $('#lastIdchat').val();
            var user_id = $('#user_id').val();
            content = content.replace(/(?:\r\n|\r|\n)/g, '<br>');
            var content = content.replaceAll("<br>", "\\n");
            var content = content.replaceAll("<br >", "\\n");
            //var photos = $('input[name="photos[]"]').val();
            var photos = $('input[name="photos[]"]').map(function(){return $(this).val();}).get();
            // var photos = $('#photos').val();
            if(photos!=""){
                $("#uploads").html("<li>Đang gởi hình</li>");
            }
            var comment_type = document.querySelector('input[name=comment_type]:checked').value;
            $("#chatdesc").val("");
            if(comment_type==4){
                $.ajax({
                    type: 'POST',
                    url: '/api/send_messenger_comment',
                    data: {'comment_id':comment_id, 'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': content, 'user_id': user_id, 'comment_type': comment_type, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        window.location.reload();
                    }
                });
            }
            if(content!="" || photos!=""){ 
                $.ajax({
                        type: 'POST',
                        url: '/api/addchatbox',
                        data: {'comment_id':comment_id, 'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': content, 'user_id': user_id, 'photos': photos, 'comment_type': comment_type, _token: '{{ csrf_token() }}'},
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
                    url: '/api/send_messenger_comment',
                    data: {'comment_id':comment_id, 'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'partner_id': partner_id, 'content': content, 'user_id': user_id, 'comment_type': comment_type, 'photos': photos, _token: '{{ csrf_token() }}'},
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
            var comment_id = $('#comment_id').val();
           // var comment_type = $('#comment_type').val();

            var comment_type = document.querySelector('input[name=comment_type]:checked').value;

            //var photos = $('input[name="photos[]"]').val();
            var photos = $photo;
            // var photos = $('#photos').val();
            if(photos!=""){
                $("#uploads").html("<li>Đang gởi hình</li>");
            }
            $("#chatwithuser").hide();
            $("#chatdesc").val("");
            if(content!="" || photos!=""){ 
                $.ajax({
                type: 'POST',
                url: 'https://api.fastercrm.com/api/sendmessenger',
                data: {'facebook_messenger_id': facebook_messenger_id, 'comment_type': comment_type, 'page_id': page_id, 'partner_id': partner_id, 'content': "", 'user_id': user_id, 'photos[]': photos, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $("#uploads li").remove();
                    if(data.id>0){
                        lastIdChat=data.id;
                        $('#lastIdchat').val(lastIdChat);
                    }

                    showHistoryComment(comment_id, facebook_messenger_id, lasttimechat, lastIdChat, 1,2, 0);
                    $("#chatwithuser").show();
                    $('.boxchat').animate({scrollTop: $('.boxchat').prop("scrollHeight")}, 500);
                }
             });
            }
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
    function pageloaduser(fillter="", $pageload=0){
        $("#userload").html("<div style='float:left; width:100%; text-align:center'><img src='//api.fastercrm.com/images/loadding.gif' /></div>");
        $page_id=$("#page_messenger_id").val();
        $page=$("#pageloading").val();
        $status=$("#status").val();
        $sales_id=$("#sales_id").val();
        $keyword=$("#keyword").val();
        $(".loadingbox").hide();
        if($page==1){
            $page=2;
        }
        
        if($page_id>0){
            $.ajax({
                type: "GET",
                url: baseUrl+"/lead/page_messenger_loading",
                data: {'page': $page, 'page_id':$page_id, 'status':$status, 'sales_id':$sales_id, 'keyword':$keyword, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $leadsListData=result.leadsList;
                    pagenext=result.pagenext;
                    $('#userload').empty();
                    $.each($leadsListData, function (i, item) {
                        $itemdata="";
                        $photo=item.photos;
                        $photoLink="";
                        if($photo!="" && $photo!="0" && $photo!=null){
                            $photoLink="<img src='"+$photo+"' height='45px' />";
                        }
                        $linebox="";
                        $selectbox="";
                        if(item.read==0 || item.read==null){
                                //$boom++;
                                $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                $selectbox="selectunread";
                        }

                        $itemdata="<div class=\"boxlead commentid"+item.comment_id+"\" onclick=\"clickChat('"+item.comment_id+"', '"+item.psid+"', '"+item.page_id+"', '"+item.id+"', '"+item.permalink_url+"');\">"+$linebox+"<div class=\"images\">"+$photoLink+"</div><div class=\"boxinfo "+$selectbox+"\">";
                        if(item.opportunity!=""){
                            $itemdata+="<span class=\"name\">"+item.opportunity+"</span>";
                        }else{
                            $itemdata+="<span class=\"name\">Chưa xác định</span>";
                        }
                        $itemdata+="<span class=\"note\">"+custrtring(item.message,30)+"</span>";
                        $itemdata+="<span class=\"phone\">"+item.phone+"</span></div></div>";

                        if($('.commentid'+item.comment_id).length){
                            if($('.commentid'+item.comment_id).hasClass( "active" )){
                                if($('.commentid'+item.comment_id+' .unreadyet').length <= 0){
                                    $('.commentid'+item.comment_id).append($linebox);
                                }
                            }else{
                                $('.commentid'+item.comment_id).append($linebox); 
                            }
                        }else{
                            $('#userload').before($itemdata);
                        } 


                       
                    });
                    $("#pageloading").val(pagenext);
                    $(".loadingbox").show();
                }
            });
        }

    }

   // pageloaduser(1, 1);

    function AutoLoadPageloadSMS(){
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
                    url: baseUrl+"/lead/page_messenger_loading",
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
                            if(item.read==0 || item.read==null){
                                $boom++;
                                $linebox="<span class=\"unreadyet\">&nbsp;</span>";
                                $selectbox="selectunread";
                            }
                        //  }

                            $itemdata="<div class=\"boxlead commentid"+item.comment_id+"\" onclick=\"clickChat('"+item.comment_id+"', '"+item.psid+"', '"+item.page_id+"', '"+item.id+"', '"+item.permalink_url+"');\">"+$linebox+"<div class=\"images\">"+$photoLink+"</div><div class=\"boxinfo "+$selectbox+"\">";

                            if(item.opportunity!=""){
                                $itemdata+="<span class=\"name\">"+item.opportunity+"</span>";
                            }else{
                                $itemdata+="<span class=\"name\">Chưa xác định</span>";
                            }
                            
                            $itemdata+="<span class=\"note\">"+custrtring(item.message,30)+"</span>";
                            
                            $itemdata+="</div></div>";
                            
                           // $( "#userlist" ).prepend($($itemdata));
                            /*
                            if($('.commentid'+item.comment_id).hasClass( "active" )){
                                $('.commentid'+item.comment_id).remove();
                            } */

                            if($('.commentid'+item.comment_id).length){
                                if($('.commentid'+item.comment_id).hasClass( "active" )){
                                    if($('.commentid'+item.comment_id+' .unreadyet').length <= 0){
                                        $('.commentid'+item.comment_id).append($linebox);
                                    }
                                }else{
                                    $('.commentid'+item.comment_id).append($linebox); 
                                }

                               // $( "#userlist" ).prepend($itemdata);
                            }else{
                                $('#userlist').prepend($itemdata);
                            }  
                        });
                        if (window.skipped_once == true && $boom>=1) {
                            document.getElementById('bgAudio').play();
                        }
                      //$("#pageloading").val(pagenext);
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
    clickChat('{{trim($commentid0)}}', '{{trim($psid0)}}', '{{$pages_id0}}', '{{$lead_id0}}', '{{$link0}}');
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
                            clickChat(data.leadDetail.psid.trim(), data.leadDetail.page_id,  data.leadDetail.id);
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
        showKeyword('');
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
        if($char=="" || $char==0){
            $char=20;
        }
        text="";
        //if($text!=""){
         //   text = $text.substr($char);
        //}
       
        return $text;
    }
    </script>
@stop