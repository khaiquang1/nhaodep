<script type="text/javascript">
            var baseUrl = '{{ url('/') }}';
            var cmsUrl = '{{ url('/') }}';

        </script>
<meta name="viewport" content="width=device-width, initial-scale=1">
@php $version=109;@endphp 
<link rel="shortcut icon" href="{{ asset('img/fav.ico') }}" type="image/x-icon">
<link rel="icon" href="{{ asset('img/fav.ico') }}" type="image/x-icon">
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link href="{{ asset('admincp/css/ionicons.min.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/morris/morris.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/timepicker/bootstrap-timepicker.min.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('css/libs.css') }}?v=2" media="screen" rel="stylesheet" type="text/css">

<link href="{{ asset('admincp/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/jvectormap/jquery-jvectormap-1.2.2.css') }}" media="screen" rel="stylesheet" type="text/css">
 
<link href="{{ asset('admincp/css/daterangepicker/daterangepicker-bs3.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/AdminLTE.css') }}?v={{$version}}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/colorpicker/bootstrap-colorpicker.min.css') }}?v={{$version}}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/fullcalendar/fullcalendar.css') }}?v={{$version}}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/select2/select2-bootstrap.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/css/site.css') }}?v={{$version}}" media="screen" rel="stylesheet" type="text/css">
<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

<link href="{{ asset('admincp/js/plugins/tags-input/src/jquery.tagsinput.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/js/plugins/tagEditor/jquery.tag-editor.css') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/js/plugins/magnific-popup/dist/magnific-popup.css') }}" media="screen" rel="stylesheet" type="text/css">

<link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" media="screen" rel="stylesheet" type="text/css">
 
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" ></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js" ></script>
<script src="//cdn.coz.vn/bootstrap/3.3.6/js/bootstrap.min.js" ></script>
<script src="{{ asset('admincp/js/jsmap.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/input-mask/jquery.inputmask.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/input-mask/jquery.inputmask.date.extensions.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/input-mask/jquery.inputmask.extensions.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/input-mask/jquery.inputmask.numeric.extensions.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>

<script src="{{ asset('admincp/js/plugins/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('admincp/js/accounting.js') }}"></script>
<script src="{{ asset('admincp/js/AdminLTE/app.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/tags-input/src/jquery.tagsinput.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/tagEditor/jquery.caret.min.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/tagEditor/jquery.tag-editor.min.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/magnific-popup/dist/jquery.magnific-popup.js') }}"></script>
<script src="{{ asset('admincp/js/jquery.editable.min.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/jquery-html5-upload/jquery.html5_upload.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/html5-dropfile-upload/assets/js/jquery.filedrop.js') }}"></script>
<script src="{{ asset('admincp/js/plugins/webcamjs/webcam.min.js') }}"></script>
<script src="{{ asset('admincp/js/jquery.nicescroll.min.js') }}"></script>
<script src="{{ asset('admincp/js/jquery.modal_upload.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="//cdn.coz.vn/jquery-tmpl/jquery.tmpl.min.js"></script>
<script src="//cdn.coz.vn/gmaps/gmaps.min.js"></script>
<script src="//cdn.coz.vn/nicEdit/nicEdit.js"></script>

<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="//cdn.datatables.net/fixedcolumns/3.3.1/js/dataTables.fixedColumns.min.js"></script>

<link href="{{ asset('admincp/FullCalendar/packages/core/main.css?v=1') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('admincp/FullCalendar/packages/daygrid/main.css?v=1') }}" rel='stylesheet' />
<script src="{{ asset('admincp/FullCalendar/packages/core/main.js') }}"></script>
<script src="{{ asset('admincp/FullCalendar/packages/interaction/main.js') }}"></script>
<script src="{{ asset('admincp/FullCalendar/packages/daygrid/main.js') }}"></script>
<script src="//cdn.coz.vn/html5sortable/jquery.sortable.min.js"></script>
<script src="{{ asset('js/libs.js') }}"></script>
<script src="{{ asset('js/secure.js') }}"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"> </script>

