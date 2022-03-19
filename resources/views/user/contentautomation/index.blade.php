@extends('layouts.user') {{-- Web site Title --}} @section('title') {{ $title }} @stop {{-- Content --}} @section('content')
<div class="page-header clearfix">
    @if($user_data->hasAccess(['sales_team.write']) || $user_data->inRole('admin'))
    <div class="pull-right">
        <a href="{{ url($type.'/create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> {{ trans('tags.create_new') }}</a>
       
    </div>
    @endif
</div>
<div class="clearfix">
    {!! Form::open(['url' => 'contentautomation', 'method' => 'get', 'id'=>'search', 'files'=> false]) !!}
    <div class="row">
        <div class="col-md-10">
                <div class="form-group {{ $errors->has('keyword') ? 'has-error' : '' }}">
                    {!! Form::label('keyword',  trans('lead.keyword'), ['class' => 'control-label required', 'placeholder' => 'Name, email, phone']) !!}
                    <div class="controls">
                        {!! Form::text('keyword', isset($keyword) ? $keyword : null, ['class' => 'form-control input-sm']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label for="search" class="control-label">&nbsp;</label>
                <div class="controls">
                    <input type="submit" class="btn btn-success" name="search" value="{{trans('lead.search')}}"/>
                </div>
            </div>
    </div>
    {!! Form::close() !!}
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
                <i class="material-icons">groups</i>
                {{ $title }}
            </h4>
    </div>
    
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="10%">{{ trans('contentautomation.stt') }}</th>
                        <th width="20%">{{ trans('contentautomation.name') }}</th>
                        <th width="5%">{{ trans('contentautomation.type') }}</th>
                        <th width="50%">{{ trans('contentautomation.content') }}</th>
                        <th width="10%">{{ trans('contentautomation.status') }}</th>
                        <th width="5%">{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($contentPage)
                        @php $i=0;@endphp
                        @foreach($contentPage as $listContent)
                        @php $i++;@endphp
                        <tr id="line-parent-{{$listContent["id"]}}">
                            <td>{{$listContent["id"]}}</td>
                            <td>{{ $listContent["title"]}} <a href="javascript:void(0);" onclick="return loadparent({{$listContent["id"]}})" id="show{{$listContent["id"]}}" style="font-size:18px"><i class="fa fa-fw fa-plus text-danger"></i></a><a href="javascript:void(0);" onclick="return hideelement({{$listContent["id"]}})" id="hiden{{$listContent["id"]}}" style="display:none; font-size:18px"><i class="fa fa-fw fa-minus text-danger"></i></a></td>
                            <td>{{ $listContent["type"]}}</td> 
                            <td>{!! $listContent["reply"]!!}</td>
                            <td>@if($listContent["status"]==1) Kích hoạt @else Không kích hoạt @endif</td>
                            <td>
                            @if((isset($roleconfig) && $roleconfig==1) || $user_data->inRole('admin'))
                                <a href="{{ url('contentautomation/' .  $listContent['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
                                    <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                            @endif
                            
                            @if((isset($roleconfig) && $roleconfig==1) || Sentinel::inRole('admin'))
                            <a href="{{ url('contentautomation/' .  $listContent['id'] . '/delete' ) }}" title="{{ trans('table.delete') }}">
                                    <i class="fa fa-fw fa-trash text-danger"></i> </a>
                            @endif
                            
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="row">
                <div class="col-sm-12">
                    <div class="dataTables_info">
                        @include('layouts.paging', ['paginator' => $contentPage])
                    </div>
                </div>
        </div>
    </div>
</div>
<script language="javascript">
    function loadparent($id){
        if($id!=""){
            $('.lineparent-'+$id).remove();
            $('#show'+$id).hide();
            $('#hiden'+$id).show();
            $.ajax({
                method: "POST",
                url: "{{ url('contentautomation/getcontentchild')}}",
                data: {id: $id, _token: '{{ csrf_token() }}'},
                success: function(data) {
                    contenthtml=data.datachirl; 
                    $j=0;
                    $lineparent="";
                    $.each(contenthtml, function (i, item) {
                        $lineparent="";
                        $itemdata="";
                        $j++;
                        $title=item.title;
                        $idItem=item.id;
                        $type=item.type;
                        $reply=item.reply;
                        $status=item.status;
                        $linkedit="{{ url('contentautomation/' ) }}";
                        $deletelink="";
                        $editlink="<a href='"+$linkedit+"/"+$idItem+"/edit' title=\"{{ trans('table.edit') }}\"><i class=\"fa fa-fw fa-pencil text-warning\"></i> </a>";
                        @if((isset($roleconfig) && $roleconfig==1) || Sentinel::inRole('admin'))
                        $deletelink="<a href='"+$linkedit+"/"+$idItem+"/delete' title=\"{{ trans('table.delete') }}\"><i class=\"fa fa-fw fa-trash text-danger\"></i> </a>";
                        @endif
                        $linkchilrconent='<a href="javascript:void(0);" onclick="return loadparent('+$idItem+')" id="show'+$idItem+'" style="font-size:18px"><i class="fa fa-fw fa-plus text-danger"></i></a><a href="javascript:void(0);" onclick="return hideelement('+$idItem+')" id="hiden'+$idItem+'" style="display:none; font-size:18px"><i class="fa fa-fw fa-minus text-danger"></i></a>';
                        $lineparent+="<tr id=\"line-parent-"+$idItem+"\" class=\"lineparent-"+$id+"\">";
                            $lineparent+="<td>"+$idItem+"</td>";
                            $lineparent+="<td>"+$title+$linkchilrconent+"</td>";
                            $lineparent+="<td>"+$type+"</td>";
                            $lineparent+="<td>"+$reply+"</td>";
                            $lineparent+="<td>"+$status+"</td>";
                            $lineparent+="<td>"+$editlink+" "+$deletelink+"</td>";


                            $lineparent+="</tr>";
                        $('#line-parent-'+$id).after($lineparent);
                    });
                }
             });
        }
        
    }
    function hideelement($id) {
        $('#show'+$id).show();
        $('#hiden'+$id).hide();
        $('.lineparent-'+$id).remove();

    }
</script>
@stop {{-- Scripts --}}
