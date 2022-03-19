<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts._meta')
    {{--<!--@include('layouts._assets')-->--}}
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/login_register.css?v=11') }}" rel="stylesheet" type="text/css">
        <link rel="shortcut icon" href="{{ asset('img/fav.ico') }}" type="image/x-icon">
        <link rel="icon" href="{{ asset('img/fav.ico') }}" type="image/x-icon">
  
</head>
<body id="sign-in">
<div class="app" id="app">
    <!-- ############ LAYOUT START-->

    <!-- ############ LAYOUT END-->

    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1 signin-form">
                <div class="panel-header">
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="text-center">
                                <!-- brand -->
                                <img src="{{ asset('uploads/site/'.Settings::get('site_logo')) }}"
                                     alt="{{ Settings::get('site_name') }}" class="site_logo">
                                <!-- / brand -->
                            </h2>
                        </div>
                    </div>
                </div>
                @include('flash::message')
                @yield('content')
            </div>
        </div>
    </div>

</div>

{{-- footer scripts --}}
<script src="{{ url(mix('js/libs.js')) }}" type="text/javascript"></script>
{{-- footer scripts --}}
</body>


</html>
