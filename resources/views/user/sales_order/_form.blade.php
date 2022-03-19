<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($saleorder))
            {!! Form::model($saleorder, ['url' => $type . '/' . $saleorder->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_order']) !!}
            <div id="sendby_ajax"></div>
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_order']) !!}
        @endif
        <div class="row">
            @php //var_dump($products);@endphp
        </div>
        <div class="row">
                    <div class="col-xs-12 col-sm-3">
                        <div class="form-group required {{ $errors->has('lead_id') ? 'has-error' : '' }}">
                            {!! Form::label('lead_id', trans('lead.opportunity_client'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                <select name="lead_id" id="lead_id" class="form-control lead_id" data-search="true"></select>
                            </div>
                            
                        </div>
                     </div>
                    <div class="col-xs-12 col-sm-3">
                        <div class="form-group">
                            {!! Form::label('sales_person_id', trans('sales_order.sales_care'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::select('sales_person_id', $staffs, (isset($saleorder)?$saleorder->sales_person_id:$userData->id), ['id'=>'sales_person_id','class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3">
                        <div class="form-group required {{ $errors->has('date') ? 'has-error' : '' }}">
                            {!! Form::label('date_ship', trans('quotation.date'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::text('date_ship', isset($saleorder) ? $saleorder->date_ship : null, ['class' => 'form-control date']) !!}
                                <span class="help-block">{{ $errors->first('date_ship', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3">
                        <div class="form-group required {{ $errors->has('date_exp') ? 'has-error' : '' }}">
                            {!! Form::label('date_exp', trans('quotation.exp_date'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::text('date_exp', isset($saleorder) ? $saleorder->expire_date : null, ['class' => 'form-control date']) !!}
                                <span class="help-block">{{ $errors->first('date_exp', ':message') }}</span>
                            </div>
                        </div>
                    </div>
            </div>
            
            <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('payment_term') ? 'has-error' : '' }}">
                    {!! Form::label('payment_term', trans('quotation.payment_term'), ['class' => 'control-label required']) !!}
                    <div class="controls">

                        {!! Form::select('payment_term', $paymentmethod, (isset($saleorder)?$saleorder->payment_term:null), ['id'=>'payment_term','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('payment_term', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('status', trans('quotation.status'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        <div class="input-group">
                            <label>
                                <input type="radio" name="status" value="{{trans('sales_order.draft_salesorder')}}"
                                       class='icheckblue'
                                       @if(isset($saleorder) && $saleorder->status == trans('sales_order.draft_salesorder')) checked @endif>
                                {{trans('sales_order.draft_salesorder')}}
                            </label>
                            <label>
                                <input type="radio" name="status" value="{{trans('sales_order.send_salesorder')}}"
                                       class='icheckblue'
                                       @if(isset($saleorder) && $saleorder->status == trans('sales_order.send_salesorder')) checked @endif>
                                {{trans('sales_order.send_salesorder')}}
                            </label>
                        </div>

                        <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('status_client') ? 'has-error' : '' }}">
                    {!! Form::label('status_client', trans('sales_order.status_client'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('status_client', $statusList, (isset($saleorder)?$saleorder->status_client:null), ['id'=>'status_client','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('payment_term', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('shipping_term') ? 'has-error' : '' }}">
                    {!! Form::label('shipping_term', trans('sales_order.shipping_term'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('shipping_term', $shippingmethod, (isset($saleorder)?$saleorder->shipping_term:null), ['id'=>'shipping_term','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('shipping_term', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('shipping_fee') ? 'has-error' : '' }}">
                    {!! Form::label('shipping_fee', trans('sales_order.shipping_fee'), ['class' => 'control-label']) !!}
                    <div class="controls">
                    {!! Form::text('shipping_fee', isset($saleorder) ? $saleorder->shipping_fee : null, ['class' => 'form-control numberprice', 'onchange'=>'update_total_price();']) !!}
                    <span class="help-block">{{ $errors->first('shipping_fee', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    {!! Form::label('branch_id', trans('branch.branch'), ['class' => 'control-label']) !!}
                    <div class="controls">
                    {!! Form::select('branch_id', $branch, (isset($saleorder)?$saleorder->branch_id:null), ['id'=>'branch_id','class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('branch_id', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        

        <div class="row">
            <div class="col-md-12">
                <label class="control-label required">{{trans('quotation.products')}}
                    <span>{!! $errors->first('products') !!}</span></label>
                <div class="{{ $errors->has('product_id.*') ? 'has-error' : '' }}">
                    <span class="help-block">{{ $errors->first('product_id.*', ':message') }}</span>
                </div>
                <div class="{{ $errors->has('product_id') ? 'has-error' : '' }}">
                    <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr class="detailes-tr">
                        <th width="20%">{{trans('quotation.product')}}</th>
                        <th width="35%">{{trans('quotation.description')}}</th>
                        <th width="10%">{{trans('quotation.quantity')}}</th>
                        <th width="10%">{{trans('quotation.unit_price')}}</th>
                        <th width="10%">{{trans('product.tax')}}</th>
                        <th width="10%">{{trans('quotation.subtotal')}}</th>
                        <th width="5%"></th>
                    </tr>
                    </thead>
                    <tbody id="InputsWrapper">
                    @if(isset($saleorder)&& $saleorder->salesOrderProducts->count()>0)
                        @foreach($saleorder->salesOrderProducts as $index => $variants)
                      
                            <tr class="remove_tr">
                                <td>
                                    <input type="hidden" name="product_id[]" id="product_id{{$index}}" value="{{$variants->pivot->product_id}}" readOnly>
                                    <select name="product_list" id="product_list{{$index}}" class="form-control product_list" data-search="true" onchange="product_value({{$index}});">
                                        <option value="">Chọn sản phẩm</option>
                                        @foreach( $products as $product)
                                            <option value="{{ $product->id . '_' . $product->description. '_' . $product->quantity_on_hand.'_'.$product->sale_price}}" @if($product->id == $variants->pivot->product_id) selected="selected" @endif>
                                                {{ $product->product_name}}</option>
                                        @endforeach
                                    </select>
                                <td><textarea name=description[]" id="description{{$index}}" 
                                              class="form-control resize_vertical" readOnly>{{$variants->description}}</textarea>
                                </td>
                                <td><input type="number" min="1" name="quantity[]" id="quantity{{$index}}"
                                           value="{{$variants->pivot->quantity}}"
                                           class="form-control numberprice"
                                           onkeyup="product_price_changes('quantity{{$index}}','price{{$index}}','sub_total{{$index}}', 'taxes{{$index}}');">
                                </td>
                                <td><input type="text" name="price[]" id="price{{$index}}"
                                           value="{{$variants->pivot->price}}"
                                           class="form-control numberprice" readonly></td>
                                <!-- <input type="text" name="taxes[]" id="taxes{{$index}}"
                                       value="{{ floatval($sales_tax) }}" class="form-control"> {{$variants->pivot->tax}}-->
                                       
                                <td><input type="text" name="taxes[]" id="taxes{{$index}}"
                                           value="@if(isset($variants->pivot->taxes)){{$variants->pivot->taxes}}@else 0 @endif"
                                           class="form-control numberprice" readonly></td>
                                @if(isset($variants->pivot->taxes))
                                <input type="hidden" name="taxestotal[]" id="taxestotal{{$index}}" value="{{$variants->pivot->taxes}}*{{$variants->pivot->price}}*{{$variants->pivot->quantity}}" class="form-control numberprice" readonly>
                                @else
                                <input type="hidden" name="taxestotal[]" id="taxestotal{{$index}}" value="0" class="form-control numberprice" readonly>

                                @endif
                                <td><input type="text" name="sub_total[]" id="sub_total{{$index}}"
                                           value="{{$variants->pivot->quantity*$variants->pivot->price}}"
                                           class="form-control numberprice" readOnly></td>
                                <td><a href="javascript:void(0)" class="delete removeclass"><i
                                                class="fa fa-fw fa-trash fa-lg text-danger"></i></a></td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
                <button type="button" id="AddMoreFile"
                        class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> {{trans('quotation.add_product')}}
                </button>
                <div class="row">&nbsp;</div>
            </div>
        </div>
        

        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('total') ? 'has-error' : '' }}">
                    {!! Form::label('total', trans('quotation.total'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('total', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('total', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('tax_amount') ? 'has-error' : '' }}">
                    {!! Form::label('tax_amount', trans('quotation.tax_amount'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('tax_amount', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('tax_amount', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('grand_total') ? 'has-error' : '' }}">
                    {!! Form::label('grand_total', trans('quotation.grand_total'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('grand_total', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('grand_total', ':message') }}</span>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group required {{ $errors->has('discount') ? 'has-error' : '' }}">
                    {!! Form::label('discount', trans('quotation.discount').' (%)', ['class' => 'control-label']) !!}
                    <div class="controls">
                        <input type="text" name="discount" id="discount"
                                value="{{(isset($saleorder)?$saleorder->discount:"0.00")}}"
                                class="form-control numberprice numberprice"
                                onkeyup="update_total_price();">
                        <span class="help-block">{{ $errors->first('discount', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group required {{ $errors->has('final_price') ? 'has-error' : '' }}">
                    {!! Form::label('final_price', trans('quotation.final_price'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('final_price', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('final_price', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group required {{ $errors->has('terms_and_conditions') ? 'has-error' : '' }}">
                    {!! Form::label('quotation_duration', trans('sales_order.terms_and_conditions'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::textarea('terms_and_conditions', null, ['class' => 'form-control resize_vertical']) !!}
                        <span class="help-block">{{ $errors->first('terms_and_conditions', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($saleorder))
        <div class="row">
                <div class="col-md-12 col-lg-12">
                    <meta name="_token" content="{{ csrf_token() }}">
                    <div class="panel panel-success succ-mar">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                                data-l="true"></i>
                                {{ trans('dashboard.historyLogs') }} 
                                <a href="#popup-modal-note" data-rel="popup"  class="popup-modal-note poupmain">Ghi chú lịch sử</a>
                            </h4>
                        </div>
                        <div class="panel-body task-body1">
                            
                            <div class="row list_of_items"></div>
                        </div>
                    </div>
                </div>
        </div>
        @endif
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i
                            class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                <a href="{{ url()->previous() }}" class="btn btn-warning"><i
                            class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
            </div>
        </div>
       
        <!-- ./ form actions -->
        {!! Form::close() !!}
    </div>
</div>
@if (isset($saleorder))
<div data-role="popup" class="mfp-hide white-popup-block" id="popup-modal-note" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;">
        <div data-role="header" data-theme="a"><strong>{{trans('task.note_calendar')}}</strong></div>
        <div role="main" class="ui-content">
            {!! Form::open(['url' => "/sales_order/add_sales_order_log", 'method' => 'post', 'files'=> true,'id'=>'logs', 'enctype'=>'multipart/form-data']) !!}
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                    {!! Form::text('logs_text', null, ['class' => 'form-control', 'id'=>'logs_text','data-fv-integer' => 'true', 'placeholder'=>'Tiêu đề']) !!}
                    </div>
                    <div class="controls">
                    {!! Form::textarea('logs_description', null, ['class' => 'form-control', 'id'=>'logs_description', 'placeholder'=>'Nội dung công việc']) !!}
                    </div>
                    <div class="controls">
                        <input type="text" id="tags" class='form-control'  value="" placeholder="Tag"/>
                    </div>
                    <div class="controls">
                        <input type="file" multiple="true" id="photos"  name="photos[]" accept=".png,.jpg,.gif"/>
                    </div>
                    <input type="hidden" id="sales_order_id" value="{{$saleorder["id"]}}"/>
                </div>
            </div>
            <div id="loading"> </div>  
             <a href="javascript:void(0);" id="add_log_calender"  class="button_upate" data-rel="back" data-transition="flow">Thêm ghi chú</a>
            <a href="#" class="popup-modal-note-dismiss" data-rel="back">Bỏ qua</a>
             {!! Form::close() !!}
        </div>
</div>
@endif
@section('scripts')
    <script>
        $(document).ready(function() {
            $("#lead_id").select2(
                {
                    theme: "bootstrap",
                    placeholder: "{{ trans('quotation.agent_name') }}",
                    ajax: {
                        type: "GET",
                        dataType: 'json',
                        url: '{{ url('lead/ajax_leads_list')}}',
                        data: function (params) {
                            return {
                                keyword: params.term,
                                _token: '{{ csrf_token() }}'
                            };
                        },
                        processResults: function (data, params) {
                            return {
                                results: $.map(data.lead_data, function (item) {
                                    return {
                                        text: item.opportunity,
                                        id: item.id,
                                        data: item
                                    };
                                })
                            };
                        }
                    }
                }
            );
            @if(isset($saleorder) && $saleorder->id!="")
            getListLead({{$saleorder->lead_id}});
            @endif
            @if(isset($lead_id) && $lead_id!="")
            getListLead({{$lead_id}});
            @endif
            function getListLead(id) {
                $.ajax({
                    type: "GET",
                    url: '{{ url('lead/ajax_lead_list')}}',
                    data: {'lead_id': id, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        $('#lead_id').empty();
                        $.each(data, function (i, item) {
                        $('#lead_id').append($('<option></option>').val(i).html(item).attr('selected', i== id ? true : false));
                        });
                    }
                });
            }
            /*
            $("#sales_person_id").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('salesteam.main_staff') }}"
            }); */
            $("#payment_term").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('quotation.payment_term') }}"
            });
            $(".product_list").select2({
                theme:"bootstrap",
                placeholder:"Product"
            });
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });

            $("#sales_order").bootstrapValidator({
                fields: {
                    leads_id: {
                        validators: {
                            notEmpty: {
                                message: 'KH không được rỗng'
                            }
                        }
                    },
                    product_list: {
                        validators: {
                            notEmpty: {
                                message: 'Sản phẩm không được rỗng'
                            }
                        }
                    }
                }
            });
        });
        
        $(function () {
            update_total_price();
            $('#qtemplate_id').change(function () {
                if ($(this).val() > 0) {
                    $.ajax({
                        type: "GET",
                        url: '{{url("quotation/ajax_qtemplates_products")}}/' + $(this).val(),
                        success: function (data) {
                            content_data = '';
                            $.each(data, function (i, item) {
                                content_data += makeContent(FieldCount, item);
                                FieldCount++;
                            });
                            $("#InputsWrapper").html(content_data);
                            update_total_price();
                        }
                    });
                }
                setTimeout(function(){
                    $(".product_list").select2({
                        theme:"bootstrap",
                        placeholder:"Product"
                    })
                },100);
            });
        });
        function product_value(FieldCount) {
            if(FieldCount!="" && FieldCount!= undefined) {
                var all_Val = $("#product_list" + FieldCount).val();
                if(all_Val!='undefined' && all_Val!=undefined){
                    var res = all_Val.split("_");
                    $quantity=1;//es[2];
                    price=res[3];
                    $('#product_id' + FieldCount).val(res[0]);
                    $('#description' + FieldCount).val(res[1]);
                    $('#quantity' + FieldCount).val($quantity);
                    $('#price' + FieldCount).val(res[3]);
                    $('#taxes' + FieldCount).val(res[4]);
                    tax_amount=res[4]*$quantity*price/100;
                    $('#taxestotal' + FieldCount).val(tax_amount);
                    $('#sub_total' + FieldCount).val(price*$quantity);
                    update_total_price();
                }
            }
        }
        function product_price_changes(quantity, product_price, sub_total_id, tax) {
            var no_quantity = $("#" + quantity).val();
            var no_product_price = $("#" + product_price).val();
            var tax = $("#" + tax).val();
            var sub_total = parseFloat(no_quantity * no_product_price);
            var tax_amount = 0;
           // tax_amount = (sub_total * {{floatval($sales_tax)}}) / 100;
            tax_amount = (sub_total*tax)/100;
            $('#taxes').val(tax_amount.toFixed(2));
            $('#' + sub_total_id).val(sub_total);
            update_total_price();
        }
        function update_total_price() {
            var sub_total = 0;
            $('#total').val(0);
            $('#tax_amount').val(0);
            $('#grand_total').val(0);
            $('#final_price').val(0);
            var shipping_fee = $("#shipping_fee").val();
            if(shipping_fee<=0){
                shipping_fee=0;
            }
            final_price_total=0;
            FieldCount=0;
            
            $('input[name^="sub_total"]').each(function () {
                sub_total += parseFloat($(this).val());

                $('#total').val(sub_total.toFixed(2));
                var vat=$('#quantity'+FieldCount).val();

               // var tax_per = '{{floatval($sales_tax)}}';
                var tax_per = '{{floatval($sales_tax)}}';
                var tax_vat = parseFloat($(this).val());

                //var tax_amount = 0;
                tax_amount = sumtaxtotal();//(sub_total * tax_vat) / 100;
                $('#tax_amount').val(tax_amount);//tax_amount.toFixed(2)
                var grand_total = 0;
                grand_total = sub_total + tax_amount;
                $('#grand_total').val(grand_total.toFixed(2));
                var discount = $("#discount").val();
                discount_amount=0;
                if(Number(discount)<=100){
                    discount_amount = (grand_total * Number(discount)) / 100;
                }
                final_price_total = grand_total - discount_amount+Number(shipping_fee);
                $('#final_price').val(final_price_total.toFixed(2));
            });

        }

        function sumtaxtotal(){
            taxestoal=0;
            $('input[name^="taxestotal"]').each(function () {
                taxestoal += parseFloat($(this).val());
            });
            return taxestoal;
        }
        
        function makeContent(number, item) {
            item = item || '';
            var content = '';
            $field=0;
            content += '<tr class="remove_tr"><td>';
            content += '<input type="hidden" name="product_id[]" id="product_id' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '@if(isset($productsLead) && $productsLead!=""){{$productsLead["id"]}} @endif' : item.pivot.product_id) + '" readOnly>';
            content += '<select name="product_list" id="product_list' + number + '" class="form-control product_list" data-search="true" onchange="product_value(' + number + ');">' +
                @if(isset($productsLead) && $productsLead!="")
                '<option value="{{ $productsLead["id"] . '_' . $productsLead["description"].'_'.$productsLead["quantity_on_hand"].'_'.$productsLead["sale_price"]}}"';
                 content += 'selected';
                content += '>' +
                '{{ $productsLead["product_name"]}}</option>';
                @else
                '<option></option>';
                @endif
                @if(isset($products) && $products!="")
                    @foreach( $products as $product)
                        content += '<option value="{{ $product->id . '_' . $product->description.'_'.$product->quantity_on_hand.'_'.$product->sale_price.'_'.$product->vat}}"';
                    if ((typeof item.pivot == 'undefined') ? '' : item.pivot.product_id =={{$product->id}}) {
                        content += 'selected';
                    }
                    content += '>' +
                        '{{ $product->product_name}}</option>';
                    @endforeach
                @endif

                content += '</select>' +
                '<td><textarea name=description[]" id="description' + number + '" rows="' + number + '" class="form-control resize_vertical" readOnly>' + ((typeof item.description == 'undefined') ? '' : item.description) + '</textarea>' +
                '</td>' +
                '<td><input type="number" min="0" name="quantity[]" id="quantity' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.quantity) + '" class="form-control numberprice" onkeyup="product_price_changes(\'quantity' + number + '\',\'price' + number + '\',\'sub_total' + number + '\',\'taxes' + number + '\');"></td>' +
                '<td><input type="text" name="price[]" id="price' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.price) + '" class="form-control" readOnly></td>' +
                '<td><input type="text" name="taxes[]" id="taxes' + number + '" value="'+ ((typeof item.pivot == 'undefined') ? '' : item.pivot.vat) +'" class="form-control numberprice" readOnly></td>' +
                '<td><input type="text" name="sub_total[]" id="sub_total' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.quantity*item.pivot.price) + '" class="form-control numberprice" readOnly><input type="hidden" name="taxestotal[]" id="taxestotal' + number + '" value="" readonly> </td>' +
                '<td><a href="javascript:void(0)" class="delete removeclass" title="{{ trans('table.delete') }}"><i class="fa fa-fw fa-trash fa-lg text-danger"></i></a></td>' +
                '</tr>';
                
            return content;
        }
        
        var FieldCount = 1; //to keep track of text box added
        var MaxInputs = 50; //maximum input boxes allowed
        var InputsWrapper = $("#InputsWrapper"); //Input boxes wrapper ID
        var AddButton = $("#AddMoreFile"); //Add button ID
        var x = InputsWrapper.length; //initlal text box count
        $("#total").val("0");
        $(AddButton).click(function (e)  //on add input button click
        {
            setTimeout(function(){
                $(".product_list").select2({
                    theme:"bootstrap",
                    placeholder:"Product"
                });
                quantityChange();
            });

            if (x <= MaxInputs) //max input box allowed
            {
                FieldCount=x+1; //text box added increment
                content = makeContent(FieldCount);
                $(InputsWrapper).append(content);
                x++; //text box increment
                $('.number').keypress(function (event) {
                    if (event.which < 46
                        || event.which > 59) {
                        event.preventDefault();
                    } // prevent if not number/dot

                    if (event.which == 46
                        && $(this).val().indexOf('.') != -1) {
                        event.preventDefault();
                    } // prevent if already dot
                });
            }
            return false;
        });

        quantityChange();
        function quantityChange(){
            $(".number").bind("keyup change click",function(){
                var no_quantity = $(this).val();
                var no_product_price = $(this).closest("tr").find("input[name='price[]']").val();
                var no_sales_tax = $(this).closest("tr").find("input[name='taxes[]']").val();
                var sub_total = parseFloat(no_quantity * no_product_price);
                var tax_amount = 0;
                tax_amount = (sub_total*no_sales_tax) / 100;
                $('#taxes').val(tax_amount.toFixed(2));
                $(this).closest("tr").find("input[name='sub_total[]']").val(sub_total);
                update_total_price();
            });
        }

        $(InputsWrapper).on("click", ".removeclass", function (e) { //user click on remove text
            @if(!isset($saleorder))
            if (x > 0) {
                $(this).parent().parent().remove(); //remove text box
                x--; //decrement textbox
            }
            @else
            $(this).parent().parent().remove(); //remove text box
            x--; //decrement textbox
            @endif
            update_total_price();
            return false;
        });

        $('#qtemplate').on('keyup keypress', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        function create_pdf(saleorder_id) {
            $.ajax({
                type: "GET",
                url: "{{url('sales_order' )}}/" + saleorder_id + "/ajax_create_pdf",
                data: {'_token': '{{csrf_token()}}'},
                success: function (msg) {
                    if (msg != '') {
                        $("#pdf_url").attr("href", msg);
                        var index = msg.lastIndexOf("/") + 1;
                        var filename = msg.substr(index);
                        $("#pdf_url").html(filename);
                        $("#saleorder_pdf").val(filename);
                    }
                }
            });
        }



        $('#form').on('keyup keypress', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        var dateFormat = 'Y-m-d';
        flatpickr('#date_ship',{
            minDate: '{{ isset($saleorder) ? $saleorder->date_ship : now() }}',
            dateFormat: dateFormat,
            disableMobile: "true",
            /*
            "plugins": [new rangePlugin({ input: "#exp_date"})],
            onChange:function(){
                $('#sales_order').bootstrapValidator('revalidateField', 'exp_date');
            } */
        });
        flatpickr('#date_exp',{
            minDate: '{{ isset($saleorder) ? $saleorder->date_exp : date("Y-m-d",strtotime("+1 day"))}}',
            dateFormat: dateFormat,
            disableMobile: "true",
        });
        /*
        if(old('payment_term'))
        $("#payment_term").find("option[value='"+'{{old("payment_term")}}'+"']").attr('selected',true);
        endif
        
        $("#sales_team_id").change(function(){
            ajaxMainStaffList($(this).val());
        });
        @if(old('sales_person_id'))
        ajaxMainStaffList({{old('sales_team_id')}});
        @endif
        function ajaxMainStaffList(id){
            $.ajax({
                type: "GET",
                url: '{{ url('opportunity/ajax_main_staff_list')}}',
                data: {'id': id, _token: '{{ csrf_token() }}' },
                success: function (data) {
                    $("#sales_person_id").empty();
                    var teamLeader;
                    $.each(data.main_staff, function (val, text) {
                        teamLeader =data.team_leader;
                        $('#sales_person_id').append($('<option></option>').val(val).html(text));
                    });
                    $("#sales_person_id").find("option[value='"+teamLeader+"']").attr('selected',true);
                    $("#sales_person_id").find("option[value!='"+teamLeader+"']").attr('selected',false);
                    $("#sales_person_id").select2({
                        theme:'bootstrap',
                        placeholder:"{{ trans('salesteam.main_staff') }}"
                    });
                    $('#sales_order').bootstrapValidator('revalidateField', 'sales_person_id');
                }
            });
        } 
        $("#customer_id").change(function(){
            ajaxSalesTeamList($(this).val());
        });*/
        
        $("#leads_id").change(function(){
            ajaxLeadList($(this).val());
        });
        function ajaxSalesTeamList(id){
            $.ajax({
                type: "GET",
                url: '{{ url('quotation/ajax_sales_team_list')}}',
                data: {'id': id, _token: '{{ csrf_token() }}' },
                success: function (data) {
                    $("#sales_team_id").empty();
                    $.each(data.sales_team, function (val, text) {
                        $('#sales_team_id').append($('<option></option>').val(val).html(text));
                    });
                    $("#sales_team_id").find("option[value='"+data.agent_name+"']").attr('selected',true);
                    $("#sales_team_id").find("option[value!='"+data.agent_name+"']").attr('selected',false);
                    $("#sales_team_id").select2({
                        theme:'bootstrap',
                        placeholder:"{{ trans('quotation.sales_team_id') }}"
                    });
                    ajaxMainStaffList(data.agent_name);
                    $('#sales_order').bootstrapValidator('revalidateField', 'sales_team_id');
                }
            });
        }
        function ajaxLeadList(keyword){
            $.ajax({
                type: "GET",
                url: '{{ url('lead/ajax_leads_list')}}',
                data: {'id': keyword, _token: '{{ csrf_token() }}' },
                success: function (data) {
                    $("#leads_id").empty();
                    $.each(data.lead_data, function (val, text) {
                        $('#leads_id').append($('<option></option>').val(val).html(text));
                    });
                }
            });
        }
        
        $("#send_saleorder").bootstrapValidator({
            fields: {
                'recipients[]': {
                    validators: {
                        notEmpty: {
                            message: 'The recipients field is required'
                        }
                    }
                },
                message_body:{
                    validators: {
                        notEmpty: {
                            message: 'The message field is required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function(e) {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: "{{url('sales_order/send_saleorder')}}",
                type: "POST",
                data: formData,
                async: false,
                success: function (msg) {
                    $('body,html').animate({scrollTop: 0}, 200);
                    $("#sendby_ajax").html(msg);
                    setTimeout(function(){
                        $("#sendby_ajax").hide();
                    },5000);
                    $("#modal-send_by_email").modal('hide');
                },
                cache: false,
                contentType: false,
                processData: false
            });
            e.preventDefault();
        });
        $("#modal-send_by_email").on('hide.bs.modal', function () {
            $("#recipients").find("option").attr('selected',false);
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });
            $("#send_saleorder").data('bootstrapValidator').resetForm();
        });
        $('.icheckblue').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        });
        $('.icheckblue').on('ifChecked',function(){
            $("#sales_order").bootstrapValidator('revalidateField', 'status');
        });
        $('.numberprice').text(function () { 
            var str = $(this).html() + ''; 
            x = str.split('.'); 
            x1 = x[0]; x2 = x.length > 1 ? '.' + x[1] : ''; 
            var rgx = /(\d+)(\d{3})/; 
            while (rgx.test(x1)) { 
                x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
            } 
            $(this).html(x1 + x2); 
        });

        @if(isset($productsLead) && $productsLead!="")
            setTimeout(function(){
                $(".product_list").select2({
                    theme:"bootstrap",
                    placeholder:"Product"
                });
                quantityChange();
            });
            $item="";
            content = makeContent(1, $item);
            $(InputsWrapper).append(content);
            //x++; //text box increment
           // product_value('{{$productsLead["id"]}}');
           //@if(isset($productsLead) && $productsLead!="")
                product_value(1);

                $quantity=1;//es[2];
                $('#product_id1').val({{$productsLead["id"]}});
                $('#taxes1').val("{{$productsLead["vat"]}}");
                $('#description1').val("{{$productsLead["description"]}}");
                $('#quantity1').val($quantity);
                $('#price1').val({{$productsLead["sale_price"]}});
                var quantity=$('#quantity1').val();
                var price=$('#price1').val();
                $('#sub_total1').val(price*quantity);
                tax_amount={{$productsLead["vat"]}}*quantity*price/100;

                $('#taxestotal1').val(tax_amount);

                update_total_price();
            //@endif
        @endif

        $(function () {
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
        });

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
                var sales_order_id = $('#sales_order_id').val();
                var tags = $('#tags').val();            
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
                $("#popup-modal-note").html("Uploading");
                $.ajax({
                    type: "post",
                    url: '{{ url('sales_order/add_sales_order_log')}}',
                    enctype: 'multipart/form-data',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function (data) {
                        alert("Cài đặt thành công");
                        $.magnificPopup.close();
                        loadHistory(sales_order_id);
                    }
                });
                return false;
            });
            //khai báo nút submit form
        }); 

        function loadHistory($sales_order_id){
            $('.list_of_items').html('');
            $.ajax({
                type: "GET",
                url: baseUrl+"/sales_order/history",
                data: {'item_id': $sales_order_id, _token: '{{ csrf_token() }}'},
                success: function (result) {
                    
                    $.each(result, function (i, item) {
                        const $photos=item.photos;
                        $listPhoto="";
                        if($photos!="" && $photos!="undefined" && $photos!=null){
                            const photolist = $photos.split("|");
                            for($i=0;$i<photolist.length;$i++){
                                $listPhoto+='<a href="/upload/'+photolist[$i]+'" target="_blank" class="photolist"> <img src="/upload/'+photolist[$i]+'" height="50px" /></a>';
                            }
                        }
                        $('.list_of_items').append("<div class='todolist_list showactions list1' id='"+ item.id +"'>");
                            $('.list_of_items').append("<div class='col-md-12 col-sm-12 col-xs-12'><strong>" + item.description + "</strong>&nbsp;&nbsp;Ngày "+ item.date+"</div>");
                            if(item.logs_description!="" && item.logs_description!=null){
                            $('.list_of_items').append("<div class='col-md-12 col-sm-12 col-xs-12 textdesc'>" + item.logs_description + "</div>");
                            }
                            if($listPhoto!=""){
                            $('.list_of_items').append("<div class='col-md-12 col-sm-12 col-xs-12 textdesc'>" + $listPhoto + "</div>");
                            }
                        $('.list_of_items').append("</div>");
                    });
                }
            });
         }
        @if(isset($saleorder) && $saleorder!="")

        $(document).ready(function() { loadHistory({{$saleorder->id}}); });
        @endif
    </script>
@endsection