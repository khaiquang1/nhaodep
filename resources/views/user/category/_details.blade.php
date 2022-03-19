<div class="panel panel-primary">
    <div class="panel-body">
        <div class="panel-content">
            <div class="form-group">
                <h5>{{trans('category.name')}}</h5>
                <div class="controls">
                    @if (isset($category))
                        {{ $category->name }}
                    @endif
                </div>
            </div>
            <div class="form-group">
                <div class="controls">
                    @if (@$action == 'show')
                        <a href="{{ url($type) }}" class="btn btn-primary"><i class="fa fa-arrow-left"></i> {{trans('table.close')}}</a>
                    @else
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                        <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>