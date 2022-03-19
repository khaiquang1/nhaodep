<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($groupclient))
            {!! Form::model($groupclient, ['url' => $type . '/' . $groupclient->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('groupclient') ? 'has-error' : '' }}">
                        {!! Form::label('name', trans('groupclient.name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('description', trans('groupclient.description'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('description', null, ['class' => 'form-control resize_vertical','placeholder'=>'Nội dung cần tư vấn trong nhóm']) !!}
                            <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($groupclient) && $groupclient->status==1)checked @endif>
                            {{trans('groupclient.status')}} </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                    {!! Form::label('client_status', trans('groupclient.status_client'), ['class' => 'control-label']) !!}
                        <div class="controls">
                        @if($statusGroup)
                            @foreach($statusGroup as $listGroupData)
                            <label> <input type="checkbox" value="{{$listGroupData["id"]}}" name="client_status[]" id="client_status[]" class='icheck'  @if(isset($groupclient) && $groupclient->client_status!="" && in_array($listGroupData["id"], explode(",",$groupclient->client_status))) checked @endif>
                                {{$listGroupData["title"]}} </label>
                            @endforeach
                        @endif
                        </div>
                    </div>
                </div>
            </div>

        <!-- Form Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}
                        </button>
                        <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ./ form actions -->

{{--            {{ $newSales }}--}}

        {!! Form::close() !!}
    </div>
</div>