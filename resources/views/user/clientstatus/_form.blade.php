<script src="{{ asset('admincp/js/jquery.minicolors.min.js') }}"></script>
<link href="{{ asset('admincp/css/jquery.minicolors.css') }}" media="screen" rel="stylesheet" type="text/css">
<script> 
    $(document).ready( function() {

      $('.colorbox').each( function() {
        $(this).minicolors({
          control: $(this).attr('data-control') || 'hue',
          defaultValue: $(this).attr('data-defaultValue') || '',
          format: $(this).attr('data-format') || 'hex',
          keywords: $(this).attr('data-keywords') || '',
          inline: $(this).attr('data-inline') === 'true',
          letterCase: $(this).attr('data-letterCase') || 'lowercase',
          opacity: $(this).attr('data-opacity'),
          position: $(this).attr('data-position') || 'bottom',
          swatches: $(this).attr('data-swatches') ? $(this).attr('data-swatches').split('|') : [],
          change: function(value, opacity) {
            if( !value ) return;
            if( opacity ) value += ', ' + opacity;
            if( typeof console === 'object' ) {
              console.log(value);
            }
          },
          theme: 'bootstrap'
        });

      });

    });
  </script>

<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($clientstatus))
            {!! Form::model($clientstatus, ['url' => $type . '/' . $clientstatus->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('clientstatus') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('clientstatus.name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('title', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('color_bg', trans('clientstatus.clorbg'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('color_bg', null, ['class' => 'form-control colorbox','placeholder'=>'Màu box']) !!}
                            <span class="help-block">{{ $errors->first('color_bg', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('color_text', trans('clientstatus.clortext'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('color_text', null, ['class' => 'form-control colorbox','placeholder'=>'Màu text trong box']) !!}
                            <span class="help-block">{{ $errors->first('color_text', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('position', trans('clientstatus.position'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('position', null, ['class' => 'form-control resize_vertical','placeholder'=>'Vị trí']) !!}
                            <span class="help-block">{{ $errors->first('position', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                    {!! Form::label('typeList', trans('tags.group_client'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}

                        <div class="controls">
                            {!! Form::select('type', $typeList, null, ['id'=>'type', 'class' => 'form-control select_function']) !!}
                            <span class="help-block">{{ $errors->first('type', ':message') }}</span>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($clientstatus) && $clientstatus->status==1)checked @endif>
                            {{trans('clientstatus.status')}} </label>
                    </div>
                </div>
            </div>

            <div class="row">
                
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
