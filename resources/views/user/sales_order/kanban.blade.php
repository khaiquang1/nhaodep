@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop
{{-- Content --}}
@section('content')
<script src="{{ asset('js/codebase/webix/webix.js?v=7.3.7') }}"></script>
<script src="{{ asset('js/codebase/kanban.js?v=7.3.7') }}"></script>

<link href="{{ asset('js/codebase/webix/webix.css?v=7.3.7') }}" media="screen" rel="stylesheet" type="text/css">
<link href="{{ asset('js/codebase/kanban.css?v=7.3.7') }}" media="screen" rel="stylesheet" type="text/css">
    @if($statustColor)
    <?php 
    $listCountStatus=array();
    $listCSS="";
    $i=0;
    ?>
        @foreach($statustColor as $value)
            <?php
            $i++;
            ${"list".$value["id"]}=0; 
            $color="#000";
            if($value["color_text"]!=""){
                $color=$value["color_text"];
            }
            $listCSS.=".webix_accordionitem:nth-child(".$i.") .webix_accordionitem_header{ background: ".$value["color_bg"]."; color:".$color."}";
                        
            ?>
        @endforeach
    @endif
<style>
{{$listCSS}}}
</style>
	<script >
    var base_task_set = [
        @if($salesorderList)
            @foreach($salesorderList as $value)
                @if($value["status_client"]>0 && isset(${"list".$value["status_client"]}))
                    @php ${"list".$value["status_client"]}++; @endphp
                @endif
                {id:{{$value["id"]}}, status:"{{$value["status_client"]}}", text:"<a href='{{ url('sales_order/' .  $value['id'] . '/edit' ) }}'>{{$value["customer_name"]}}</a> <span><a href='{{ url('sales_order/' .  $value['id'] . '/edit')}}'>{{$value["product_name"]}}</a></span> <span class='email'><a href='{{ url('sales_order/' .  $value['id'] . '/edit' ) }}'>{{number_format($value["final_price"])}}</a></span>", comments:[{text:"{{$value["date_ship"]}}"}, {text:"{{$value["date_exp"]}}"}]},
            @endforeach
        @endif
    ];
    </script>
    
    <div class="clearfix">
    {!! Form::open(['url' => 'sales_order/kanban', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                    {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('starting_date', isset($date_select) ? $date_select : null, ['class' => 'form-control input-sm date-input']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('name',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                    <div class="controls">
                        {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('function') ? 'has-error' : '' }}">
                    {!! Form::label('function', trans('Đến từ'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('function', $sourceList, null, ['id'=>'function', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('product') ? 'has-error' : '' }}">
                    {!! Form::label('product',  trans('product.products'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('product_id', $productList, null, ['id'=>'product_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
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
        <div class="row" style="margin-bottom: 10px;">
            @if($groupLead)
                <ul class="listclient">
                
                @foreach($groupLead as $key=>$value)
                    @if($key!="")
                    @php $select=""; $colorItem="" @endphp
                    @if(isset($_GET["group_id"]) && $_GET["group_id"]==$key)
                    @php $select="green"; $colorItem="#fff" @endphp
                    @endif

                    <li class="itembox" @if($select!="") style="color:{{$colorItem}}; background:{{$select}}" @endif><a href="/sales_order/kanban?group_id={{$key}}" @if($select!="") style="color:{{$colorItem}};" @endif>{{$value}}</a></li>
                    @endif
                @endforeach
                </ul>
            @endif
        </div>
    </div>
    <div data-role="popup" class="mfp-hide white-popup-block" id="popup-modal-note" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
        <div data-role="header" data-theme="a"><strong>{{trans('task.note_calendar')}}</strong></div>
        <div role="main" class="ui-content">
            {!! Form::open(['url' => "/lead/add_call_log", 'method' => 'post', 'files'=> true,'id'=>'logs', 'enctype'=>'multipart/form-data']) !!}
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                    {!! Form::text('logs_text', null, ['class' => 'form-control', 'id'=>'logs_text','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề']) !!}
                    </div>
                    <div class="controls">
                    {!! Form::textarea('logs_description', null, ['class' => 'form-control', 'id'=>'logs_description', 'placeholder'=>'Nội dung công việc']) !!}
                    </div>
                    {!! Form::label('tags', trans('lead.tags'), ['class' => 'control-label']) !!}
                    <div class="controls">
                    <input type="text" id="tags" class='form-control'  value="" placeholder="Tag"/>
                        <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                    </div>
                    <div class="controls">
                        <input type="file" multiple="true" id="photos"  name="photos[]" accept=".png,.jpg,.gif"/>
                    </div>
                    <input type="hidden" id="status_from" value=""/>
                    <input type="hidden" id="status_to" value=""/>
                    <input type="hidden" id="sales_order_id" value=""/>
                </div>
            </div>
            <div id="loading"> </div>  
             <a href="javascript:void(0);" id="add_log_calender"  class="button_upate" data-rel="back" data-transition="flow">Thêm ghi chú</a>
            <a href="#" class="popup-modal-note-dismiss" data-rel="back">Bỏ qua</a>
             {!! Form::close() !!}
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">thumb_up</i>
                {{ $title }} (Tổng số @if($totalOrder) {{$totalOrder}} @endif)
            
                <div class="pull-right" style="margin-top: -7px;">
                <a href="/sales_order" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('lead.view_list') }}</a>
                </div>
            </h4>
        </div>
        <div style="overflow-x: scroll; height: 500px;">
        <div style="width: @if($statustColor){{count($statustColor)*300}}px @endif; height:500px">
            <div class="panel-body">
                
                <div id="boxdata">
                <script type="text/javascript">
                    webix.type(webix.ui.kanbanlist,{
                        name: "cards",
                    });
                    
                    webix.ready(function(){
                        if (!webix.env.touch && webix.env.scrollSize)
                            webix.CustomScroll.init();
                        webix.ui({
                            view:"kanban",
                            id: "myBoard",
                            cols:[
                                @if($statustColor)
                                    @php $i=0; @endphp
                                    @foreach($statustColor as $value)
                                        @if($value["id"]!="" && $value["title"]!="")
                                            { 
                                                header:"{{$value["title"]}}",
                                                body:{ 
                                                    rows:[
                                                        {template: "<strong>Tổng @if(isset(${"list".$value["id"]})) <?php echo ${"list".$value["id"]};?> @else 0 @endif KH</strong>", height: 27},
                                                        {type: "cards", view:"kanbanlist", status:"{{$value["id"]}}", color:"#fff", class:"color{{$value["color_bg"]}}"}
                                                    ] 
                                                }
                                            },    

                                        @endif
                                    @endforeach
                                @endif
                            ],
                            data: base_task_set
                        });
                        $$("myBoard").attachEvent("onListAfterDrop", function(context,ev,list){
                            data=context.source;
                            salesorder=data[0];
                            from=context.from.P.status;
                            to=context.to.P.status;
                            if(from!="" && to!="" && salesorder!="" && from!=to){
                                $("#status_from").val(from);
                                $("#status_to").val(to);
                                $("#sales_order_id").val(salesorder);
                                $("#logs_text").val();
                                $("#logs_description").val();
                                $.magnificPopup.open({items: {src: '#popup-modal-note'},type: 'inline'}, 0);            
                            }
                        });
                      //  webix.ajax().post(leadUpdateUrl, form.getValues());

                    });
                    </script>
                </div>
            
            </div>
        </div>
        </div>
        <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                       <!-- include('layouts.paging', ['paginator' => $salesorderPage]) -->
                    </div>
                </div>
            </div>
    </div>



    <script language="javascript">
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
                 $( "#callstatus"+$id).html("Đã chuyển lead cho "+data);
            }
        });
    }
    $(function () {
        $(document).on('click', '.popup-modal-note-dismiss', function (e) {
          e.preventDefault();
          $.magnificPopup.close();
        });
    });
    $(document).ready(function()
    {  
        var submit   = $("#add_log_calender");
        submit.click(function()
        {
            var logs_text = $('#logs_text').val();
            var logs_description = $('#logs_description').val();
            var sales_order_id = $('#sales_order_id').val();
            var tags = $('#tags').val();          
            var status_from = $('#status_from').val();
            var status_to = $('#status_to').val();    
            var form_data = new FormData();
            //form_data.append('file', photos); 
            form_data.append('logs_text', logs_text);
            var ins = document.getElementById('photos').files.length;
            if(ins>0){
                for (var x = 0; x < ins; x++) {
                    form_data.append("file[]", document.getElementById('photos').files[x]);
                }
            }
            form_data.append('logs_description', logs_description);
            form_data.append('sales_order_id', sales_order_id);
            form_data.append('tags', tags);
            form_data.append('_token', '{{ csrf_token() }}');
            $("#loading").show();
            $("#loading").html("Uploading");
            $("#add_log_calender").hide();
            leadUpdateUrl = cmsUrl + "/sales_order/update_order_status";
            $.ajax({
                type: "post",
                url: '{{ url('sales_order/add_sales_order_log')}}',
                enctype: 'multipart/form-data',
                contentType: false,
                processData: false,
                data: form_data,

                success: function (data) {
                    $.ajax({
                        method: "POST",
                        url: leadUpdateUrl,
                        data: {sales_order_id: sales_order_id, status_from: from, status_to: to, _token: '{{ csrf_token() }}'},
                        success: function(data) {
                            alert("Cài đặt thành công");
                            $("#loading").hide();
                            $("#add_log_calender").show();
                        }
                    });
                    $.magnificPopup.close();
                }
            });
            return false;
        });
        //khai báo nút submit form
    }); 
    </script>
    
@stop