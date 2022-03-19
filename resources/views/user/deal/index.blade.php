@extends('layout2.master')
@section('title') Deal @endsection
@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.css')}}">
@endsection
@section('content')
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
                @if($leadDetail["product_name"]!="")
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
    {!! Form::open(['url' => 'deal', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
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
                             <option value="3-90" @if(isset($daterange) && $daterange=='3-90') selected @endif>3->90 Ngày</option>
                             <option value="91-120" @if(isset($daterange) && $daterange=='91-120') selected @endif>91->120 Ngày</option>
                             <option value="121-152" @if(isset($daterange) && $daterange=='121-152') selected @endif>121->152 Ngày</option>
                             <option value="153-183" @if(isset($daterange) && $daterange=='153-183') selected @endif>153->183 Ngày</option>
                             <option value="184-214" @if(isset($daterange) && $daterange=='184-214') selected @endif>184->214 Ngày</option>
                             <option value="215-245" @if(isset($daterange) && $daterange=='215-245') selected @endif>215->245 Ngày</option>
                             <option value="246-276" @if(isset($daterange) && $daterange=='246-276') selected @endif>246->276 Ngày</option>
                             <option value="277-307" @if(isset($daterange) && $daterange=='277-307') selected @endif>277->307 Ngày</option>
                             <option value="308-338" @if(isset($daterange) && $daterange=='308-338') selected @endif>308->338 Ngày</option>
                             <option value="339-365" @if(isset($daterange) && $daterange=='339-365') selected @endif>339->365 Ngày</option>
                             <option value="366" @if(isset($daterange) && $daterange=='366') selected @endif>>365 Ngày</option>
                             <!-- 
                             <option value="1" @if(isset($daterange) && $daterange==1) selected @endif>Hôm nay</option>
                             <option value="7" @if(isset($daterange) && $daterange==7) selected @endif>Trong 7 ngày</option>
                             <option value="15"  @if(isset($daterange) && $daterange==15) selected @endif>Trong 15 ngày</option>
                             <option value="30" @if(isset($daterange) && $daterange==30) selected @endif>Trong 30 ngày</option>
                             <option value="60" @if(isset($daterange) && $daterange==60) selected @endif>Trong 60 ngày</option>
                             <option value="90" @if(isset($daterange) && $daterange==90) selected @endif>Trong 90 ngày</option>
                             <option value="180" @if(isset($daterange) && $daterange==180) selected @endif>Trong 180 ngày</option>
                             <option value="270" @if(isset($daterange) && $daterange==270) selected @endif>Trong 270 ngày</option>
                             <option value="365" @if(isset($daterange) && $daterange==365) selected @endif>Trong 365 ngày</option> -->
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
                    {!! Form::label('status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls"> 
                        <select class="listselect2 form-control" name="status" id="status">  
                             <option value="">Tất cả</option>
                             @if($callStatusQuery)
                                @foreach($callStatusQuery as $key=>$value)
                                    <option value="{{$value["id"]}}" @if(isset($status) && $value["id"]==$status) selected @endif>{{$value["title"]}}</option>
                                @endforeach
                            @endif
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
                        <select name="tuongtac" id="tuongtac" class="form-control select_function">
                            <option value="0">Tất cả</option> 
                            <option value="3" @if(isset($tuongtac) && $tuongtac==3) selected @endif>Đợi chăm sóc</option>
                            <option value="1" @if(isset($tuongtac) && $tuongtac==1) selected @endif>Đã chăm sóc</option>
                            <option value="2" @if(isset($tuongtac) && $tuongtac==2) selected @endif>Chưa đến chu kỳ</option>
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
            <div class="col-md-2">
                 <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                </div>
            </div>
        </div>
        {!! Form::close() !!}
        <div class="row boxclients">
                <ul class="listclient">
                    <?php $query="&daterange=".$daterange."&tags=".$tags."&sales_id=".$sales_id."&tuongtac=".$tuongtac."&starting_date=".$date_select;?>
                    <li class="itembox"><a href="/deal?status={{$query}}">Tất cả</a></li>
                    
                    @if($callStatusQuery)
                        @foreach($callStatusQuery as $key=>$value)
                            @if(isset($_GET["status"]) && $_GET["status"]==$value["id"])
                                @php $select="#fff"; $colorItem="#ff0000"; $fontweight="bold" @endphp
                            @else
                                @php $select=$value["color_bg"]; $colorItem="#fff"; if($value["color_text"]!=""){$colorItem=$value["color_text"];} $fontweight="normal" @endphp
                            @endif 
                            <li class="itembox" @if($select!="") style="color:{{$colorItem}}; background:{{$select}}" @endif> <a href="/deal?status={{$value["id"]}}{{$query}}" @if($select!="") style="color:{{$colorItem}}; font-weight:{{$fontweight}}" @endif>{{$value["title"]}}</a></li>
                        @endforeach
                    @endif
                </ul>
           
        </div>
    </div>
       
    <div class="table-rep-plugin">
        <div class="table-responsive mb-0" data-pattern="priority-columns">
            <h4>Tổng KH: <span class="green">{{number_format($totalLead)}}</span></h4>
            <table id="data" class="table table-striped">
                <thead>
                <tr>
                    <th width="15px">{{ trans('lead.id') }}</th>
                    <th width="120px">{{ trans('lead.lead_name') }}  </th>
                    <th width="100px">{{ trans('lead.phone') }}</th>
                    <th width="100px">{{ trans('lead.email') }}</th>
                    <th width="100px">{{ trans('lead.salesperson') }}</th>
                    <th width="120px">{{ trans('lead.group_name') }}</th>
                    <th width="80px">{{ trans('lead.tags') }}</th>
                    <th width="120px">Ghi chú lần cuối</th>
                    <th width="100px">Link</th>
                    <th width="100px">{{ trans('lead.createdate') }}</th>
                    <th width="100px">{{ trans('lead.interactive_new') }}</th>
                    <th width="100px">{{ trans('lead.start_date') }}</th>
                    <th width="80px">{{ trans('lead.end_date') }}</th>
                    <th width="100px">{{ trans('lead.status') }}</th>
                    <th width="100px">{{ trans('table.actions') }}</th>
                    <th width="80px">{{ trans('lead.option') }}</th>
                </tr>
                </thead>
                <tbody>
                @if($leadsList)
                    <?php $i=0;?>
                    @foreach($leadsList as $listData)
                    <?php $i++;?>
                        <tr id="lead{{$listData['id']}}">
                            <td>{{$i}}</td>
                            <!--<td><a href="{{ url('lead/?function='.$listData['function']) }}">{{ $listData["function"] }}</a></td>-->
                            <td><a href="{{ url('deal/' .  $listData['id'] . '/edit' ) }}?ref=deal">@if($listData["opportunity"]!="") {{ $listData["opportunity"] }}  @else Chưa xác định @endif </a>
                            @if($listData["psid"]!="" && $listData["URL"]!="")<a href="https://facebook.com{{$listData["URL"]}}" target="_blank" style="color:green">&nbsp;(Chat)</a> @endif &nbsp;<a data-target="#popup-modal-note" href="javascript:void(0);" data-toggle="modal"  class="popup-modal-note poupmain" data-id="{{$listData['id']}}">Ghi chú</a>
                            </td> 
                            <td>
                            @if($user_data->extention_code!="" && $user_data->password_call_center!="")
                            <a href="javascript:void(0);" onclick="callphone('{{trim($listData["phone"])}}');">
                            @else
                            <a href="{{ url('deal/' .  $listData['id'] . '/edit' ) }}?ref=deal"> 
                            @endif {{trim($listData["phone"])}}</a>

                            @if($listData["process_note"]==1)
                            <span id='boxLeadPush{{ $listData["id"] }}'><a  href="javascript:void(0);" onclick="pushApp({{$listData['id']}}, {{$listData['partner_id']}}, {{$listData['sale_id']}})" class="show" ><span class="button_assign btn btn-success">Liên lạc gấp</span></a></span>
                            @endif
                            <br/><a data-target="#popup-modal-view-log" href="javascript:void(0);" data-toggle="modal" class="popup-modal-note-view-log poupmain" data-id="{{$listData['id']}}">Xem lịch sử</a>
                            </td>
                            <td>@if($listData["email"]!="") {{substr($listData["email"],0,10)}} @endif </td>
                            <td>
                                <div id='callstatus{{ $listData["id"] }}'>
                                @if($listData['sale_id']<=0)
                                    <span class="hide" id='lead_status{{ $listData["id"] }}'>Loading</span>
                                    <span id='boxLead{{ $listData["id"] }}'><a  href="javascript:void(0);" onclick="pushApp({{$listData['id']}}, {{$listData['partner_id']}})" class="show" ><span class="button_assign btn btn-success">{{ trans('lead.assignfor') }}</span></a></span>
                                @else
                                    <a href="{{ url('report/summary?daterange=1&sales_id='.$listData['sale_id'])}}" title="{{ trans('table.show') }}">{{ $listData["sale_name"] }}</a>
                                @endif


                                </div>
                            </td> 
                            <td><a href="{{ url('lead' ) }}?group_id={{ $listData["group_id"] }}">{{ $listData["group_name"] }}</a></td>
                            <td>
                            
                            @if($listData["tagsList"]!="")
                                @foreach($listData["tagsList"] as $listTags)
                                    <span class="cicle" style="background-color:{{$listTags["color_bg"]}}; color:{{$listTags["color_text"]}}" title="{{$listTags["title"]}}">&nbsp;</span>
                                @endforeach
                            @endif

                            </td>
                            <td>{{ $listData["logs"]['logs']}}</td>
                            <td>@if($listData["psid"]!="" && $listData["URL"]!="") <a href="https://facebook.com{{$listData["URL"]}}" target="_blank">Gởi tin</a> @else {{urldecode($listData["URL"])}} @endif</td>
                            
                            <td>{{ $listData["created_at"] }}</td>
                            <td>{{ $listData["update_at"] }}</td>

                            <td>@if($listData["tasks"]["task_start"]=="" || $listData["tasks"]["task_start"]=='0000-00-00' || (date("Y", strtotime($listData["tasks"]["task_start"]))<=2019)) <span style="color:red">Chưa lên lịch</span> @else {{date("d/m/Y H:i:s",strtotime($listData["tasks"]["task_start"]))}} @endif</td>
                            <td>
                            @if($listData["tasks"]["task_end"]=="" || $listData["tasks"]["task_end"]=='0000-00-00' || (date('Y', strtotime($listData["tasks"]["task_start"]))<=2019))
                                <span style="color:red">Chưa lên lịch</span>
                            @else 
                                @if(($listData["tasks"]["report_status"]==0 || $listData["tasks"]["report_status"]=="") && strtotime($listData["tasks"]["task_end"])<=time()) 
                                    <span style="color:red; font-weight:bold">Đã trể Deadline</span> @else <span style="color:#1641f0; font-weight:bold">{{date("d/m/Y H:i:s",strtotime($listData["tasks"]["task_end"]))}}</span> 
                                @endif 
                            @endif 
                            </td>
                            <td style="text-align: center;"> 
                            @if($listData['lead_type']==1) 
                                <span class="not_win_yet"> 
                            @elseif($listData['lead_type']==2) 
                                <span class="win"> 
                            @elseif($listData['lead_type']==3) 
                                <span class="lost">
                            @else
                                <span class="notcareyet">
                            @endif
                            @if($listData['icons']!="")
                                <img width="20px"  src="{{ asset('uploads/icons/'.$listData['icons']) }}"  title="{{ $listData['status_title'] }}"/></span>
                            @else
                                <img width="20px"  src="{{ asset('uploads/icons/call.png') }}"  title="Call"/></span>
                            @endif
                            </td>
                            <td>
                            @if(Sentinel::getUser()->hasAccess(['leads.write']) || Sentinel::inRole('admin'))
                                <a href="{{ url('deal/' .  $listData['id'] . '/edit' ) }}?ref=deal" title="{{ trans('table.edit') }}">
                                    <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                <a href="{{ url('leadcall/'.  $listData['id'] .'/' ) }}" title="{{ trans('table.calls') }}">
                                    <i class="fa fa-fw fa-phone text-primary"></i> <sup>{{ $listData["calls"] }}</sup></a>
                            @endif
                            @if(Sentinel::getUser()->hasAccess(['leads.read']) || Sentinel::inRole('admin'))
                            <a href="{{ url('deal/' .  $listData['id'] . '/show' ) }}" title="{{ trans('table.details') }}" >
                                    <i class="fa fa-fw fa-eye text-primary"></i> </a>
                            @endif
                            @if(Sentinel::getUser()->hasAccess(['leads.delete']) || Sentinel::inRole('admin'))
                            <a href="{{ url('deal/' .  $listData['id'] . '/delete' ) }}" title="{{ trans('table.delete') }}">
                                    <i class="fa fa-fw fa-trash text-danger"></i> </a>
                            @endif
                            </td>
                            <td> 
                            @if(isset($user_data) && $user_data->user_id==1)
                            <a href="javascript:void(0);" onclick="return lockuser({{$listData["id"]}});">Không phải KH</a>
                            @endif
                            @if($listData['lead_type']=="care")
                                @if(Sentinel::getUser()->hasAccess(['opportunities.write']) || Sentinel::inRole('admin'))
                                <a href="{{ url('deal/' . $listData['id'] .'/lead-lost' ) }}" class="btn btn-danger" title="{{ trans('opportunity.lost') }}">
                                        Lost</a>
                                @endif
                                @if(Sentinel::getUser()->hasAccess(['quotations.write']) && Sentinel::getUser()->hasAccess(['opportunities.write']) || Sentinel::getUser()->inRole('admin'))
                                <a href="{{ url('deal/' . $listData['id'] .'/lead-win' ) }}" class="btn btn-success m-t-10" title="{{ trans('opportunity.won') }}">Won</a>
                                @endif
                            @endif
                            @if($listData['lead_type']=="cancel" || $listData['lead_type']=="sucess")
                            {{ trans('lead.finish') }}
                            @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>

            <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                    <!-- {{ $leadsPage->links() }} -->
                    @include('layout2.paging', ['paginator' => $leadsPage, 'lastPage'=>$lastPage])
                    </div>
                </div> 
            </div>
        </div>
    </div>
    

    <div role="dialog" class="modal fade" id="popup-modal-note" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div data-role="header" data-theme="a"><strong>{{trans('task.note_calendar')}}</strong></div>
                <div role="main" class="ui-content">
                    {!! Form::open(['url' => "/lead/add_call_log", 'method' => 'post', 'files'=> true,'id'=>'logs', 'enctype'=>'multipart/form-data']) !!}
                    <input type="hidden" id="lead_id_log" name="lead_id_log" value="" />
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="controls">
                            {!! Form::text('logs_text', null, ['class' => 'form-control', 'id'=>'logs_text','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề']) !!}
                            </div>
                            <div class="controls">
                            {!! Form::textarea('logs_description', null, ['class' => 'form-control', 'id'=>'logs_description', 'placeholder'=>'Nội dung công việc']) !!}
                            </div>
                            {!! Form::label('source_id', 'Tương tác qua', ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::select('source_id', $sourceList, null, ['id'=>'source_id', 'class' => 'form-control select_function']) !!}
                                <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                            </div> 

                            <div class="controls">
                                {!! Form::label('tags', trans('Tags'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('tags', $leadGroupSource, null, ['id'=>'tags', 'class' => 'form-control select_function']) !!}
                                    <span class="help-block">{{ $errors->first('function', ':message') }}</span>

                                </div>
                        </div>

                            {!! Form::label('Tương tác', trans('Tương tác'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                <select name="client_interactive" id="client_interactive" class="form-control">
                                    <option value="1">NV Tương tác</option>
                                    <option value="2">KH tương tác lại</option>
                                </select>
                            </div>
                            <div class="controls">
                                <input type="file" multiple="true" id="photos"  name="photos[]" accept=".png,.jpg,.gif"/>
                            </div>
                        </div>
                    </div>
                     <a href="javascript:void(0);" id="add_log_calender"  class="button_upate" data-rel="back" data-transition="flow">Thêm ghi chú</a>
                    <a href="#" class="popup-modal-note-dismiss" data-rel="back">Bỏ qua</a>
                     {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


    <div role="dialog" class="modal fade" id="popup-modal-view-log" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div data-role="header" data-theme="a"><strong style="color: blueviolet;">{{trans('task.note_calendar')}}</strong></div>
                <div role="main" class="ui-content">
                    <div class="row list_of_items"></div>
                </div>
                <a href="#" class="popup-modal-view-dismiss" data-rel="back">Đóng lại</a>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script language="javascript">
    /*
    function pushApp($id, $partner_id, $user=0){
        $( "#lead_status"+$id).addClass( "show" );
        $( "#lead_status"+$id).removeClass( "hide" );
        $( "#boxLead"+$id).hide();
        $.ajax({
            method: "POST",
            url: "https://api.fastercrm.com/api/push_app",
            data: {lead_id: $id, partner_id: $partner_id, sales_person_id: $user},
            success: function(data) {
                $( "#lead_status"+$id).addClass( "hide" );
                 $( "#callstatus"+$id).html("Đã chuyển lead cho "+data.notification);
            }
        });
    } */

    function pushApp($id, $partner_id, $user=0){
        $user_fullname=""; // The text content of the selected option
        $('.title_staffOther .dropdown-menu').removeClass('selectassign');
        $user_to=0; 
        $lead=$id; 
        if($lead!=""){
            $.ajax({
                type: "post",
                url: '{{ url('lead/assignlead')}}',
                data: {'task_from_id': '0', 'lead_id': $lead, 'type_assign': 0, 'user_to': $user_to, 'group_id': '{{$user_data["group_id"]}}', 'user_fullname': $user_fullname, 'task_title': "{{trans('lead.chat_title_assign')}}", 'task_description': "{{trans('lead.chat_desc_assign')}}", 'task_deadline': "{{date('Y-m-d H:i:s')}}", _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $loaded=0;
                    $( "#lead_status"+$id).addClass( "hide" );
                    $("#callstatus"+$id).html("Đã chuyển lead cho "+data.notification);
                }
            });
        }else{
            return false;
        }
       
    }
    function exportExcel(){
        $starting_date=$("#starting_date").val();
        $brand_id=$("#brand_id").val();
        $sales_id=$("#sales_id").val();
        $keyword=$("#keyword").val();
        $tags=$("#tags").val();
        @if(isset($status) && $status!="")
        $status="{{$status}}";
        @else
        $status=$("#status").val();
        @endif
        
        @if(isset($type_status) && $type_status!="")
        $type_status="{{$type_status}}";
        @else
        $type_status="";
        @endif
        
        $function=$("#function").val();
        $group_id=$("#group_id").val();
        document.location.href = "/{{$type}}/lead-export?type=xlsx&brand_id="+$brand_id+"&starting_date="+$starting_date+"&sales_id="+$sales_id+"&function="+$function+"&tags="+$tags+"&group_id="+$group_id+"&search="+$keyword+"&status="+$status+"&type_status="+$type_status;
    }
    function callphone($phone){
        window.open('/goikh/index.php?url={{$user_data->extention_code}}&password={{$user_data->password_call_center}}&phone='+$phone, 'windowName', 'width=500, height=350, left=24, top=24, scrollbars, resizable'); return false;
    } 
    function lockuser($lead){
        if($lead!=""){
            $.ajax({
                method: "POST",
                url: "{{ url('lead/lockedupdate')}}",
                data: {lead_id: $lead, _token: '{{ csrf_token() }}'},
                success: function(data) {
                    alert("Update thành công");
                    $("#lead"+$lead).remove();
                }
             });
        }
        
    }
    $(document).ready(function() {
        $('input[name="starting_date"]').daterangepicker();
   // $(".listselect2").select2();
  // Thêm các tùy chọn của bạn vào đây.
        /*
        $('#data').DataTable( {
                scrollY:        "400px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                autoWidth: true,
                fixedColumns:   {
                    leftColumns: 2,
                }
            } );
        } ); */
        /*
            var table = $('#data').removeAttr('width').DataTable( {
                scrollY:        "450px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                fixedColumns:   {
                    leftColumns: 2,
                }
            });*/
        } );

        function acceptlead($assignid){
            if($assignid>0){
                $.ajax({
                    method: "POST",
                    url: "{{ url('lead/receivelead')}}",
                    data: {approve: $assignid,  _token: '{{ csrf_token() }}'},
                    success: function(data) {
                        if(data.success==1){ 
                            location.href="{{ url('lead')}}/"+data.leadDetail.id+"/edit";
                        }else{
                            alert(data.messenger);
                            location.href="{{ url('lead')}}";
                        }
                    }
                });
            }
        }
        /*
        (function($) {
            'use strict';
            var $accountAcceptDialog = $('#confirm-accept');
            $accountAcceptDialog[0].showModal();
            $('#cancel').on('click', function() {
                $accountAcceptDialog[0].close();
            });

        })(jQuery); */
        
        function loadHistory($leadid){
            $('.list_of_items').html();
            $.ajax({
                type: "GET",
                url: baseUrl+"/lead/history",
                data: {'lead_id': $leadid, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $dataLine="";
                    $.each(result, function (i, item) {
                        const $photos=item.photos;
                        $listPhoto="";
                        if($photos!="" && $photos!="undefined" && $photos!=null){
                            const photolist = $photos.split("|");
                            for($i=0;$i<photolist.length;$i++){
                                $listPhoto+='<a href="/upload/'+photolist[$i]+'" target="_blank" class="photolist"> <img src="/upload/'+photolist[$i]+'" height="50px" /></a>';
                            }
                        }
                        $dataLine+="<div class='todolist_list showactions list1' id='"+ item.id +"'>";
                        $dataLine+="<div class='col-md-12 col-sm-12 col-xs-12'>Ngày <i>"+ item.date+"</i> <strong>&nbsp;&nbsp;" + item.description + "</strong></div>";
                            if(item.logs_description!="" && item.logs_description!=null){
                                $dataLine+="<div class='col-md-12 col-sm-12 col-xs-12 textdesc'>" + item.logs_description + "</div>";
                            }
                            if($listPhoto!=""){
                                $dataLine+="<div class='col-md-12 col-sm-12 col-xs-12 textdesc'>" + $listPhoto + "</div>";
                            }
                            $dataLine+="</div>";
                    });
                    $('.list_of_items').html($dataLine);
                }
            });
        }
        $(document).on("click", ".popup-modal-note-view-log", function() {
            var id = $(this).data('id').toString();
            loadHistory(id);
        });

        $(document).on("click", ".popup-modal-note", function() {
            var id = $(this).data('id').toString();
            
            $('input[name="lead_id_log"]').val(id);

           // loadHistory(id);
        });
        /*
        $(function () {
            $('.popup-modal-note-view-log').magnificPopup({
                type: 'inline',
                preloader: false,
                focus: 'task_title',
                modal: true
                });
                $(document).on('click', '.popup-modal-view-dismiss', function (e) {
                e.preventDefault();
                $.magnificPopup.close();
            });
            $('.popup-modal-note').magnificPopup({
                type: 'inline',
                preloader: false,
                focus: 'logs',
                modal: true
                });
                $(document).on('click', '.popup-modal-note-dismiss', function (e) {
                e.preventDefault();
                $.magnificPopup.close();
            });

        
        });*/
        $(document).ready(function()
        {  
        var submit   = $("#add_log_calender");
        submit.click(function()
        {
            //var data = $('form#logs').serialize();
         //   var photos = $('.photos').prop('files');
           // var photos = $('#photos').prop("files");
            var logs_text = $('#logs_text').val();
            var logs_description = $('#logs_description').val();
            var lead_id_log = $('#lead_id_log').val();
            var client_interactive = $('#client_interactive').val();            
            var tags = $('#tags').val();            
            var source_id = $('#source_id').val();            

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
           // form_data.append('tags', tags);
            form_data.append('client_interactive', client_interactive);
            form_data.append('source_id', source_id);
            form_data.append('tags', tags);
            form_data.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                type: "post",
                url: '{{ url('lead/add_call_log')}}',
                enctype: 'multipart/form-data',
                contentType: false,
                processData: false,
                data: form_data,
            // data:{'logs': $title, 'tags': $tags, 'lead_id': $lead_id, 'logs_description': $description, 'photos': $photos, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    alert("Thêm ghi chú thành công");
                    $.magnificPopup.close();
                  //  loadHistory(lead_id_log);
                }
            });
            return false;
        });
        //khai báo nút submit form
    }); 

  </script>
<script src="{{ URL::asset('assets/libs/admin-resources/rwd-table/rwd-table.min.js')}}"></script>
<!-- Init js-->
<script src="{{ URL::asset('assets/js/pages/table-responsive.init.js')}}"></script> 
<!-- Calendar init -->
<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ URL::asset('assets/libs/jquery.repeater/jquery.repeater.min.js')}}"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script src="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/daterangepicker/daterangepicker.css')}}" />

@endsection