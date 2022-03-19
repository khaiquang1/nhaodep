<div class="form-group">
    <legend for="pages"><i class="material-icons md-24">pages</i> {{ trans('social.pages') }}</legend>
    <div class="controls">
        @foreach($provider->getPages() as $page)
            <div class="radio">
                {!! Form::radio('fb_page', $page->id,(Settings::get('fb_page')===$page->id)?true:false,array('class' => 'icheck'))  !!}
                {!! Form::label('true', $page->name.'('.$page->category.')')  !!}
            </div>
        @endforeach
    </div>
</div>
