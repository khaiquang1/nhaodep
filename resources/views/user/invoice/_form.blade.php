<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($invoice))
            {!! Form::model($invoice, ['url' => $type . '/' . $invoice->id, 'method' => 'put', 'id'=>'invoice','files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'invoice']) !!}
        @endif
            <div id="sendby_ajax"></div>
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
                <div class="form-group required {{ $errors->has('sales_person_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_person_id', trans('salesteam.main_staff'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('sales_person_id', $salesList, null, ['id'=>'sales_person_id','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('sales_person_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-3">
                    <div class="form-group required {{ $errors->has('invoice_date') ? 'has-error' : '' }}">
                        {!! Form::label('invoice_date', trans('invoice.invoice_date'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('invoice_date', isset($invoice)? $invoice->invoice_start_date : null, ['class' => 'form-control date']) !!}
                            <span class="help-block">{{ $errors->first('invoice_date', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3">
                    <div class="form-group required {{ $errors->has('invoice_deadline_date') ? 'has-error' : '' }}">
                        {!! Form::label('invoice_deadline_date', trans('invoice.due_date'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('invoice_deadline_date', isset($invoice)? $invoice->invoice_deadline_date : null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('invoice_deadline_date', ':message') }}</span>
                        </div>
                    </div>
                </div> 
        </div>
        <div class="row">
            
            <div class="col-xs-12 col-sm-6">

                <div class="form-group required {{ $errors->has('payment_term') ? 'has-error' : '' }}">
                    {!! Form::label('payment_id',  trans('invoice.payment_term'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                    <div class="controls">
                        {!! Form::select('payment_id', $payment_term, null, ['id'=>'payment_id', 'class' => 'form-control select_function']) !!}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('status', trans('invoice.status'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('status', $invoice_status, null, ['id'=>'status', 'class' => 'form-control select_function']) !!}
                        <span class="help-block">{{ $errors->first('status', ':message') }}</span>
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
                            <th width="20%">{{trans('invoice.product')}}</th>
                            <th width="35%">{{trans('invoice.description')}}</th>
                            <th width="10%">{{trans('invoice.quantity')}}</th>
                            <th width="10%">{{trans('invoice.unit_price')}}</th>
                            <th width="10%">{{trans('invoice.tax')}}</th>
                            <th width="10%">{{trans('invoice.subtotal')}}</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody id="InputsWrapper">
                        @if(isset($invoice) && $invoice->invoiceProducts->count()>0)
                            @foreach($invoice->invoiceProducts as $index => $variants)
                                <tr class="remove_tr">
                                    <td>
                                        <input type="hidden" name="product_id[]" id="product_id{{$index}}"
                                               value="{{$variants->pivot->product_id}}"
                                               readOnly>
                                        <select name="product_list" id="product_list{{$index}}" class="form-control product_list"
                                                data-search="true" onchange="product_value({{$index}});">
                                            <option value=""></option>
                                            @foreach( $products as $product)
                                                <option value="{{ $product->id . '_' . $product->description. '_' . $product->quantity_on_hand.'_'.$product->sale_price}}"
                                                        @if($product->id == $variants->pivot->product_id) selected="selected" @endif>
                                                    {{ $product->product_name}}</option>
                                            @endforeach
                                        </select>
                                    <td><textarea name=description[]" id="description{{$index}}" rows="2"
                                                  class="form-control resize_vertical" readOnly>{{$variants->description}}</textarea>
                                    </td>
                                    <td><input type="number" min="1" name="quantity[]" id="quantity{{$index}}"
                                               value="{{$variants->pivot->quantity}}"
                                               class="form-control numberprice"
                                               onkeyup="product_price_changes('quantity{{$index}}','price{{$index}}','sub_total{{$index}}');">
                                    </td>
                                    <td><input type="text" name="price[]" id="price{{$index}}"
                                               value="{{$variants->pivot->price}}"
                                               class="form-control numberprice" readonly></td>
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
                                               class="form-control" readOnly></td>
                                    <td><a href="javascript:void(0)" class="delete removeclass"><i
                                                    class="fa fa-fw fa-trash fa-lg text-danger"></i></a></td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" id="AddMoreFile"
                        class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> {{trans('invoice.add_product')}}</button>
                <div class="row">&nbsp;</div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('total') ? 'has-error' : '' }}">
                    {!! Form::label('total', trans('invoice.total'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('total', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('total', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('tax_amount') ? 'has-error' : '' }}">
                    {!! Form::label('tax_amount', trans('invoice.tax_amount'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('tax_amount', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('tax_amount', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('grand_total') ? 'has-error' : '' }}">
                    {!! Form::label('grand_total', trans('invoice.grand_total'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('grand_total', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('grand_total', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('discount') ? 'has-error' : '' }}">
                    {!! Form::label('discount', trans('invoice.discount'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        <input type="text" name="discount" id="discount"
                               value="{{(isset($invoice)?$invoice->discount:"0.00")}}"
                               class="form-control numberprice"
                               onkeyup="update_total_price();">
                        <span class="help-block">{{ $errors->first('discount', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('final_price') ? 'has-error' : '' }}">
                    {!! Form::label('final_price', trans('invoice.final_price'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('final_price', null, ['class' => 'form-control numberprice','readonly']) !!}
                        <span class="help-block">{{ $errors->first('final_price', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
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

@section('scripts')
    <script>
        $(document).ready(function(){
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
            @if(isset($invoice) && $invoice->id!="")
            getListLead({{$invoice->lead_id}});
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
            $("#customer_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('invoice.agent_name') }}"
            });
            $("#sales_person_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('salesteam.main_staff') }}"
            });
            $("#sales_team_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('invoice.sales_team_id') }}"
            });
            $("#payment_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('invoice.payment_id') }}"
            });
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });
            $(".product_list").select2({
                theme:"bootstrap",
                placeholder:"Product"
            });
            $("#invoice").bootstrapValidator({
                fields: {
                    lead_id: {
                        validators: {
                            notEmpty: {
                                message: 'Kh??ch h??ng kh??ng ???????c tr???ng.'
                            }
                        }
                    },
                    sales_person_id: {
                        validators: {
                            notEmpty: {
                                message: 'Ph???i c?? nh??n vi??n ph??? tr??ch.'
                            }
                        }
                    },
                    invoice_date: {
                        validators: {
                            notEmpty: {
                                message: 'Ng??y t???o h??a ????n kh??ng ???????c r???ng'
                            }
                        }
                    },
                    invoice_deadline_date: {
                        validators: {
                            notEmpty: {
                                message: 'Ng??y ?????n h???n thanh to??n kh??ng ???????c r???ng'
                            }
                        }
                    },
                    payment_id: {
                        validators: {
                            notEmpty: {
                                message: '??i???u kho???n thanh to??n b???t bu???c'
                            }
                        }
                    },
                    status: {
                        validators: {
                            notEmpty: {
                                message: 'Ph????nng th???c thanh to??n l?? b???t bu???c.'
                            }
                        }
                    },
                    product_list: {
                        validators: {
                            notEmpty: {
                                message: 'C???n cung c???p s???n ph???m/d???ch v??? KH ch???n.'
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
            var all_Val = $("#product_list" + FieldCount).val();
            var res = all_Val.split("_");
            $('#product_id' + FieldCount).val(res[0]);
            $('#description' + FieldCount).val(res[1]);
            $('#quantity' + FieldCount).val(res[2]);
            $('#price' + FieldCount).val(res[3]);
            var quantity=$('#quantity'+FieldCount).val();
            var price=$('#price'+FieldCount).val();
            $('#sub_total' + FieldCount).val(price*quantity);
            update_total_price();
        }
        function product_price_changes(quantity, product_price, sub_total_id) {
            var no_quantity = $("#" + quantity).val();
            var no_product_price = $("#" + product_price).val();

            var sub_total = parseFloat(no_quantity * no_product_price);

            var tax_amount = 0;
            tax_amount = (sub_total * {{floatval($sales_tax)}}) / 100;
            $('#taxes').val(tax_amount.toFixed(2));

            var quantity=$('#quantity'+FieldCount).val();
            var price=$('#price'+FieldCount).val();
            tax_amount=res[4]*quantity*price/100;
             $('#taxestotal' + FieldCount).val(tax_amount);

            $('#' + sub_total_id).val(sub_total);
            update_total_price();

        }
        function update_total_price() {
            var sub_total = 0;
            $('#total').val(0);
            $('#tax_amount').val(0);
            $('#grand_total').val(0);
            $('#final_price').val(0);
            $('input[name^="sub_total"]').each(function () {
                sub_total += parseFloat($(this).val());
                $('#total').val(sub_total.toFixed(2));

                var tax_per = '{{floatval($sales_tax)}}';
                var tax_amount = 0;

                //tax_amount = (sub_total * tax_per) / 100;
                tax_amount = sumtaxtotal();//(sub_total * tax_vat) / 100;
                $('#tax_amount').val(tax_amount.toFixed(2));
                var grand_total = 0;
                grand_total = sub_total + tax_amount;
                $('#grand_total').val(grand_total.toFixed(2));
                
                var discount = $("#discount").val();
                discount_amount = (grand_total * discount) / 100;
                final_price = grand_total - discount_amount;
                $('#final_price').val(final_price.toFixed(2));

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
            content += '<tr class="remove_tr"><td>';
            content += '<input type="hidden" name="product_id[]" id="product_id' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.product_id) + '" readOnly>';
            content += '<select name="product_list" id="product_list' + number + '" class="form-control product_list" data-search="true" onchange="product_value(' + number + ');">' +
                '<option value=""></option>';
            @foreach( $products as $product)
                content += '<option value="{{ $product->id . '_' . $product->description.'_'.$product->quantity_on_hand.'_'.$product->sale_price.'_'.$product->vat}}"';
            if ((typeof item.pivot == 'undefined') ? '' : item.pivot.product_id =={{$product->id}}) {
                content += 'selected';
            }
            content += '>' +
                '{{ $product->product_name}}</option>';
            @endforeach

                content += '</select>' +
                '<td><textarea name=description[]" id="description' + number + '" rows="2" class="form-control resize_vertical" readOnly>' + ((typeof item.description == 'undefined') ? '' : item.description) + '</textarea>' +
                '</td>' +
                '<td><input type="number" min="0" name="quantity[]" id="quantity' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.quantity) + '" class="form-control number" onkeyup="product_price_changes(\'quantity' + number + '\',\'price' + number + '\',\'sub_total' + number + '\');"></td>' +
                '<td><input type="text" name="price[]" id="price' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.price) + '" class="form-control" readOnly>' +
                '<td><input type="text" name="taxes[]" id="taxes' + number + '" value="'+ ((typeof item.pivot == 'undefined') ? '' : item.pivot.vat) +'" class="form-control numberprice" readOnly></td>' +
                '<td><input type="text" name="sub_total[]" id="sub_total' + number + '" value="' + ((typeof item.pivot == 'undefined') ? '' : item.pivot.quantity*item.pivot.price) + '" class="form-control" readOnly></td>' +
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
            FieldCount++; //text box added increment
            if (x <= MaxInputs) //max input box allowed
            {
                FieldCount++; //text box added increment
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
                var sub_total = parseFloat(no_quantity * no_product_price);
                var tax_amount = 0;
                tax_amount = (sub_total * {{floatval($sales_tax)}}) / 100;
                $('#taxes').val(tax_amount.toFixed(2));
                $(this).closest("tr").find("input[name='sub_total[]']").val(sub_total);
                update_total_price();
            });
        }

        $(InputsWrapper).on("click", ".removeclass", function (e) { //user click on remove text
            $(this).closest(".remove_tr").remove();
            update_total_price();
            return false;
        });

        function create_pdf(quotation_id) {
            $.ajax({
                type: "GET",
                url: "{{url('invoice' )}}/" + quotation_id + "/ajax_create_pdf",
                data: {'_token': '{{csrf_token()}}'},
                success: function (msg) {
                    if (msg != '') {
                        $("#pdf_url").attr("href", msg);
                        var index = msg.lastIndexOf("/") + 1;
                        var filename = msg.substr(index);
                        $("#pdf_url").html(filename);
                        $("#invoice_pdf").val(filename);
                    }
                }
            });
        }
        $("form[name='send_invoice']").submit(function (e) {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: "{{url('invoice/send_invoice')}}",
                type: "POST",
                data: formData,
                async: false,
                success: function (msg) {
                    $('body,html').animate({scrollTop: 0}, 200);
                    $("#sendby_ajax").html(msg);
                },
                cache: false,
                contentType: false,
                processData: false
            });
            e.preventDefault();
        });


        $('#form').on('keyup keypress', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        var dateFormat = 'Y-m-d';
        flatpickr('#invoice_date',{
            minDate: '{{ isset($invoice) ? $invoice->created_at : now() }}',
            dateFormat: dateFormat,
            disableMobile: "true",
            "plugins": [new rangePlugin({ input: "#invoice_deadline_date"})],
            onChange:function(){
                $('#invoice').bootstrapValidator('revalidateField', 'invoice_deadline_date');
            }
        });

        @if(old('payment_id'))
        $("#payment_id").find("option[value='"+'{{old("payment_id")}}'+"']").attr('selected',true);
        @endif
      //  $("#sales_team_id").change(function(){
            //ajaxMainStaffList($(this).val());
        //});
        @if(old('sales_person_id'))
       // ajaxMainStaffList({{old('sales_team_id')}});
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
                    $('#invoice').bootstrapValidator('revalidateField', 'sales_person_id');
                }
            });
        }
        $("#customer_id").change(function(){
            ajaxSalesTeamList($(this).val());
        });
        @if(old('sales_team_id'))
        ajaxSalesTeamList({{old('customer_id')}});
        @endif
        @if(!isset($invoice))
       // $("#sales_team_id").empty();
       // $("#sales_person_id").empty();
        @endif
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
                    $('#invoice').bootstrapValidator('revalidateField', 'sales_team_id');
                }
            });
        }


        $("#send_invoice").bootstrapValidator({
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
            e.preventDefault();
            $.post( "{{url('invoice/send_invoice')}}",
                $('#send_invoice').serialize()
            )
                .done(function( msg ) {
                    $('body,html').animate({scrollTop: 0}, 200);
                    $("#sendby_ajax").html(msg);
                    setTimeout(function(){
                        $("#sendby_ajax").hide();
                    },5000);
                    $("#modal-send_by_email").modal('hide');
                });
        });
        $("#modal-send_by_email").on('hide.bs.modal', function () {
            $("#recipients").find("option").attr('selected',false);
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });
            $("#send_invoice").data('bootstrapValidator').resetForm();
        });
        $('.icheckblue').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        });
        $('.icheckblue').on('ifChecked',function(){
            $("#invoice").bootstrapValidator('revalidateField', 'status');
        });
    </script>
@endsection