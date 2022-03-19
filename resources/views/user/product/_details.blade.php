<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-5 col-md-4 col-lg-3  m-t-20">
                <div>
{{--                    {!! Form::label('product_image_file', trans('product.product_image'), ['class' => 'control-label']) !!}--}}
                </div>
                <div class="fileinput fileinput-new m-t-10">
                    <div class="fileinput-preview thumbnail form_Blade">
                        @if(isset($product->product_image))
                            <img src="{{ url('uploads/products/'.$product->product_image) }}" alt="avatar" width="300">
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-7 col-md-8 col-lg-9 m-t-30">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.product_name')}}</label>:
                    {{ $product->product_name }}
                </div>
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.category_id')}}</label>:
                    {{ is_null($product->category)?"-":$product->category->name }}
                </div>
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.product_type')}}</label>:
                    {{ $product->product_type }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 col-lg-3 m-t-10">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.status')}}</label>
                    <div class="controls">
                        {{ $product->status }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-3 m-t-10">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.quantity_on_hand')}}</label>
                    <div class="controls">
                        {{ $product->quantity_on_hand }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-3 m-t-10">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.quantity_available')}}</label>
                    <div class="controls">
                        {{ $product->quantity_available }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-3 m-t-10">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.sale_price')}}</label>
                    <div class="controls">
                        {{ $product->sale_price }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('product.description')}}</label>
                    <div class="controls">
                        {{ $product->description }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        @if (@$action == 'show')
                            <a href="{{ url($type) }}" class="btn btn-warning"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        @else
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}
                            </button>
                            <a href="{{ url($type) }}" class="btn btn-warning"><i
                                        class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>