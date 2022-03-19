
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
                <form accept-charset="UTF-8" action="{{ url('lead') }}/import-lead" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="col-md-6">
                        <div class="form-group">
                                {!! Form::label('group_id',  trans('lead.group_name'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('group_id', $groupLead, null, ['id'=>'group_id', 'class' => 'form-control select_function']) !!}
                                    <span class="help-block">{{ $errors->first('group_id', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('status',  trans('lead.status'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                                <div class="controls">
                                    {!! Form::select('status', $callStatus, null, ['id'=>'status', 'class' => 'form-control select_function']) !!}
                                    <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                        <label>Choose Excel File</label> 
                        <p class="chonfile"><input type="file" name="import_file" id="import_file" accept=".xls,.xlsx"></p>
                        </div>
                        <div class="col-md-12">
                        <button type="submit" id="submit" name="import" class="btn btn-primary">Import</button> | <span><a href=" /templates/fileKH.xlsx"> Download mẫu </a></span>
                        </div>
                    </div>
                </form>
            
        </div>
    </div>
</div>
@stop

{{-- Scripts --}}
@section('scripts')
<script language="javascript">
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
     </script>
@stop
