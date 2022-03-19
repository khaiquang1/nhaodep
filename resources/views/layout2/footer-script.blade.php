        <!-- JAVASCRIPT -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.0.4/popper.min.js"></script>
        <script src="{{ URL::asset('assets/libs/bootstrap/js/bootstrap.min.js')}}"></script>
        <script src="{{ URL::asset('assets/libs/metismenu/metisMenu.min.js')}}"></script>
        <script src="{{ URL::asset('assets/libs/simplebar/simplebar.min.js')}}"></script>
        <script src="{{ URL::asset('assets/libs/node-waves/waves.min.js')}}"></script>

        @yield('script')
        <!-- App js -->
        <script src="{{ URL::asset('assets/js/app.js')}}"></script>
        <script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

        @yield('script-bottom')
