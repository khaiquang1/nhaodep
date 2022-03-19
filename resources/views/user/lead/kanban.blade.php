@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop
{{-- Content --}}
@section('content')

<link rel="stylesheet" href="{{ asset('admincp/jkanban/dist/jkanban.min.css') }}" />
<script src="{{ asset('admincp/jkanban/dist/jkanban.js') }}"></script>
@php
    $listCountStatus=array();
    $listCSS="";
    $i=0;
@endphp  

<script>
 var base_task_set_data = [];
    var $dataLog="";
    $(document).ready(function()
    {  
        $(document).on('click', '.popup-modal-note-dismiss', function (e) {
          e.preventDefault();
          $.magnificPopup.close();
        });
        /*
        if($dataLog!=""){
            var base_task_set = [$dataLog];
        }else{
            var base_task_set = [];
        } */
        
    });
</script>

<style>
    @if($statusListKanban)
        @foreach($statusListKanban as $value)
            .StatusBox{{$value["id"]}}{background:@if($value['color_bg']!=''){{$value["color_bg"]}}@else #4285f4 @endif;color:@if($value["color_text"]!=''){{$value["color_text"]}}@else #fff @endif }
        @endforeach
    @endif
</style>

    <div class="clearfix">
        {!! Form::open(['url' => 'lead/kanban', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
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
                    {!! Form::label('daterange',  trans('lead.daterange'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        <select class="listselect2 form-control" name="daterange" id="daterange">  
                             <option value="0">Tất cả</option>
                             <option value="1" @if(isset($daterange) && $daterange==1) selected @endif>Hôm nay</option>
                             <option value="7" @if(isset($daterange) && $daterange==7) selected @endif>Trong 7 ngày</option>
                             <option value="15"  @if(isset($daterange) && $daterange==15) selected @endif>Trong 15 ngày</option>
                             <option value="30" @if(isset($daterange) && $daterange==30) selected @endif>Trong 30 ngày</option>
                             <option value="60" @if(isset($daterange) && $daterange==60) selected @endif>Trong 60 ngày</option>
                             <option value="90" @if(isset($daterange) && $daterange==90) selected @endif>Trong 90 ngày</option>
                             <option value="180" @if(isset($daterange) && $daterange==180) selected @endif>Trong 180 ngày</option>
                             <option value="270" @if(isset($daterange) && $daterange==270) selected @endif>Trong 270 ngày</option>
                        </select>     
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('sales_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_id',  trans('lead.salesperson'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('sales_id', $salesList, null, ['id'=>'sales_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('sales_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('type_status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        <select class="listselect2 form-control" name="type_status" id="type_status">  
                             <option value="">Tất cả</option>
                             <option value="0" @if(isset($listStatusSearch) && $listStatusSearch==0) selected @endif>Đăng ký mới</option>
                             <option value="1" @if(isset($listStatusSearch) && $listStatusSearch==1) selected @endif>Chưa tương tác</option>
                             
                        </select>                
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
            
        </div>
        <div class="row">
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
                 <div class="form-group required {{ $errors->has('brand') ? 'has-error' : '' }}">
                    {!! Form::label('brand',  trans('lead.brand'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('brand_id', $brand, null, ['id'=>'brand_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('tags') ? 'has-error' : '' }}">
                    {!! Form::label('tags',  trans('lead.tags'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('tags', $leadGroupSource, null, ['id'=>'tags', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                    </div>
                </div>
            </div>
            
            
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                    {!! Form::label('grouplead',  trans('lead.group_name'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('group_id', $groupLead, $group_id, ['id'=>'group_id', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('group_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                    {!! Form::label('grouplead',  trans('lead.group_name'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        <select name="tuongtac" id="tuongtac" class="form-control select_function">
                            <option value="0">Tất cả</option> 
                            <option value="1" @if(isset($tuongtac) && $tuongtac==1) selected @endif>Tương tác</option>
                            <option value="2" @if(isset($tuongtac) && $tuongtac==2) selected @endif>KH tương tác lại</option>
                        </select>
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
    <div class="clearfix">
        <div class="row" style="margin: 0 auto;">
            @if($groupLead)
                <ul class="listclient">
                @foreach($groupLead as $key=>$value)
                    @if($key!="")
                    @php $select=""; $colorItem="" @endphp
                    @if(isset($group_id) && $group_id==$key)
                    @php $select="green"; $colorItem="#fff" @endphp
                    @endif
                    <li class="itembox" @if($select!="") style="color:{{$colorItem}}; background:{{$select}}" @endif><a href="/lead/kanban?group_id={{$key}}" @if($select!="") style="color:{{$colorItem}};" @endif>{{$value}}</a></li>
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
                        {!! Form::select('tags', (isset($tagsList)?$tagsList:$tagsList), null, ['id'=>'tags', 'class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                    </div>
                    <div class="controls">
                        <input type="file" multiple="true" id="photos"  name="photos[]" accept=".png,.jpg,.gif"/>
                    </div>
                    
                    <input type="hidden" id="status_from" value=""/>
                    <input type="hidden" id="status_to" value=""/>
                    <input type="hidden" id="lead_id" value=""/>
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
                {{ $title }} (Tổng số @if($totalLead) {{$totalLead}} @endif)
            
                <div class="pull-right" style="margin-top: -7px;">
                <a href="/lead/home" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('lead.view_list') }}</a> | 
                 @if($user_data->hasAccess(['leads.write']) || $user_data->inRole('admin'))
                <a href="/lead/create" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('lead.new') }}</a> | 
                <a href="/lead/lead-export?type=xlsx&product_id={{$product_id}}&project_id={{$project_id}}" class="btn btn-primary">
                    <i class="fa fa-plus-circle"></i> {{ trans('lead.export') }}</a>
            
                @endif
                </div>
            </h4>
        </div>
        <div style="overflow-x: scroll; height: 600px; width: 100%;">
        <div style="width: @if($statusListKanban){{count($statusListKanban)*300}}px @endif; height:600px">
            <div class="panel-body">
                <div id="myKanban"></div>
                
            </div>

        </div>
        </div>
    </div>



    <script language="javascript">
      var KanbanDoashBoard = new jKanban({
        element: "#myKanban",
        gutter: "10px",
        widthBoard: "450px",
        heighBoard: "350px",
        itemHandleOptions:{
          enabled: true,
        },
        click: function(el) {
          console.log("Trigger on all items click!");
        },
        dropEl: function(el, target, source, sibling){
          console.log(target.parentElement.getAttribute('data-id'));
          console.log(el, target, source, sibling)
        },
        buttonClick: function(el, boardId) {
          console.log(el);
          console.log(boardId);
          // create a form to enter element
          var formItem = document.createElement("form");
          formItem.setAttribute("class", "itemform");
          formItem.innerHTML =
            '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="form-group"><button type="submit" class="btn btn-primary btn-xs pull-right">Submit</button><button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancel</button></div>';
            KanbanDoashBoard.addForm(boardId, formItem);
          formItem.addEventListener("submit", function(e) {
            e.preventDefault();
            var text = e.target[0].value;
            KanbanDoashBoard.addElement(boardId, {
              title: text
            });
            formItem.parentNode.removeChild(formItem);
          });
          document.getElementById("CancelBtn").onclick = function() {
            formItem.parentNode.removeChild(formItem);
          };
        },
        itemAddOptions: {
          enabled: false,
          content: '+ Add New Card',
          class: 'custom-button',
          footer: false
        }, 
        boards: [
            @if($statusListKanban)
            @php $i=0; @endphp
            @foreach($statusListKanban as $key=>$value)
                @if($value["id"]!="" && $value["title"]!="")
                @php
                $pre=$statusListKanban->get($key-1);
                $next=$statusListKanban->get($key+1);
                $idpre="";
				if($pre){
					$idpre=$pre["id"];
				}
				$idnext="";
				if($next){
					$idnext=$next["id"];
				}
                @endphp
                    { 
                        id: "Status{{$value["id"]}}",
                        title: "{{$value["title"]}}<br/><strong class='numbertotal'>Tổng <span id='totalStatus{{$value["id"]}}'></span> KH</strong>",
                        class: "color,StatusBox{{$value["id"]}}",
                        dragTo: ["Status{{$idnext}}"],
                        item: []
                    },    
                @endif
            @endforeach
        @endif
        ]
      });
      /*
      var toDoButton = document.getElementById("addToDo");
      toDoButton.addEventListener("click", function() {
        KanbanDoashBoard.addElement("_todo", {
          title: "Test Add"
        });
      });

      var addBoardDefault = document.getElementById("addDefault");
      addBoardDefault.addEventListener("click", function() {
        KanbanDoashBoard.addBoards([
          {
            id: "_default",
            title: "Kanban Default",
            item: [
              {
                title: "Default Item"
              },
              {
                title: "Default Item 2"
              },
              {
                title: "Default Item 3"
              }
            ]
          }
        ]);
      });
     

      var removeBoard = document.getElementById("removeBoard");
      removeBoard.addEventListener("click", function() {
        KanbanDoashBoard.removeBoard("_done");
      });

      var removeElement = document.getElementById("removeElement");
      removeElement.addEventListener("click", function() {
        KanbanDoashBoard.removeElement("_test_delete");
      });

      var allEle = KanbanDoashBoard.getBoardElements("_todo");
      allEle.forEach(function(item, index) {
      });
  */
        

    $(document).ready(function()
    {
        @if($statusListKanban)
            @foreach($statusListKanban as $value)
                //$status='{{$value["id"]}}';
                if({{$value["id"]}}!="" && {{$value["id"]}}!="0"){
                    $daterange=$("#daterange").val();
                    $sales_id=$("#sales_id").val();
                    $type_status=$("#type_status").val();
                    $function=$("#function").val();
                    $brand_id=$("#brand_id").val();
                    $group_id=$("#group_id").val();
                    $tags=$("#tags").val();
                    $keyword=$("#keyword").val();
                    $starting_date=$("#starting_date").val();
                    $tuongtac=$("#tuongtac").val();
                    $("#totalStatus{{$value["id"]}}").html("<img src='//api.fastercrm.com/images/loadding.gif' />");
                    $.ajax({
                        method: "get",
                        url: "{{ url('lead/kanban_data')}}",
                        data: {status: {{$value["id"]}}, daterange: $daterange, sales_id: $sales_id, sales_id: $sales_id, type_status: $type_status, function: $function, brand_id: $brand_id, tags:$tags, group_id:$group_id, tuongtac:$tuongtac, keyword:$keyword, starting_date:$starting_date, _token: '{{ csrf_token() }}'},
                        success: function(data) {
                            if(data){ 
                                $totalDataStatus=data.length;
                                $("#totalStatus{{$value["id"]}}").html($totalDataStatus);
                                $.each(data, function (i, item) {
                                    $linkLead="{{ url('lead/')}}/"+item.id+"/edit";
                                    KanbanDoashBoard.addElement("Status{{$value["id"]}}", {
                                            id: item.id,
                                            title: "<a href='"+$linkLead+"' target='_blank'>"+item.opportunity+"</a> <span class='phone'>"+item.phone+"</span>",
                                            desc: item.status_title,
                                            drag: function(el, source) {
                                            console.log("START DRAG: " + el.dataset.eid);
                                                lead=item.id;
                                                from=0;
                                                to='{{$value["id"]}}';
                                                console.log("from: " + from);
                                                console.log("to: " + to);
                                               // if(from!="" && to!="" && lead!="" && from!=to){
                                                    $("#status_from").val(from);
                                                    $("#status_to").val(to);
                                                    $("#lead_id").val(lead);
                                                    $("#logs_text").val();
                                                    $("#logs_description").val();
                                                    $.magnificPopup.open({items: {src: '#popup-modal-note'},type: 'inline'}, 0);            
                                                //}
                                            },
                                            dragend: function(el) {
                                                console.log("END DRAG: " + el.dataset.eid);
                                            },
                                            drop: function(el) {
                                                console.log("DROPPED: " + el.dataset.eid);
                                                console.log("DROPPED1: " + el);
                                                
                                            }
                                    });
                                });
                                /*
                                    $linkLead="{{ url('lead/')}}"+item.id+"/edit";
                                    base_task_set_data.push({id:item.id, status:item.status, text:"<a href='"+$linkLead+"'>"+item.opportunity+"</a> <span>Ttt</span> <span class='email'><a href='"+$linkLead+"'>"+item.email+"</a></span>", tags:item.phone, comments:[{text:item.group_name}, {text:item.status_title}]});

                                }); */
                            }
                        }
                    }); 
                }
            @endforeach
        @endif
    });
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

    $(document).ready(function()
    {  
        var submit   = $("#add_log_calender");
        submit.click(function()
        {
            var logs_text = $('#logs_text').val();
            var logs_description = $('#logs_description').val();
            var lead_id_log = $('#lead_id').val();
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
            form_data.append('lead_id_log', lead_id_log);
            form_data.append('tags', tags);
            form_data.append('_token', '{{ csrf_token() }}');
            $("#loading").show();
            $("#loading").html("Uploading");
            $("#add_log_calender").hide();
            
            leadUpdateUrl = cmsUrl + "/lead/update_lead_status";
            $.ajax({
                type: "post",
                url: '{{ url('lead/add_call_log')}}',
                enctype: 'multipart/form-data',
                contentType: false,
                processData: false,
                data: form_data,
                success: function (data) {
                    $.ajax({
                        method: "POST",
                        url: leadUpdateUrl,
                        data: {lead_id: lead_id_log, status_from: from, status_to: to, _token: '{{ csrf_token() }}'},
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