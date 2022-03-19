<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($product))
            {!! Form::model($product, ['url' => $type . '/' . $product->id, 'method' => 'put', 'files'=> true, 'id'=> 'product']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=> 'product']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('product_image_file') ? 'has-error' : '' }}">
                        {!! Form::label('product_image_file', trans('product.product_image'), ['class' => 'control-label']) !!}
                        <div class="controls row" v-image-preview>
                            <div class="col-sm-12">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                        <img id="image-preview" width="300">
                                        @if(isset($product->product_image) && $product->product_image!="")
                                            <img src="{{ url('uploads/products/thumb_'.$product->product_image) }}"
                                                 alt="Image">
                                        @endif
                                    </div>
                                    <div>
                                        <span class="btn btn-default btn-file">
                                            <span class="fileinput-new">{{trans('dashboard.select_image')}}</span>
                                            <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                                            <input type="file" name="product_image_file">
                                        </span>
                                        <a href="#" class="btn btn-default fileinput-exists"
                                           data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                                    </div>
                                    <div>
                                        <span class="help-block">{{ $errors->first('product_image_file', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('product_name') ? 'has-error' : '' }}">
                        {!! Form::label('sku', trans('product.sku'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('sku', null, ['class' => 'form-control','placeholder' => 'SKU']) !!}
                            <span class="help-block">{{ $errors->first('sku', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('product_name') ? 'has-error' : '' }}">
                        {!! Form::label('product_name', trans('product.product_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('product_name', null, ['class' => 'form-control','placeholder' => 'Product name']) !!}
                            <span class="help-block">{{ $errors->first('product_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('barcode', trans('product.barcode'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('barcode', null, ['class' => 'form-control','placeholder' => 'Barcode']) !!}
                            <span class="help-block">{{ $errors->first('barcode', ':message') }}</span>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('category_id') ? 'has-error' : '' }}">
                        {!! Form::label('category_id', trans('product.category_id'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('category_id', $categories, null, ['id'=>'category_id','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('category_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('product_type') ? 'has-error' : '' }}">
                        {!! Form::label('product_type', trans('product.product_type'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('product_type', $product_types, (isset($product)?$product->product_type:null), ['id'=>'product_type','class' => 'form-control', 'onchange'=>'changeProductType(this.value)']) !!}
                            <span class="help-block">{{ $errors->first('product_type', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                        {!! Form::label('status', trans('product.status'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('status', $statuses, (isset($product)?$product->status:null), ['id'=>'status','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('quantity_on_hand') ? 'has-error' : '' }}">
                        {!! Form::label('quantity_on_hand', trans('product.quantity_on_hand'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::input('number','quantity_on_hand', null, ['class' => 'form-control number' , 'min'=>0]) !!}
                            <span class="help-block">{{ $errors->first('quantity_on_hand', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('quantity_available') ? 'has-error' : '' }}">
                        {!! Form::label('quantity_available', trans('product.quantity_available'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::input('number','quantity_available', null, ['class' => 'form-control number', 'min'=>0]) !!}
                            <span class="help-block">{{ $errors->first('quantity_available', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('quantity_available') ? 'has-error' : '' }}">
                        {!! Form::label('unit_price', trans('product.unit_price'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                        {!! Form::select('unit_price', $unitprice, (isset($product)?$product->unit_price:null), ['id'=>'unit_price','class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                @if(isset($unitmass) && count($unitmass)>0)
                <div class="col-md-2 Product">
                    <div class="form-group">
                        {!! Form::label('mass', trans('product.mass'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::input('text','mass', null, ['class' => 'form-control', 'min'=>0]) !!}
                            <span class="help-block">{{ $errors->first('mass', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 Product">
                    <div class="form-group required">
                        {!! Form::label('unit_mass', trans('product.unit_mass'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('unit_mass', $unitmass, (isset($product)?$product->unit_mass:null), ['id'=>'unit_mass','class' => 'form-control']) !!}

                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group required {{ $errors->has('description') ? 'has-error' : '' }}">
                        {!! Form::label('description', trans('product.description'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('description', null, ['class' => 'form-control resize_vertical', 'placeholder' => 'Product Information']) !!}
                            <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('user_care', trans('product.user_care'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::select('staff_care', $staff_care, (isset($product)?$product->staff_care:null), ['id'=>'staff_care','class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group number">
                        {!! Form::label('price_cost', trans('product.price_cost'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('price_cost', null, ['class' => 'form-control number numberprice']) !!}
                            <span class="help-block">{{ $errors->first('price_cost', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group number">
                        {!! Form::label('sale_price', trans('product.sale_price'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('sale_price', null, ['class' => 'form-control number numberprice']) !!}
                            <span class="help-block">{{ $errors->first('sale_price', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group number">
                        {!! Form::label('tax', trans('product.tax'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('vat', null, ['class' => 'form-control number']) !!}
                            <span class="help-block">{{ $errors->first('vat', ':message') }}</span>
                        </div>
                    </div>
                </div>
                
                
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('tags', trans('product.tags'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('tags', null, ['class' => 'form-control resize_vertical', 'placeholder' => 'Tags']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('feature', trans('product.feature'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('feature', null, ['class' => 'form-control resize_vertical', 'placeholder' => 'Đặt tính sản phẩm']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row Service">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('start_date', trans('product.start_date'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('start_date', null, ['class' => 'form-control  date flatpickr-input', 'placeholder' => 'Ngày bắt đầu']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('end_date', trans('product.end_date'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('end_date', null, ['class' => 'form-control  date flatpickr-input', 'placeholder' => 'Ngày kết thúc']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                </div>
            </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>


@section('scripts')
    <script>
        function changeProductType($product_type){
                $('.'+$product_type).show();
                if($product_type=="Product"){
                    $('.Service').hide();
                }else if($product_type=="Service"){
                    $('.Product').hide();
                }
            }
        $(document).ready(function () {
            @if(isset($product) && $product->product_type="Service")
            $('.Service').show();
            $('.Product').hide();
            @elseif(isset($product) && $product->product_type="Product")
            $('.Service').show();
            $('.Product').hide();
            @else
            $('.Service').hide();
            $('.Product').hide();
            @endif


            var MaxInputs = 50; //maximum input boxes allowed
            var InputsWrapper = $("#InputsWrapper"); //Input boxes wrapper ID
            var AddButton = $("#AddMoreFileBox"); //Add button ID

            var x = InputsWrapper.length; //initlal text box count
            var FieldCount = 1; //to keep track of text box added
            var dateFormat="Y-m-d";
            flatpickr('#start_date',{
                minDate: '{{ isset($product) ? $product->start_date : now() }}',
                dateFormat: dateFormat,
                disableMobile: "true",
            });
            flatpickr('#end_date',{
                minDate: '{{ isset($product) ? $product->end_date : now() }}',
                dateFormat: dateFormat,
                disableMobile: "true",
            });
            
            $(AddButton).click(function (e)  //on add input button click
            {
                if (x <= MaxInputs) //max input box allowed
                {
                    FieldCount++; //text box added increment
                    //add input box
                    $(InputsWrapper).append('<tr><td><input type="text" name="attribute_name[]" value="" class="form-control"></td><td><input type="text" name="product_attribute_value[]" value="" class="form-control"></td><td><a href="javascript:void(0)" class="delete removeclass" data-toggle="modal" data-target="#modal-basic"><i class="fa fa-fw fa-times text-danger"></i></a></td></tr>');
                    x++; //text box increment
                }
                return false;
            });

            $("#InputsWrapper").on("click", ".removeclass", function (e) { //user click on remove text
                @if(!isset($product))
                if (x > 1) {
                    $(this).parent().parent().remove(); //remove text box
                    x--; //decrement textbox
                }
                @else
                    $(this).parent().parent().remove(); //remove text box
                x--; //decrement textbox
                @endif
                        return false;
            });
            $("#category_id").select2({
                theme: 'bootstrap',
                placeholder:'Select Category'
            });
            
            $("#status").select2({
                theme: 'bootstrap',
                placeholder:'Select Status'
            });
//            form validation
            $("#product").bootstrapValidator({
                fields: {
                    product_image_file: {
                        validators:{
                            file: {
                                extension: 'jpeg,jpg,png',
                                type: 'image/jpeg,image/png',
                                maxSize: 1000000,
                                message: 'The logo format must be in jpeg, jpg or png and size less than 1MB'
                            }
                        }
                    },
                    product_name: {
                        validators: {
                            notEmpty: {
                                message: 'The product name field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The product name must be minimum 3 characters.'
                            }
                        }
                    },
                    category_id: {
                        validators: {
                            notEmpty: {
                                message: 'The category field is required.'
                            }
                        }
                    },
                    status: {
                        validators: {
                            notEmpty: {
                                message: 'The status field is required.'
                            }
                        }
                    },
                    quantity_on_hand: {
                        validators: {
                            notEmpty: {
                                message: 'The quantity on hand field is required.'
                            }
                        }
                    },
                    quantity_available: {
                        validators: {
                            notEmpty: {
                                message: 'The quantity available field is required.'
                            }
                        }
                    },
                    sale_price: {
                        validators: {
                            notEmpty: {
                                message: 'The sale price field is required.'
                            },
                            regexp: {
                                regexp: /^\d{1,12}(\.\d{1,2})?$/,
                                message: 'Sale price contains digits only.'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
