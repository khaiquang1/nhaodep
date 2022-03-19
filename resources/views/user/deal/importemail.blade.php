
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
                Xác thực
            </h4>
        </div>
    <div class="panel-body">
        <div class="row">
                <form accept-charset="UTF-8" action="{{ url('lead') }}/import-email" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
                {{ csrf_field() }}
                    @if($number_check_email<=$total_email_checked)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Loại data</label> 
                                <div class="controls">
                                    <select name="datatype" id="datatype" class="form-control">
                                        <option value="email">Email</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="zalo">Zalo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Segment trên Mautic</label> 
                                <div class="controls">
                                    <input type="text" name="segment" id="segment"  class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label>Choose Excel File</label> 
                            <div class="controls">
                                <p class="chonfile"><input type="file" name="import_file" id="import_file" accept=".xls,.xlsx"></p>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" id="submit" name="import" class="btn btn-primary">Import</button> | <span><a href=" /templates/email_template.xlsx"> Download mẫu </a></span>
                        </div>
                    @else
                        <div class="col-md-12">
                            Chúng tôi vẫn đang kiểm tra email. Việc này có thể mất nhiều thời gian. Chúng tôi sẽ thông báo khi nào chúng tôi xong.
                        </div>
                    @endif
                   
                </form>
            
        </div>
    </div>
</div>
@stop

{{-- Scripts --}}
@section('scripts')

@stop
