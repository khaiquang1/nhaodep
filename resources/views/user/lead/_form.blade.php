@section('css') 
    <!-- DataTables -->        
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/select2/css/select2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <link href="{{ URL::asset('assets/libs/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}">
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/datepicker/datepicker.min.css')}}" type="text/css">
@endsection
<audio id='bgAudio' style="display:none"> <source src='//api2.fastercrm.com/audio/boom.mp3' type='audio/mpeg'> </audio>
<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($lead))
            {!! Form::model($lead, ['url' => $type . '/' . $lead->id, 'method' => 'put', 'id'=>'lead', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true,'id'=>'lead']) !!}
        @endif

        <div class="row">
            <div class="col-md-2">
                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                    {!! Form::label('title', trans('lead.title'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder'=>'Xưng danh', 'onchange'=>"return updateselect(this.value, 'title')"]) !!}
                        <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('opportunity') ? 'has-error' : '' }}">
                    {!! Form::label('opportunity', trans('lead.opportunity'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('opportunity', null, ['class' => 'form-control', 'placeholder'=>'Tên đăng ký',  'onchange'=>"return updateselect(this.value, 'opportunity')"]) !!}
                        <span class="help-block">{{ $errors->first('opportunity', ':message') }}</span>
                    </div>
                </div> 
            </div>
            <div class="col-md-3">
                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                    {!! Form::label('email', trans('lead.email'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::email('email', null, ['class' => 'form-control', 'placeholder'=>'Email Address']) !!}
                        <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group {{ $errors->has('birthday') ? 'has-error' : '' }}">
                    {!! Form::label('birth_day', trans('lead.birthday'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        <input type="text" name="birth_day" id="birth_day" class="form-control date flatpickr-input" placeholder='@if(isset($lead) && $lead["birth_day"]!="" && $lead["birth_day"]!="0000-00-00 00:00:00"){{$lead["birth_day"]}}@endif' value="@if(isset($lead) && $lead["birth_day"]!="" && $lead["birth_day"]!="0000-00-00 00:00:00"){{$lead["birth_day"]}}@endif" />
                        <span class="help-block">{{ $errors->first('birth_day', ':message') }}</span>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group {{ $errors->has('function') ? 'has-error' : '' }}">
                    {!! Form::label('function', trans('Đến từ'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('source_id', $functions, null, ['id'=>'source_id', 'class' => 'form-control select_function', 'onchange'=>"return updateselect(this.value, 'source_id')"]) !!}
                        <span class="help-block">{{ $errors->first('function', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('product_id') ? 'has-error' : '' }}">
                    {!! Form::label('product_id', trans('lead.product_name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('product_id', $products, null, ['id'=>'product_id', 'class' => 'form-control  select2','onchange'=>"return updateselect(this.value, 'product_id')"]) !!}
                        <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
                    </div>
                </div>
               
            </div>
            <div class="col-md-2">
            @if(isset($lead) && $lead!="")
             <div class="form-group" style="margin-top: 30px;font-weight: bold;margin-bottom: 0;padding: 0;"><a href="javascript:void(0)" onclick="return addProductInterate();"> Thêm vào SP quan tâm</a><span  id="addresult"></span></div>
             @endif
            </div>
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('product_id') ? 'has-error' : '' }}">
                    {!! Form::label('group_id', trans('lead.group_name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('group_id', $groupLead, null, ['id'=>'group_id', 'class' => 'form-control select2', 'onchange'=>"return updateselect(this.value, 'group_id')"]) !!}
                        <span class="help-block">{{ $errors->first('group_id', ':message') }}</span>
                    </div>   

                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('sales_person_id') ? 'has-error' : '' }}">
                            {!! Form::label('sales_person_id', trans('lead.salesperson'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::select('sales_person_id', (isset($staffs)?$staffs:$staffs), null,['class' => 'form-control select2']) !!}
                                <span class="help-block">{{ $errors->first('sales_person_id', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('call_status') ? 'has-error' : '' }}">
                            Nhóm KH
                            <div class="controls">
                                {!! Form::select('status', $callStatus, null, ['id'=>'status', 'class' => 'form-control', 'onchange'=>"return updateselect(this.value, 'status')"]) !!}
                                <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                            </div> 
                        </div>
                    </div>
                    <div class="col-md-4">

                        <div class="form-group">
                            {!! Form::label('tags', trans('lead.tags'), ['class' => 'control-label']) !!}
                            <div class="controls listtagclient">
                            </div>
                        </div>

                    </div>

                    
                </div>
            </div>
        </div>
        @if($customFields)
        <div class="row">
           <?php // var_dump($customerFieldData);?>
            @foreach($customFields as $listCustomerFiled)
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label($listCustomerFiled["field_name"], $listCustomerFiled["field_desc"], ['class' => 'control-label']) !!}
                    <div class="controls">

                    @if($listCustomerFiled["field_type"]=="photo")
                        @php
                        $filename=$listCustomerData[$listCustomerFiled["id"]];
                        $ext = strtolower(substr($filename, strrpos($filename, '.', -1), strlen($filename)));
                        $array_images=array("jpg", "png", "gif", "jpeg",".jpg", ".png", ".gif", ".jpeg");
                        @endphp
                       <a href="@if(isset($listCustomerData) && isset($listCustomerData[$listCustomerFiled["id"]])){{$listCustomerData[$listCustomerFiled["id"]]}}@endif" target="_blank"> 
                       @if(in_array($ext,$array_images))    
                       <img src="@if(isset($listCustomerData) && isset($listCustomerData[$listCustomerFiled["id"]])){{$listCustomerData[$listCustomerFiled["id"]]}}@endif" height="200px" class="photo" />
                       @else
                       Download file
                       @endif 
                       </a>
                       <input type="hidden" name="{{$listCustomerFiled["field_name"]}}" class="form-control @if($listCustomerFiled["field_type"]=="date") datetime @endif {{$listCustomerFiled["field_type"]}}" value="@if(isset($listCustomerData) && isset($listCustomerData[$listCustomerFiled["id"]])){{$listCustomerData[$listCustomerFiled["id"]]}}@endif" placeholder="{{$listCustomerFiled["field_desc"]}}" />

                        @else
                        <input type="textbox" name="{{$listCustomerFiled["field_name"]}}" class="form-control @if($listCustomerFiled["field_type"]=="date") datetime @endif {{$listCustomerFiled["field_type"]}}" value="@if(isset($listCustomerData) && isset($listCustomerData[$listCustomerFiled["id"]])){{$listCustomerData[$listCustomerFiled["id"]]}}@endif" placeholder="{{$listCustomerFiled["field_desc"]}}" />
                        @endif

                        <span class="help-block"></span>
                    </div>
                </div>
            </div>
            
            @endforeach
        </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <hr/>
            </div>
            <div class="col-md-12">
                <h4>{{trans('lead.profile_info')}}:</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('client_name') ? 'has-error' : '' }}">
                    {!! Form::label('client_name', trans('lead.agent_name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('client_name', null, ['class' => 'form-control', 'placeholder'=>'Tên công ty', 'onchange'=>"return updateselect(this.value, 'client_name')"]) !!}
                        <span class="help-block">{{ $errors->first('client_name', ':message') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                    {!! Form::label('phone', trans('lead.phone'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('phone', null, ['class' => 'form-control mainnumber','data-fv-integer' => "true",'placeholder'=>'Phone Number']) !!}
                        <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                    {!! Form::label('mobile', trans('lead.mobile'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('mobile', null, ['class' => 'form-control','data-fv-integer' => 'true', 'placeholder'=>'Mobile number', 'onchange'=>"return updateselect(this.value, 'mobile')"]) !!}
                        <span class="help-block">{{ $errors->first('mobile', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- contact -->
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('address', trans('lead.address'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('address', null, ['class' => 'form-control resize_vertical', 'onchange'=>"return updateselect(this.value, 'address')"]) !!}
                        <span class="help-block">{{ $errors->first('address', ':message') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('city_id', trans('lead.city'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('city_id', [0=>trans('lead.select_city')], null, ['class' => 'form-control', 'onchange'=>"return updateselect(this.value, 'city_id')"]) !!}
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('district_id', trans('lead.district'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('district_id', [0=>trans('lead.select_district')], null, ['class' => 'form-control', 'onchange'=>"return updateselect(this.value, 'district_id')"]) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ward_id', trans('lead.ward'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('ward_id', [0=>trans('lead.select_ward')], null, ['class' => 'form-control', 'onchange'=>"return updateselect(this.value, 'ward_id')"]) !!}
                    </div>
                </div>
            </div> 
        </div>
        <!-- end contact -->

        <div class="row">
            <div class="col-md-12">
            {!! Form::label('additionl_info', trans('lead.additionl_info'), ['class' => 'control-label']) !!}
                <div class="form-group {{ $errors->has('additionl_info') ? 'has-error' : '' }}">
                    <div class="controls">
                        {!! Form::textarea('additionl_info', null, ['class' => 'form-control', 'placeholder'=>'Thông tin thêm', 'onchange'=>"return updateselect(this.value, 'additionl_info')"]) !!}
                        <span class="help-block">{{ $errors->first('additionl_info', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12" style="display:none">
                <div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
                    {!! Form::label('priority', trans('lead.priority'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('priority', $priority, null, ['id'=>'priority','class' => 'form-control select2', 'placeholder'=>trans('lead.select_priority'), 'onchange'=>"return updateselect(this.value, 'priority')"]) !!}
                        <span class="help-block">{{ $errors->first('priority', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($lead) && $lead!="")
            <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <meta name="_token" content="{{ csrf_token() }}">
                        <div class="card"> 
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                    data-l="true"></i>
                                    {{ trans('lead.calllist') }}
                                    <a href="javascript:void(0);" class="showhideelement" onclick="return ShowHide('boxcall', 'showhideelement');">[ + ]</a>
                                </h4>
                            </div>
                            <div class="panel-body task-body1" id="boxcall" style="display:none">
                                <div class="table-responsive">
                                    <table id="list_of_items_call" class="table table-bordered" data-id="list_of_items_call">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>{{ trans('call.salesperson') }}</th>
                                                <th>{{ trans('call.typecall') }}</th>
                                                <th>{{ trans('call.date') }}</th>
                                                <th>{{ trans('call.time_start') }}</th>
                                                <th>{{ trans('call.time_end') }}</th>
                                                <th>{{ trans('call.phone') }}</th>
                                                <th>File</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>    
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <meta name="_token" content="{{ csrf_token() }}">
                        <div class="card"> 
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                    data-l="true"></i>
                                    {{ trans('dashboard.calenderwork') }}
                                    <a href="#popup-modal" data-rel="popup"  class="popup-modal poupmain">Lên lịch làm việc</a>
                                </h4>
                            </div>
                            <div class="panel-body task-body1">
                                <div class="table-responsive">
                                    
                                    <table id="data2" class="table table-bordered" style="width:1200px">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>STT</th>
                                                <th>Tiêu đề</th>
                                                <th>Công việc</th>
                                                <th>Ghi chú</th>
                                                <th>Ngày thực hiện</th>
                                                <th>Chỉnh sửa</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list_of_items_calendar">
                                        </tbody>    
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <meta name="_token" content="{{ csrf_token() }}">
                        <div class="card"> 
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                    data-l="true"></i>
                                    {{ trans('dashboard.historyLogs') }} <span style="color:red">(Nên nhập ghi chú lịch làm viêc)</span>
                                    <a href="#popup-modal-note" data-rel="popup"  class="popup-modal-note poupmain">Ghi chú lịch sử làm việc</a>
                                </h4>
                            </div>
                            <div class="panel-body task-body1">
                                
                                <div class="row list_of_items"></div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <meta name="_token" content="{{ csrf_token() }}">
                        <div class="card"> 
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                    data-l="true"></i>
                                    {{ trans('lead.product_order') }}
                                    @if(isset($lead) && $lead->id!="")
                                    <a onclick="javascript:void(0);" data-rel=""  class="poupmain createorder">Tạo đơn hàng</a>
                                    @endif
                                </h4>
                            </div>
                            <div class="panel-body task-body1">
                                    <table id="data3" class="table table-bordered" >
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>Sản phẩm</th>
                                                <th>Số lượng đặt</th>
                                                <th>Ngày đặt</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list_of_product_order">
                                            
                                        </tbody>    
                                    </table>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="row">

                <div class="col-md-12 col-lg-12">
                    <meta name="_token" content="{{ csrf_token() }}">
                    <div class="card"> 
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                data-l="true"></i>
                                {{ trans('lead.product_interate') }}
                                @if(isset($lead) && $lead->id!="")
                                    <a onclick="javascript:void(0);" data-rel=""  class="poupmain createorder">Tạo đơn hàng</a>
                                @endif
                            </h4>
                        </div>
                        <div class="panel-body task-body1">
                                <table id="data3" class="table table-bordered" >
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Sản phẩm</th>
                                            <th>Số lượng trong kho</th>
                                            <th>Ngày quan tâm gần nhất</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list_of_product_interate">
                                        
                                    </tbody>    
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($lead) && in_array($user_data["partner_id"],array(1,14,23)))
        <div class="row">
                <div class="col-md-12 col-lg-12">
                    <meta name="_token" content="{{ csrf_token() }}">
                    <div class="card"> 
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                data-l="true"></i>
                                {{ trans('lead.linkuseraccess') }} [<a href="javascript:void(0);" onclick="return loaddataHistory();"><img src="{{asset('uploads/site/reload.png')}}" width="25px"></a>]
                            </h4>
                        </div>
                        <div class="panel-body task-body1">
                            <div class="table-responsive">
                                <table id="list_of_items_accesswebsite" class="table table-bordered" data-id="list_of_items_accesswebsite">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>{{ trans('lead.date') }}</th>
                                            <th>{{ trans('lead.url') }}</th>
                                            <th>{{ trans('lead.url_refer') }}</th>
                                            <th>{{ trans('lead.ip') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>    
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <!-- Form Actions -->
                <div class="form-group">
                    <div class="controls">
                    @if(!isset($lead))
                    <button type="submit" class="btn btn-success" form="lead"><i
                                    class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    @else
                        <button type="submit" class="btn btn-success" form="lead"><i
                                    class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    @endif
                        <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>

                    </div>
                </div>
                <!-- ./ form actions -->
            </div>
        </div>
        {!! Form::close() !!}
        <div class="boxbutton">
        @if(isset($lead))
            <button class="open-button-tranfer messengboxtitle" onclick="openForm('transferform', '')">Chuyển Khách hàng</button>
            <div class="tranfer-popup" id="transferform">
                <div class="asignbox">
                    <form action="" id="asignbox" class="form-container" method="post">
                    <div class="row" id="transferclienting">
                        <div class="col-md-12 marginbottom5px">
                            <div class="controls">
                            {!! Form::text('task_title', null, ['class' => 'form-control', 'id'=>'task_title','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề công việc']) !!}
                            </div>
                        </div>
                        <div class="col-md-12 marginbottom5px">
                            <div class="controls">
                            <textarea class="form-control" id="task_description" row="5" placeholder="Nội dung công việc" name="task_description" cols="50" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('group_user_id',  trans('lead.select_group_user'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('group_user_id', $groupStaff, null, ['id'=>'group_user_id', 'class' => 'form-control select_function', 'onchange'=>'showuser(this.value)']) !!}
                                    <span class="help-block">{{ $errors->first('page_id', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('user_id',  trans('staff.staffs'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    <select name="user_id_data_asign" id="user_id_data_asign" class="form-control select_function">
                                        <option>Chọn nhân viên</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 marginbottom5px">
                            <div class="controls">
                            {!! Form::label('task_deadline', trans('task.timeline')) !!}
                            {!! Form::text('task_deadline', null, ['class' => 'form-control datetime','id'=>'task_deadline']) !!}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group" id="assignstatus">
                                <div id="assigning"></div>
                                <button onclick="assignUser();" type="button" class="btn" name="transferuser" style="background: blue;
    color: #fff;">Chuyển Khách hàng</button>
                                <button type="button" class="btn cancel" onclick="closeForm('transferform')">Close</button>
                            </div>
                        </div>

                    </div>
                    <div id="transferclienting_text"></div>
                    </form>
                </div>
                <div class="addtag" id="addtag"></div>

            </div>



            @if(isset($lead->phone) && $lead->phone>0)
                <button class="open-button-sms messengboxtitle" onclick="openForm('smsform', 'linesms')">Nhắn tin qua SMS</button>
                <div class="sms-popup" id="smsform">
                    <div class="boxtag">
                        <h3>Trạng thái khách hàng</h3>
                        <div calss="liststatusclient">
                        @if($callStatus)
                            @foreach($callStatus as $key=>$value)
                            <div id="linestatus{{$key}}" class="linestatus @if($lead["status"]==$key) active @endif linestatus{{$key}}">
                                <span class="boxstatus" ><a href="javascript:void(0)" onclick="return updateCallStatus('status', '{{$key}}', '{{$value}}', '{{$lead->id}}');">{{$value}}</a></span>
                            </div>
                            @endforeach
                        @endif
                        </div>
                    </div>
                    <h3 class="titletuongtac">Lịch sử nhắn tin <a href="javascript:void(0);" onclick="return closeForm('smsform');"><span class="iconclose">&nbsp;&nbsp;&nbsp;&nbsp;</span></a></h3>
                    <div class="boxsms">
                        <div class="linesms" id="idsms0"></div>
                    </div>
                    <div class="lastrecord" id="smsLastRecord"></div>
                    <form action="" id="sms" class="form-container" method="post">
                        <input type="hidden" id="lasttimesms" value="0" />
                        <input type="hidden" id="lastIdsms" value="0" />
                        <textarea  placeholder="Type message.." name="msg" id="smsdesc"  required></textarea>
                        <button type="submit" class="btn" id="smswithuser" name="smswithuser">Send</button>
                        <button type="button" class="btn cancel" onclick="closeForm('smsform')">Close</button>
                    </form>
                </div>
             @endif
            @if(isset($lead->psid) && $lead->psid>0)
                <button class="open-button-chat messengboxtitle" onclick="openForm('chatform', 'linechat')">Chat với KH qua Messenger</button>
                <div class="chat-popup" id="chatform">
                    <div class="boxtag">
                        <h3>Trạng thái khách hàng</h3>
                        <div calss="liststatusclient">
                        @if($callStatus)
                            @foreach($callStatus as $key=>$value)
                            <div id="linestatus{{$key}}" class="linestatus @if($lead["status"]==$key) active @endif linestatus{{$key}}">
                                <span class="boxstatus" ><a href="javascript:void(0)" onclick="return updateCallStatus('status', '{{$key}}', '{{$value}}', '{{$lead->id}}');">{{$value}}</a></span>
                            </div>
                            @endforeach
                        @endif
                        </div>
                    </div>
                    <h3 class="titletuongtac">Tương tác với khách hàng <a href="javascript:void(0);" onclick="return closeForm('chatform');"><span class="iconclose">&nbsp;&nbsp;&nbsp;&nbsp;</span></a></h3>
                    <div class="boxchat">
                        <div class="linechat" id="idchat0"></div>
                    </div>
                    <div class="lastrecord" id="chatLastRecord"></div>
                    <form action="" id="messenger" class="form-container" method="post">
                        <input type="hidden" id="lasttimechat" value="0" />
                        <input type="hidden" id="lastIdchat" value="0" />
                        <input type="hidden" id="psid" value="{{$lead->psid}}" />
                        <input type="hidden" id="page_id" value="{{$lead->page_id}}" />
                        <input type="file" name="files" id="file_upload_id" multiple="true" accepts="image/*"  style="display:none" />
                        <input type="textbox" placeholder="Type message.." name="msg" id="chatdesc"  required />
                        <a href="#" class="iconupload" onclick="_upload()"><img src="//api2.fastercrm.com/upload/photos/icon_upload.png" width="30px" id="icon_upload" class="fa fa-upload"></a>
                        <button type="submit" class="btn" id="chatwithuser" name="chatwithuser">Send</button>
                        <button type="button" class="btn cancel" onclick="closeForm('chatform')">Close</button>
                        <ul class="gallery-image-list" id="uploads">
                            <input type="hidden" name="photos[]" value="" />
                        </ul>
                    </form>
                </div>
             @endif

             

        
        
             @endif
        </div>
    </div>
</div>
@if(isset($lead))
<div role="dialog" class="modal fade" id="popup-modal" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
    <div data-role="header" data-theme="a"><strong>Lên lịch làm việc</strong></div>
    <div role="main" class="ui-content">
        {!! Form::open(['url' => "", 'method' => 'post', 'files'=> true,'id'=>'task']) !!}
        <input type="hidden" id="lead_id" name="lead_id" value="@if(isset($lead)){{$lead->id}}@endif" />
        <div class="col-md-12">
            <div class="form-group">
                <div class="controls">
                {!! Form::text('task_title', null, ['class' => 'form-control', 'id'=>'task_title','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề công việc']) !!}
                </div>
                <div class="controls">
                {!! Form::textarea('task_description', null, ['class' => 'form-control', 'id'=>'task_description', 'placeholder'=>'Nội dung công việc']) !!}
                </div>
                <div class="controls">
                {!! Form::textarea('task_note', null, ['class' => 'form-control', 'id'=>'task_note', 'placeholder'=>'Ghi chú']) !!}
                </div>
                {!! Form::label('status', trans('lead.status'), ['class' => 'control-label required']) !!}
                <div class="controls" style="display:none">
                    {!! Form::select('status', $callStatus, null, ['id'=>'finished', 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                </div>

                {!! Form::label('sales', trans('lead.sales_takecare'), ['class' => 'control-label required']) !!}
                <div class="controls">
                    {!! Form::select('user_id', $staffs, null, ['id'=>'user_id', 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('user_id', ':message') }}</span>
                </div>
                <div class="controls">
                {!! Form::label('task_start', trans('task.timestart')) !!}
                {!! Form::text('task_start', null, ['class' => 'form-control datetime','id'=>'task_start']) !!}
                </div>
                <div class="controls">
                {!! Form::label('task_deadline', trans('task.timeline')) !!}
                {!! Form::text('task_deadline', null, ['class' => 'form-control datetime','id'=>'task_deadline']) !!}
                </div>
            </div>
        </div>
         <a href="#" onclick="return setupCalendar();" class="button_upate" data-rel="back" data-transition="flow">Cài đặt lịch</a>
        <a href="#" class="popup-modal-dismiss" data-rel="back">Bỏ qua</a>
         {!! Form::close() !!}
    </div>
</div>
<div role="popup" class="modal fade" id="popup-modal-note" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
    <div data-role="header" data-theme="a"><strong>{{trans('task.note_calendar')}}</strong></div>
    <div role="main" class="ui-content">
        {!! Form::open(['url' => "/lead/add_call_log", 'method' => 'post', 'files'=> true,'id'=>'logs', 'enctype'=>'multipart/form-data']) !!}
        <input type="hidden" id="lead_id_log" name="lead_id_log" value="@if(isset($lead)){{$lead->id}}@endif" />
        <div class="col-md-12">
            <div class="form-group">
                <div class="controls">
                {!! Form::text('logs_text', null, ['class' => 'form-control', 'id'=>'logs_text','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề']) !!}
                </div>
                <div class="controls">
                {!! Form::textarea('logs_description', null, ['class' => 'form-control', 'id'=>'logs_description', 'placeholder'=>'Nội dung công việc']) !!}
                </div>
                <!--
                {!! Form::label('tags', trans('lead.tags'), ['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::select('tags', (isset($tagsList)?$tagsList:$tagsList), null, ['id'=>'tags', 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                </div> -->
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

@endif
@section('script')
<script type="text/javascript">
window.first = 0;
window.last = 0;
window.skipped_once = false;
window.loading = false;
$running=1;
    @if(isset($lead) && $lead!="")
    var oTable;
    $(document).ready(function () {
        oTable = $('#list_of_items_call').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [],
                columns:[
                    {"data":"id"},
                    {"data":"salesperson"},
                    {"data":"typecall"},
                    {"data":"date", "className": "date number"},
                    {"data":"time_start", "className": "date number"},
                    {"data":"time_end", "className": "date number"},
                    {"data":"phone", "className": "date number"},
                    {"data":"file"},
                ],
                "ajax": "/leadcall/{{$lead["id"]}}/data"
            });
        });
        @if(isset($lead) && in_array($user_data["partner_id"],array(1,14,23)))
        var oTable2;
        $(document).ready(function () {
            oTable2 = $('#list_of_items_accesswebsite').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [],
                columns:[
                    {"data":"id"},
                    {"data":"date", "className": "date number"},
                    {"data":"url"},
                    {"data":"url_refer"},
                    {"data":"ip"},
                ],
                "ajax": "/lead/historypageaccess?cookie_id={{$lead["cookie_id"]}}&psid={{$lead["psid"]}}"
            });
        });
        /*
        setInterval( function () {
            oTable2.ajax.reload();
        }, 30000 );
        */
        function loaddataHistory(){
            oTable2.ajax.reload();
        }
    @endif
    
@endif

    /*
    $(document).ready(function() {
        $('.popup-edit').magnificPopup({
          disableOn: 700,
          type: 'iframe',
          mainClass: 'mfp-fade',
          removalDelay: 160,
          preloader: false,
          fixedContentPos: false
        });
    }); */
        

    $(function () {
            /*
        $('.popup-modal').magnificPopup({
          type: 'inline',
          preloader: false,
          focus: 'task_title',
          modal: true
        });
        $(document).on('click', '.popup-modal-dismiss', function (e) {
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
        */
        
    });
      
    $(document).ready(function () {
        
        $("#lead").bootstrapValidator({
            fields: {
                opportunity: {
                    validators: {
                        notEmpty: {
                            message: 'Tên KH không được trống.'
                        }
                    }
                },
                status: {
                    validators: {
                        notEmpty: {
                            message: 'Tình trạng KH không được trống.'
                        }
                    }
                },
                function: {
                    validators: {
                        notEmpty: {
                            message: 'Vui lòng chọn nguồn đến.'
                        }
                    }
                },
                

            }
        });
        function mainStaffChange(){
            
        }
        //mainStaffChange();
        $("#function").select2({
            theme: "bootstrap",
            placeholder: "{{ trans('lead.function') }}"
        });
        /*
        $("#title").select2({
            theme: "bootstrap",
            placeholder: "{{ trans('lead.title') }}"
        }); */
        $("#product_id").select2({
            theme: "bootstrap",
            placeholder: "{{ trans('lead.product') }}"
        });
        var dateTimeFormat = 'Y-m-d H:i';
        flatpickr("#task_deadline", {
            minDate: '{{  now() }}',
            dateFormat: dateTimeFormat,
            enableTime: true,
        });
        flatpickr("#task_start", {
            minDate: '{{  now() }}',
            dateFormat: dateTimeFormat,
            enableTime: true,
        });
        var date = 'Y-m-d';
        flatpickr("#birth_day", {
            maxDate: '{{date("Y-m-d")}}',
            dateFormat: date,
            enableTime: false,
        });
        var date = 'Y-m-d';
        flatpickr(".date", {
            dateFormat: date,
            enableTime: false,
        });
    });

    function setupCalendar() {
        $task_title=$("#task_title").val(); 
        $lead_id=$("#lead_id").val(); 
        $task_description=$("#task_description").val(); 
        $task_note=$("#task_note").val(); 
        $task_deadline=$("#task_deadline").val(); 
        $task_start=$("#task_start").val(); 

        $finished=0;//$("#finished").val(); 
        $user_id=$("#user_id").val(); 
        if($task_title!="" &&  $lead_id!=""){
            $.ajax({
                type: "post",
                url: '{{ url('task/addtasktolead')}}',
                data: {'task_title': $task_title, 'lead_id': $lead_id, 'task_description': $task_description, 'task_note': $task_note, 'task_deadline': $task_deadline, 'task_start': $task_start, 'user_id': $user_id, 'finished': $finished, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    alert("Cài đặt thành công");
                    $.magnificPopup.close();
                    loadLogCall($lead_id);
                }
            });
        }
    }
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
            $("#popup-modal-note").html("Uploading");
            $.ajax({
                type: "post",
                url: '{{ url('lead/add_call_log')}}',
                enctype: 'multipart/form-data',
                contentType: false,
                processData: false,
                data: form_data,
            // data:{'logs': $title, 'tags': $tags, 'lead_id': $lead_id, 'logs_description': $description, 'photos': $photos, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    alert("Cài đặt thành công");
                    $.magnificPopup.close();
                    loadHistory(lead_id_log);
                }
            });
            return false;
        });
        //khai báo nút submit form
    }); 

    function getcity(country) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_city_list')}}',
            data: {'id': country, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#city_id').empty();
                $('#district_id').select2({
                    theme: "bootstrap",
                    placeholder: "Select State"
                }).trigger('change');
                $('#city_id').select2({
                    theme: "bootstrap",
                    placeholder: "Select City"
                }).trigger('change');
                $.each(data, function (val, text) {
                    $('#city_id').append($('<option></option>').val(val).html(text).attr('selected', val == "{{old('city_id')}}" ? true : false));
                });
            }
        });
    }

    @if(isset($lead))
    function addProductInterate(){
        $product_id=$("#product_id").val(); 
        if($product_id!=""){
            $.ajax({
                type: "POST",
                url: baseUrl+"/lead/add_products_interest",
                data: {'lead_id': '{{$lead->id}}', 'product_id':$product_id, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $('#addresult').html("Thêm thành công");
                }  
                });
        }else{
            return false;
        }
        
    }
    @endif
    $('#city_id').change(function () {
        if($(this).val()!="" && $(this).val()!=null){
            getdistrict($(this).val());
        }
    });

    $('#group_id').change(function () {
        if($(this).val()!="" && $(this).val()!=null){
            getstatus($(this).val());
        }
    });
    function getstatus(groupid) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/statusgroup')}}',
            data: {'group_id': groupid, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#status').empty();
                $('#status').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn tình trạng"
                }).trigger('change');
                $.each(data, function (i, item) {
                   $('#status').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($lead) && $lead->id){{$lead->status}}@endif" ? true : false));
                });
            }
        }); 
    }
    
    $('#district_id').change(function () {
        if($(this).val()!="" && $(this).val()!=null){
            getward($(this).val());
        }
    });
    getcities(241);
    @if(isset($lead) && $lead->id)
    getdistrict({{$lead->city_id}});
    getward({{$lead->district_id}});
    @endif
    function getcities(country) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_city_list')}}',
            data: {'id': country, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#city_id').empty();
                $('#city_id').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn Tỉnh/Thành phố"
                }).trigger('change');
                $.each(data, function (i, item) {
                    $('#city_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($lead) && $lead->id){{$lead->city_id}}@endif" ? true : false));

                });

            }
        });  
    }
    function getdistrict(cities) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_district_list')}}',
            data: {'id': cities, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#district_id').empty();
                $('#district_id').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn Quận/Huyện"
                }).trigger('change');
                $.each(data, function (i, item) {
                   $('#district_id').append($('<option></option>').val(i).html(item).attr('selected', i== "@if(isset($lead) && $lead->id){{$lead->district_id}}@endif" ? true : false));
                });
            }
        }); 
    }
    function getward(district) {
        $.ajax({
            type: "GET",
            url: '{{ url('lead/ajax_ward_list')}}',
            data: {'id': district, _token: '{{ csrf_token() }}'},
            
            success: function (data) {
                $('#ward_id').empty();
                $('#ward_id').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn Phường/Xã"
                }).trigger('change');
                $.each(data, function (i, item) {
                    $('#ward_id').append($('<option></option>').val(i).html(item).attr('selected', i == "@if(isset($lead) && $lead->id){{$lead->ward_id}}@endif" ? true : false));
                });
            }
        }); 
    }
    @if(isset($lead) && $lead->id)
    $(document).ready(function(){
        $.ajax({
            type: "GET",
            url: baseUrl+"/lead/products_order_history",
            data: {'lead_id': '{{$lead->id}}', _token: '{{ csrf_token() }}'},
            success: function (result) {
                $.each(result, function (i, item) {
                    $i=i+1;
                    $linkDetailProduct=baseUrl+"/product/"+item.id+"/edit";
                    
                    $lineHistory="<tr><td>"+$i+"</td><td><a href='"+$linkDetailProduct+"' target='_blank'>"+item.name+"</a></td> <td class='number'>"+item.number_buy+"</td><td>"+item.date_buy+"</td></tr>";
                    
                    $('.list_of_product_order').append($lineHistory);

                });
            }                  
        });

        

        $.ajax({
            type: "GET",
            url: baseUrl+"/lead/products_interest",
            data: {'lead_id': '{{$lead->id}}', _token: '{{ csrf_token() }}'},
            success: function (result) {
                $.each(result, function (i, item) {
                    $i=i+1;
                    $linkDetailProduct=baseUrl+"/product/"+item.id+"/edit";
                    
                    $lineHistory="<tr><td>"+$i+"</td><td><a href='"+$linkDetailProduct+"' target='_blank'>"+item.name+"</a></td> <td class='number'>"+item.number_hand_store+"</td><td>"+item.date_interate+"</td></tr>";
                    
                    $('.list_of_product_interate').append($lineHistory);

                });
            }

                                        
        });
        @if(isset($lead) && $lead!="")
            $lead_id='{{$lead->id}}';
            loadLogCall($lead_id);
        @endif
    });

    function loadLogCall(leadid){
        $('.list_of_items_calendar tr').remove();
        $.ajax({
            type: "GET",
            url: baseUrl+"/task/history",
            data: {'lead_id': leadid, _token: '{{ csrf_token() }}'},
            success: function (result) {
                $.each(result, function (i, item) {
                    $status=item.title_status;
                    $i=i+1;
                    if(item.type_task==2){
                        $status="<span style='color:green'>"+item.title_status+"</span>";
                    }
                    $edit='<a class="popup-edit-'+item.id+'" href="/task/editag?id='+item.id+'">Sửa</a> | <a class="popup-modal-report-'+item.id+'" href="/task/reporttask?task_id='+item.id+'&redirect={{$linkfull}}">Báo cáo</a>';
                    $lineHistory="<tr><td class='number'><a href=\"javascript:void(0)\" onclick=\"return showReport("+item.id+");\" id=\"showhideicons"+item.id+"\"><span class=\"show\">+</span></a></td><td>"+$i+"</td><td>"+item.task_title+"</td> <td>"+item.task_description+"</td><td>"+item.task_note+"</td></td><td>"+item.task_deadline+"</td><td>"+$edit+"</td></tr>";
                    $lineHistory1="<tr id=\"linetask"+item.id+"\"  class='boxshowhide' style='display:none'><td class='list_of_items"+item.id+"' colspan='7'><table id='data"+item.id+"' class='table table-bordered' style='width:1000px'><thead><tr><th>STT</th><th>User</th><th>Tình trạng</th><th>Nội dung</th><th>Ngày Cập nhật</th><th>File báo cáo</th></tr></thead><tbody class='list_of_items_data_"+item.id+"'></tbody></table></td></tr>";
                    $('.list_of_items_calendar').append($lineHistory);
                    $('.list_of_items_calendar').append($lineHistory1);

                    $('.popup-edit-'+item.id).magnificPopup({
                        disableOn: 700,
                        type: 'ajax',
                        //preloader: false,
                        //fixedContentPos: false
                    });

                    $('.popup-modal-report-'+item.id).magnificPopup({
                        type: 'ajax',
                        preloader: false,
                        focus: 'task_report_description',
                        modal: true
                    });
                    $(document).on('click', '.popup-modal-report-dismiss', function (e) {
                        e.preventDefault();
                        $.magnificPopup.close();
                    });
                });
            }

                                        
        });
    }
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
                    $dataLine+="<div class='col-md-12 col-sm-12 col-xs-12'><strong>" + item.description + "</strong>&nbsp;&nbsp;Ngày "+ item.date+"</div>";
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
    $(document).ready(function(){
        @if(isset($lead) && $lead!="")
            loadHistory({{$lead->id}});
        @endif
    });
    @endif
    function showReport($taskid) {
        if (document.getElementById("linetask"+$taskid).style.display === "none") {
            document.getElementById("linetask"+$taskid).style.display = "contents";
            $("#showhideicons"+$taskid).html("<span class=\"show\">-</span>");
        } else {
            document.getElementById("linetask"+$taskid).style.display = "none";
            $("#showhideicons"+$taskid).html("<span class=\"show\">+</span>");
        }
        // document.getElementById("linetask"+$taskid).style.display = 'contents';
        // document.getElementById('linetask'+$taskid).classList.remove("boxshowhide");
        $.ajax({
            type: "GET",
            url: baseUrl+"/task/history_task_report",
            data: {'task_id': $taskid, _token: '{{ csrf_token() }}'},
            success: function (result) {
                $dataLine="";
                $.each(result, function (i, item) {
                    $dataLine+=" <tr>";
                    $dataLine+=" <td>"+(i+1)+"</td>";
                    $dataLine+=" <td>"+item.full_name+"</td>";
                    $dataLine+=" <td>"+item.title_status+"</td>";
                    $dataLine+=" <td>"+item.task_report_description+"</td>";
                    $dataLine+=" <td>"+item.date_report+"</td>";
                    if(item.file_report!=""){
                        $dataLine+=" <td><a href='/"+item.file_report+"' target='_blank'>Download file</a></td>";
                    }else{
                        $dataLine+=" <td>Không có file báo cáo</td>";

                    }
                    $dataLine+=" </tr>";
                });
                $('.list_of_items_data_'+$taskid).html($dataLine);
            }
        });
            
    }
    function hideReport($taskid) {
        $('.list_of_items_data_'+$taskid).html("");
    }
    @if(isset($lead) && $lead!="")
        $(function () {
            $('.createorder').bind('click', function (event) {
                // using this page stop being refreshing 
                event.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: '/{{$type."/".$lead->id}}',
                    data: $('#lead').serialize(),
                    success: function () {
                        @if($lead->phone!="")
                            window.open("https://crm.lavendervn.com/sales/create?cus={{$lead->id}}",'_blank');
                        @else
                            alert("Bạn cần có số điện thoại KH để tạo đơn hàng ");
                            return false;
                        @endif
                    }
                });
            });
        });
        $(function () {
            $('#chatwithuser').bind('click', function (event) {
                // using this page stop being refreshing 
                event.preventDefault();
                submitMessenger();
            });
        });
        var facebook_messenger_id = '{{trim($lead->psid)}}';
        $lastLoad=0;
        if(facebook_messenger_id!="" && facebook_messenger_id!=0){
            $loaded=0;
            setInterval(function(){
                var lasttimechat = $('#lasttimechat').val();
                //var lastIdChat = $('#lastIdchat').val();
                var lastIdChat = 0;//$('#lastIdchat').val();
                showHistoryChat(facebook_messenger_id, lasttimechat, lastIdChat, 0, $loaded);
                if($loaded==0){
                    let el = document.querySelector('.linechat:last-child');
                    el.scrollIntoView(true, {
                            behavior: 'smooth'
                    }); 
                }
                $loaded++;
             }, 3000);

             setInterval(function(){
                $(".boxchat .linechat").removeClass("boxlight");
             }, 10000);
             

             /*
            var lasttimechat = $('#lasttimechat').val();
                var lastIdChat = $('#lastIdchat').val();
            showHistoryChat(facebook_messenger_id, lasttimechat, lastIdChat);
            */
        }
        var phonenumber = '{{$lead->phone}}';
        $loadedSMS=0;
        if(phonenumber!="" && phonenumber!=0){
            setInterval(function(){
                var lasttimesms = $('#lasttimesms').val();
                //var lastIdChat = $('#lastIdchat').val();
                var lastIdSMS = 0;//$('#lastIdchat').val();
                showHistorySMS('{{$lead["id"]}}', lasttimesms, lastIdSMS, 0, $loadedSMS);
                if($loadedSMS==0){
                    let el = document.querySelector('.linesms:last-child');
                    el.scrollIntoView(true, {
                            behavior: 'smooth'
                    }); 
                }
                $loadedSMS++;
             }, 10000);
             setInterval(function(){
                $(".boxsms .linesms").removeClass("boxlight");
             }, 20000);
        }
        function submitMessenger(){
            var facebook_messenger_id = '{{trim($lead->psid)}}';
            var page_id = '{{$lead->page_id}}';
            var partner_id = '{{$user_data["partner_id"]}}';
            var content = $('#chatdesc').val();
            var lasttimechat = $('#lasttimechat').val();
            var lastIdChat = $('#lastIdchat').val();
            var user_id = "{{$user_data["id"]}}";
            //var photos = $('input[name="photos[]"]').val();
            var photos = $('input[name="photos[]"]').map(function(){return $(this).val();}).get();
            // var photos = $('#photos').val();
            if(photos!=""){
                $("#uploads").html("<li>Đang gởi hình</li>");
            }
            $("#chatdesc").val("");
            if(content!="" || photos!=""){
                $.ajax({
                type: 'POST',
                url: 'https://api.fastercrm.com/api/sendmessenger',
                data: {'facebook_messenger_id': facebook_messenger_id, 'page_id': page_id, 'user_id': user_id, 'partner_id': partner_id, 'content': content, 'photos': photos, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $("#uploads li").remove();
                    let el = document.querySelector('.linechat:last-child');
                    el.scrollIntoView(true, {
                        behavior: 'smooth'
                    });
                    if(data.id>0){
                        lastIdChat=data.id;
                        $('#lastIdchat').val(lastIdChat);
                    }
                    return showHistoryChat(facebook_messenger_id, lasttimechat, lastIdChat, 1,2);
                }
             });
            }
        }
        $('#chatdesc').bind('keypress', function(e) {
            if(e.keyCode==13){
                submitMessenger();
            }
        });


        function submitSMS(){
            var lead_id = '{{$lead->id}}';
            var phone = '{{$lead->phone}}';
            var sender = '{{$user_data["phone_number"]}}';
            var partner_id = '{{$user_data["partner_id"]}}';
            var content = $('#smsdesc').val();
            var lasttimechat = $('#lasttimesms').val();
            var lastIdChat = $('#lastIdsms').val();
            var user_id = "{{$user_data["id"]}}";
            $("#smsdesc").val("");
            if(content!=""){
                $.ajax({
                type: 'POST',
                url: 'https://api3.fastercrm.com/api/send_sms',
                data: {'lead_id': lead_id, 'user_id': user_id, 'phone': phone, 'partner_id': partner_id, 'description': content, 'sender': sender, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    let el = document.querySelector('.linesms:last-child');
                    el.scrollIntoView(true, {
                        behavior: 'smooth'
                    });
                    return showHistorySMS(lead_id, lasttimechat, lastIdChat, 1,2);
                }
             });
            }
        }
        $('#smsdesc').bind('keypress', function(e) {
            if(e.keyCode==13){
                submitSMS();
            }
        });

        $(function () {
            $('#smswithuser').bind('click', function (event) {
                // using this page stop being refreshing 
                event.preventDefault();
                submitSMS();
            });
        });
        
    @endif
    function showHistoryChat($psid, $lasttime, $lastIdChat, $scroll=0, $timeload) {
            $.ajax({
                type: "GET",
                url: baseUrl+"/lead/historychat",
                data: {'psid': $psid, 'lasttime': $lasttime, 'lastIdChat': $lastIdChat, 'timeload':$timeload, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $lastitem=0;
                     $lastId=0;
                    $.each(result, function (i, item) {
                        if($('#chatform .boxchat #idchat'+item.id).length <= 0){
                            $dataLine="";
                            if(item.sender_id==$psid){
                                $light="";
                                if(item.read==0){
                                    $light="boxlight";
                                }
                                $addphone="";

                                $phonemain="{{$lead["phone"]}}";
                                if(item.extention!=""){
                                    if(item.extention!=$phonemain){
                                        $addphone="&nbsp<span class='number"+item.id+"'><a href='javascript:void(0);' onclick=\"return updatephone('"+item.extention+"', "+item.id+")\" title='Thêm số điện chính cho khách hàng' class='iconsadd '>[ + Phone ]</a></span>";
                                    }else if($phonemain!="" && item.extention==$phonemain){
                                        $addphone="&nbsp<a href='javascript:void(0);' class='iconsadd mainnumber'>[Số chính]</a>";
                                    } 
                                
                                }

                                $dataLine+="<div class=\"linechat client "+$light+"\" id=\"idchat"+item.id+"\">";
                                $dataLine+="<span class=\"name\">{{$lead["opportunity"]}} <span class=\"date\">"+item.date+"</span></span>";
                                $dataLine+="<span class=\"content\">"+item.messenger+" "+$addphone+"</span>";
                                $dataLine+="</div>";
                                if (window.skipped_once == true) {
                                    document.getElementById('bgAudio').play();
                                }
                            }else{
                                $dataLine+="<div class=\"linechat user\" id=\"idchat"+item.id+"\">";
                                $dataLine+="<span class=\"name\">{{$user_data["first_name"]}} <span class=\"date\">"+item.date+"</span></span>";
                                $dataLine+="<span class=\"content\">"+item.messenger+"</span>";
                                $dataLine+="</div>";
                            }
                            if(item.idpre!="" && item.idpre>0 && $('#idchat'+item.idpre).length > 0){
                                if(item.idpre>item.id){
                                    $('#chatform #idchat'+item.idpre).before($dataLine);
                                }else{
                                    $('#chatform #idchat'+item.idpre).after($dataLine);
                                } 
                            }else{
                                $('#chatform #idchat0').before($dataLine);
                               // $('.boxchat').append($dataLine);
                            }
                        }
                        $lastitem=item.lasttime;
                        $lastId=item.id;
                    });
                    window.skipped_once = true;
                    if($lastitem>0){
                        $('#chatform #lasttimechat').val($lastitem);
                    }
                    if($lastId>0){
                        $('#chatform #lastIdchat').val($lastId);
                    }
                    if($lastLoad==0 || $scroll==1){
                        let el = document.querySelector('.linechat:last-child');
                        el.scrollIntoView(true, {
                                behavior: 'smooth'
                        });
                    }
                    $lastLoad++;
                }
        });
    }

    function showHistorySMS($lead, $lasttime, $lastIdChat, $scroll=0, $timeload) {
            $.ajax({
                type: "GET",
                url: baseUrl+"/lead/historysms",
                data: {'lead_id': $lead, 'lasttime': $lasttime, 'lastIdChat': $lastIdChat, 'timeload':$timeload, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    $lastitem=0;
                     $lastId=0;
                    $.each(result, function (i, item) {
                        if($('.boxsms #idsms'+item.id).length <= 0){
                            $dataLine="";
                            if(item.sender_id=='{{$lead['phone']}}'){
                                $light="";
                                if(item.read==0){
                                    $light="boxlight";
                                }
                                $addphone="";
                                if(item.extention!="" && item.extention!="{{$lead["phone"]}}"){
                                    $addphone="&nbsp<span class='number"+item.extention+"'><a href='javascript:void(0);' onclick=\"return updatephone('"+item.extention+"')\" title='Thêm số điện chính cho khách hàng' class='iconsadd '>[ + ]</a></a>";
                                }else if(item.extention=="{{$lead["phone"]}}"){
                                    $addphone="&nbsp<a href='javascript:void(0);' class='iconsadd mainnumber'>[Số chính]</a>";
                                }

                                $dataLine+="<div class=\"linesms client "+$light+"\" id=\"idsms"+item.id+"\">";
                                $dataLine+="<span class=\"name\">{{$lead["opportunity"]}} <span class=\"date\">"+item.date+"</span></span>";
                                $dataLine+="<span class=\"content\">"+item.messenger+" "+$addphone+"</span>";
                                $dataLine+="</div>";
                                if (window.skipped_once == true) {
                                    document.getElementById('bgAudio').play();
                                }
                            }else{
                                $dataLine+="<div class=\"linesms user\" id=\"idsms"+item.id+"\">";
                                $dataLine+="<span class=\"name\">{{$user_data["first_name"]}} <span class=\"date\">"+item.date+"</span></span>";
                                $dataLine+="<span class=\"content\">"+item.messenger+"</span>";
                                $dataLine+="</div>";
                            }
                            if(item.idpre!="" && item.idpre>0 && $('#idsms'+item.idpre).length > 0){
                                if(item.idpre>item.id){
                                    $('#idsms'+item.idpre).before($dataLine);
                                }else{
                                    $('#idsms'+item.idpre).after($dataLine);
                                } 
                            }else{
                                $('#idsms0').before($dataLine);
                               // $('.boxchat').append($dataLine);
                            }
                        }
                        $lastitem=item.lasttime;
                        $lastId=item.id;
                    });
                    window.skipped_once = true;
                    if($lastitem>0){
                        $('#lasttimechat').val($lastitem);
                    }
                    if($lastId>0){
                        $('#lastIdsms').val($lastId);
                    }
                    if($lastLoad==0 || $scroll==1){
                        let el = document.querySelector('.linesms:last-child');
                        el.scrollIntoView(true, {
                                behavior: 'smooth'
                        });
                    }
                    $lastLoad++;
                }
        });
    }

    function openForm($formname, $laterecord="") {
        if($laterecord==""){
            $laterecord="linechat"
        }
        document.getElementById($formname).style.display = "block";
        $(".messengboxtitle").attr("style", "display:none");
        if($laterecord!=""){
            let el = document.querySelector('.'+$laterecord+':last-child');
        el.scrollIntoView(true, {
            behavior: 'smooth'
        });
        }
        
    }

    function closeForm($formname) {
        document.getElementById($formname).style.display = "none";
        $(".messengboxtitle").attr("style", "display:block");


    }
    
    function updatephone($phone){
            var lead_id = '{{$lead->id}}';
            var user_id = '{{$user_data["id"]}}';
            var partner_id = '{{$user_data["partner_id"]}}';
            var phone = $phone;
            if(phone!=""){
                $.ajax({
                type: 'POST',
                url: '{{ url('lead/updatephone')}}',
                data: {'lead_id': lead_id, 'phone': phone, 'partner_id': partner_id, 'user_id': user_id, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('.number'+$phone).html('<a href="javascript:void(0);" title="Số DT chính" class="iconsadd">[Số KH]</a>');
                    $(".mainnumber").val(data.phone);

                    return true;
                }
             });
            }
            
    }
    function updateCallStatus($function, $id, $title, $lead_id){
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
    function updateselect(value, $function){
        $lead_id='{{$lead["id"]}}';
        $title=$( "#"+$function+" option:selected" ).text();
        return updateCallStatus($function, value, $title, $lead_id);
    }
    
    @if(isset($_GET["chat"]) && $_GET["chat"]==1)
        document.getElementById("chatform").style.display = "block";
        let el = document.querySelector('.linechat:last-child');
        el.scrollIntoView(true, {
                behavior: 'smooth'
        });
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
                    $("#uploads").append("<li id='"+data.id+"'><input type='hidden' name='photos[]' value='"+data.photo+"'/>"+data.name+" <a href=\"javascript:void(0);\" onclick=\"deleteimages(\'"+data.name+"\', '"+data.date+"', '"+data.id+"');\" class=\"deleteimages\">Xóa</a></li>");
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
    $(function () {

        $('.listtagclient').empty();
        res="";
        @if($tagListData && count($tagListData)>0)
            @foreach($tagListData as $listData)
                $active='{{$listData["active"]}}';
                $tagremove=0;
                if($active=="active"){
                    $tagremove=1;
                }
                $itemTag="<div id=\"linetag{{$listData["id"]}}\" class=\"linetagsbox linestag{{$listData["id"]}} {{$listData["active"]}}\"><span class=\"linetags\"><a href=\"javascript:void(0)\" onclick=\"addTag('{{$listData["id"]}}', '{{$listData["title"]}}', '"+$tagremove+"');\">{{$listData["title"]}}</a></span></div>";
                $('.listtagclient').append($itemTag);

            @endforeach
        @endif
    });

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
                        $(".listtagclient").append('<div id="linetag'+data.tag_id+'" class="linetagsbox active linestag'+data.tag_id+'"><span class="linetags "><a href="javascript:void(0)">'+$tag+'</a></span></div>');
                    }
                    if($remove==1){
                        $("#linetag"+$tagid).removeClass('active');
                        $removenext=0;
                    }else{
                        $removenext=1;
                    } 
                    $("#linetag"+$tagid+" .linetags").html("<a href=\"javascript:void(0)\" onclick=\"addTag('"+$tagid+"', '"+$tag+"', '"+$removenext+"');\">"+$tag+"</a>");
                    $("#tags_text").val('');
                }
             });
        }
        
    }

    $('#group_id').change(function () {
        if($(this).val()!="" && $(this).val()!=null){
            showStatus($(this).val());
        }
    });
    function showStatus($groupid) {
        $.ajax({
            type: "GET",
            url: baseUrl+"/lead/statusgroup",
            data: {'group_id': $groupid, _token: '{{ csrf_token() }}'},
            success: function (result) {
                $('#status').empty();
                $('#status').select2({
                    theme: "bootstrap",
                    placeholder: "Chọn tình trạng khách hàng"
                }).trigger('change');
                $.each(result, function (i, item) {
                    $('#status').append($('<option></option>').val(i).html(item));
                });
            }

                                        
        });
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
    </script>

<script>
    function loadNotification(){
            $user_noti_id='121';
            $partner_noti_id='21';
            $idlast=$('#notificationslast').val();
            $.ajax({
                type: "GET",
                url: "https://api.fastercrm.com/api/listnoti", 
                data: {'user_id': $user_noti_id, 'partner_id': $partner_noti_id, 'idlast': $idlast, _token: 'cyW0ev9wibMJBl8hcedBWtmUWHJxzSeed7lKf7ch'},
                success: function (result) {
                    $notificationlist=result.notification;
                    //var res = $tag.split(",");
                    $('.firstnotinumber').empty();
                    $('.notinumber').html($notificationlist.length);
                    $.each($notificationlist, function (i, item) {
                        if(item.id>$idlast){
                            $idlast=item.id;
                        }
                        $class="";
                        if(item.status==0){
                            $class="bold";
                        }
                        $link=item.url;
                        $itemNotification="<div id=\"linenoti"+item.id+"\" class=\"linenoti linenoti"+item.id+" "+$class+"\" onclick=\"updatenotification('status', '"+item.id+"', '"+$link+"');\"><span>"+item.title+"</span><span class=\"small italic pull-right\"> "+item.created_at+"</span></div>";
                        $('.firstnotinumber').append($itemNotification);
                    });
                    $('#notificationslast').val($idlast);

                    
                }
            });
    }
    function updatenotification($status, $id, $link){
        if($id>0){
            $.ajax({
                type: "POST",
                url: "https://api.fastercrm.com/api/updatenotification",
                data: {'id': $id, _token: 'cyW0ev9wibMJBl8hcedBWtmUWHJxzSeed7lKf7ch'},
                success: function (result) {
                    if(result.success==1){
                        $("#linenoti"+$id).removeClass("bold");
                        if($link!=""){
                            location.href=$link;
                        }
                    }else{
                        alert('Có lỗi xảy ra');
                        return false;
                    }
                   
                }
            });
        }
    }
    $loaded=0;
    /*
    setInterval(function(){
        loadNotification();
    }, 10000);  */

    function ShowHide(idbox, classelement) {
        var x = document.getElementById(idbox);
        if (x.style.display === "none") {
            x.style.display = "block";
            $('.'+classelement).html('[ - ]');
        } else {
            x.style.display = "none";
            $('.'+classelement).html('[ + ]');
        }
    }
    function updatenotification($type){
        if($type>=0){
            $.ajax({
                type: "POST",
                url: "/lead/acceptlead",
                data: {'received_lead': $type, _token: 'cyW0ev9wibMJBl8hcedBWtmUWHJxzSeed7lKf7ch'},
                success: function (result) {
                    if(result.success==1){
                        if($type==1){
                            alert('Bạn đã mở lại nhận lead');
                            $("#receptleadpost").html('<a href="javascript:void(0);"  onclick="return updatenotification(0);">Ngưng nhận lead </a>');
                        }else{
                            alert('Bạn đã ngưng nhận lead');
                            $("#receptleadpost").html('<a href="javascript:void(0);"  onclick="return updatenotification(1);">Nhận lead</a>');
                        }
                        return true;
                    }else{
                        alert('Có lỗi xảy ra');
                        return false;
                    }
                   
                }
            });
        }
    }
</script>
<script src="{{ URL::asset('assets/libs/select2/js/select2.min.js')}}"></script>

@endsection
