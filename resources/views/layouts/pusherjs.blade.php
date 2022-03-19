<div id="showalertbutton"></div>
@if(isset($user_data))
<script language="javascript">

function notificationcheck(){
    $.ajax({
            type: "GET",
            url: 'https://api.fastercrm.com/api/notification',
            data: {'user_id': {{$user_data->id}}, 'partner_id':{{$user_data->partner_id}}, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $listData=data;
                $icons="https://fastercrm.com/uploads/site/2_1575338676.png";
                if($listData.notification!=null && typeof $listData.notification.desc!== "undefined" && $listData.notification.desc!=""){
                    notifyMe($listData.notification.desc, $listData.notification.url, $icons, $listData.notification.id);
                } 
            }
    }); 

}
//setInterval(function(){ notificationcheck(); }, 35000);
setInterval(function(){ alertLead(); }, 15000);

function updatenotification($id){
        $.ajax({
            type: "POST",
            url: '/api/updatenotification',
            data: {'id': $id, _token: '{{ csrf_token() }}'},
            success: function (data) { }
        });
}
function alertLead(){
    $.ajax({
        type: "POST",
        url: '/api/alert_task',
        data: {'user_id': {{$user_data->id}}, 'partner_id':{{$user_data->partner_id}}, _token: '{{ csrf_token() }}'},
        success: function (data) {
            if(data!="" && data!=null && data.data!=null){
                $fullname="Admin";
                if(data.data.task_from_fullname!="" && data.data.task_from_fullname!=null){
                    $fullname=data.data.task_from_fullname;
                }
                $('#showalertbutton').html('<div id="confirm-accept" class="site-dialog"><header class="dialog-header"><h1>'+data.data.task_title+'</h1> </header><div class="dialog-content"><p>Người chuyển: <strong>'+$fullname+' </strong></p><p>'+data.data.task_description+'</><p><strong>Trong 30 giây bạn không nhận chúng tôi sẽ chuyển người khác</strong></p></div><div class="btn-group cf"><button class="btn btn-danger" id="accept" onclick="return acceptlead('+data.data.id+',0)">Chấp nhận</button> &nbsp;<button class="btn btn-cancel" onclick="return acceptlead('+data.data.id+',1)" id="cancel">Cancel</button></div></div>');
            }
            return true;
            
         }
    }); 
}  
function acceptlead($assignid, $cancel=0){
    if($assignid>0){
        $.ajax({
            method: "POST",
            url: "{{ url('lead/receivelead')}}",
            data: {approve: $assignid, cancel:$cancel,  _token: '{{ csrf_token() }}'},
            success: function(data) {
                console.log(data);
                if(data.success==1){
                    //alert("Cảm ơn bạn đã nhận chăm sóc lead/KH "+data.leadDetail.opportunity);
                    $('#showalertbutton').html(''); 
                }else{
                    alert(data.messenger);
                    $('#showalertbutton').html('');
                }
                return false; 
              //  location.href="{{ url('lead')}}";
            }
        });
    }
}


function notifyMe($body, $link, $icon="https://fastercrm.com/uploads/site/2_1575338676.png", $id="") {
    if (!window.Notification) {
        console.log('Browser does not support notifications.');
    } else {
        if (Notification.permission === 'granted') {
            var notify = new Notification('FasterCrm Thông báo!', {
                        body: $body,
                        icon: $icon
            });
            if($link!=""){
                notify.onclick = function() {
                    updatenotification($id);
                    window.open($link);
                };
            }
        } else {
            // request permission from user
            Notification.requestPermission().then(function (p) {
                if (p === 'granted') {
                    // show notification here
                    var notify = new Notification('FasterCrm Thông báo!', {
                        body: $body,
                        icon: $icon
                    });
                    if($link!=""){
                        notify.onclick = function() {
                            updatenotification($id);
                            window.open($link);
                        };
                    }
                    
                } else {
                    console.log('User blocked notifications.');
                }
            }).catch(function (err) {
                console.error(err);
            });
        }
    }
}
</script>
@endif