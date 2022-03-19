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
        @if (isset($libcontent))
            {!! Form::model($libcontent, ['url' => $type . '/' . $libcontent->id, 'method' => 'put', 'files'=> true, 'id'=>'libcontent']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'libcontent']) !!}
        @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('libcontent') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('libcontent.title'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('title', null, ['class' => 'form-control', 'placeholder'=>'Tiêu đề']) !!}
                            <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('type', trans('libcontent.type_content'), ['class' => 'control-label required', 'placeholder' => 'Please select']) !!}
                        <div class="controls">
                            <select name="type" id="type" class="form-control">
                                <option value="text">Nội dung</option>
                                <option value="video">Video</option>
                                <option value="photo">Hình</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                        {!! Form::label('content', trans('libcontent.content'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('content', null, ['class' => 'form-control resize_vertical','placeholder'=>'Nội dung', 'rows'=>20, 'style="height: auto!important;"']) !!}
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" value="1" name="status" id="status" class='icheck'
                                @if(isset($libcontent) && $libcontent->status==1)checked @endif>
                            {{trans('libcontent.status')}} </label>
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

@section('scripts')
    <script>
       
    </script>
@stop