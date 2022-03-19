
@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')

<div class="panel panel-primary">
    <div class="panel-heading">
            <h4 class="panel-title">
                Import data từ Excel
            </h4>
        </div>
    <div class="panel-body">
        <div class="row">
                @if(isset($partner) && $partner->partner_type==2)
                <form accept-charset="UTF-8" action="{{ url('product') }}/importproductedu" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
                {{ csrf_field() }}

                @else
                <form accept-charset="UTF-8" action="{{ url('product') }}/import" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
                {{ csrf_field() }}
                @endif
                        <div class="col-md-2">
                            <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                                {!! Form::label('category_name',  trans('product.category_id'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('category_id', $categories, null, ['id'=>'category_id', 'class' => 'form-control select_function']) !!}
                                    <span class="help-block">{{ $errors->first('category_id', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                                {!! Form::label('product_type',  trans('product.product_type'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('product_type', $productTypes, null, ['id'=>'product_type', 'class' => 'form-control select_function']) !!}
                                    <span class="help-block">{{ $errors->first('product_type', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group required {{ $errors->has('fileList') ? 'has-error' : '' }}">
                                {!! Form::label('status',  trans('product.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('status', $statuses, null, ['id'=>'status', 'class' => 'form-control select_function']) !!}
                                    <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label>Choose Excel File</label> 
                            <p class="chonfile"><input type="file" name="file" id="file" accept=".xls,.xlsx"></p>
                        </div>
                        <div class="col-md-12">
                            
                            <button type="submit" id="submit" name="import" class="btn btn-primary">Import</button> | <span> @if(isset($partner) && $partner->partner_type==2) <a href="/templates/product.xlsx"> Download mẫu </a>  @else <a href="/templates/product_template.xlsx"> Download mẫu </a> @endif</span>
                        </div>
                        <div class="col-md-12" style="margin-bottom:10px" style="display:none">
                          <strong>Diễn giải</strong> 
                          <p><strong>product_name:</strong> tên sản phẩm, <strong>quantity_on_hand:</strong> Số lượng hiện có, <strong>quantity_available:</strong> số lượng có thể bán, <strong>sale_price:</strong> Giá bán, <strong>description:</strong> Mô tả, vat: Thuế VAT(%), <strong>product_image:</strong> Link hình</p>
                        </div>
                   
                </form>
            
        </div>
    </div>
</div>
@stop

{{-- Scripts --}}
@section('scripts')

@stop
