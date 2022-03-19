@extends('layouts.user')
{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
    </div>
    <input type="hidden" id="user_id" value="{{$userid}}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-fw fa-bell-o"></i>
                {{ $title}}  :<strong>{{$fullname}}</strong>
            </h4>
            <span class="pull-right">
                <i class="fa fa-fw fa-chevron-up clickable"></i>
                <i class="fa fa-fw fa-times removepanel clickable"></i>
            </span>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                {!! Form::open(['url' => 'call/' . $userid.'/user', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
                <div class="row">
                

                    <div class="col-md-4">
                        <div class="form-group required {{ $errors->has('starting_date') ? 'has-error' : '' }}">
                            {!! Form::label('starting_date', trans('call.starting_date'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::text('starting_date', isset($starting_date) ? $starting_date : null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group required {{ $errors->has('ending_date') ? 'has-error' : '' }}">
                            {!! Form::label('ending_date', trans('call.ending_date'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::text('ending_date', isset($ending_date) ? $ending_date : null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="control-label">&nbsp;</label>
                        <div class="controls">
                            <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                <table id="data" class="table table-bordered" data-id="data_user">
                    <thead>
                    <tr>
                        <th>{{ trans('call.date') }}</th>
                        <th>{{ trans('lead.leads_name') }}</th>
                        <th>{{ trans('call.summary') }}</th>
                        <th>{{ trans('call.duration') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    columns:[
                        {"data":"date", "className": "date"},
                        {"data":"lead_name"},
                        {"data":"call_summary"},
                        {"data":"duration", "className": "number"},
                        {"data":"actions"}
                    ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#user_id').val() + "/" + $('#data').attr('data-id') : "/data_user")+"?starting_date="+$('#starting_date').val()+"&ending_date="+$('#ending_date').val()
                });

                $("#date").on("dp.change",function(){
                    $('#call').bootstrapValidator('revalidateField', 'date');
                });
            
                var dateTimeFormat = 'Y-m-d';
                flatpickr('#starting_date',{
                    minDate: '',
                    dateFormat: dateTimeFormat,
                    enableTime: false,
                    disableMobile: "true",
                    "plugins": [new rangePlugin({ input: "#ending_date"})],
                    onChange:function(){
                        $('#search').bootstrapValidator('revalidateField', 'ending_date');
                    }
                });



            });
        </script>
    @endif
@stop