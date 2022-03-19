<script type="text/javascript" src="{{ asset('admincp/js/common.js') }}?v=15"></script>
    <!-- ############ LAYOUT END-->
    @if(isset($user_data) && $user_data!="" && $user_data->extention_code!="" && $user_data->password_call_center!="" && $user_data->partner_id==12)
    <script>
                window.SETTINGS =
                {
                    display_name        : "{{$user_data->first_name}} {{$user_data->last_name}}",
                    uri                 : "{{$user_data->extention_code}}",
                    password            : "{{$user_data->password_call_center}}",
                    socket              :
                    {
                        uri           : "wss://sbcwrtchcm.ccall.vn:8080/ws",
                        via_transport : 'auto',
                    },
                    registrar_server    : null,
                    contact_uri         : null,
                    authorization_user  : null,
                    instance_id         : null,
                    session_timers      : false,
                    use_preloaded_route : false,
                    pcConfig            :
                    {
                        rtcpMuxPolicy : 'negotiate',
                        iceServers    :
                        [
                    //        { urls : [ 'stun:stun.l.google.com:19302' ] }
                        ]
                    },
                    callstats           :
                    {
                        enabled   : false,
                        AppID     : null,
                        AppSecret : null
                    }
                };
    </script>
    <script src="{{ asset('admincp/js/ua.js') }}"></script>
@endif
<div id='tryit-jssip-container'></div>
<div id='tryit-jssip-media-query-detector'></div>
@include('layouts._datatable')