<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{trans('install.installation')}} | LCRM</title>
    @include('layouts._assets')
    @yield('styles')
    <link rel="stylesheet" href="{{ asset('css/custom_install.css') }}">
</head>

<body>
<div id="page-wrapper">
    <div>
        <div class="top_logo">
            <div class="header_padd">
                <img src="{{ url('img/logo.png') }}" alt="LCRM" class="logo center-block install_header_logo">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="wizard wizard_section">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ url(mix('js/libs.js')) }}" type="text/javascript"></script>

@yield('scripts')
</body>
</html>
