@extends('layouts.user') {{-- Web site Title --}} @section('title') {{ $title }} @stop {{-- Content --}} @section('content')
<div class="page-header clearfix">
    @if($user_data->hasAccess(['leads.write']) || $user_data->inRole('admin'))
    <div class="pull-right">
        <a href="{{ url($type.'/create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> {{ trans('groupclient.create_new') }}</a>
       
    </div>
    @endif
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
            <table id="data" class="cell-border">
                <thead>
                    <tr>
                        <th width="100px">{{ trans('botcontent.stt') }}</th>
                        <th width="100px">{{ trans('botcontent.page') }}</th>
                        <th width="300px">{{ trans('botcontent.content') }}</th>
                        <th width="300px">{{ trans('botcontent.keyword') }}</th>

                        <th  width="150px" >{{ trans('botcontent.tags') }}</th>
                        <th  width="100px">{{ trans('botcontent.button_text_next') }}</th>
                        <th  width="100px">{{ trans('botcontent.keyword_text_next') }}</th>
                        <th  width="100px">{{ trans('botcontent.type_button_next') }}</th>
                        <th  width="100px">{{ trans('botcontent.date') }}</th>
                        <th  width="100px">{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($botcontentPage)
                        <?php $i=0;?>
                        @foreach($botcontentPage as $listData)
                        <?php $i++;?>
                            <tr id="lead{{$listData['id']}}">
                                <td>{{$i}}</td>
                                <td><a href="http://fb.com/{{$listData["page_id"]}}" target="_blank">{{$listData["page_id"]}}</a></td>
                                <td>{{$listData["content"]}}</td>
                                <td>{{$listData["keyword"]}}</td>
                                <td>{{$listData["tags"]}}</td>
                                <td>{{$listData["button_text_next"]}}</td>
                                <td>{{$listData["keyword_text_next"]}}</td>
                                <td>{{$listData["type_button_next"]}}</td>
                                <td>{{ $listData["created_at"] }}</td>
                                <td>
                                @if(Sentinel::getUser()->hasAccess(['leads.write']) || Sentinel::inRole('admin'))
									<a href="{{ url('botcontent/' .  $listData['id'] . '/edit' ) }}" title="{{ trans('table.edit') }}">
										<i class="fa fa-fw fa-pencil text-warning"></i> </a></a>
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
                        @include('layouts.paging', ['paginator' => $botcontentPage])
                    </div>
                </div>
            </div>    
    </div>
</div>
<script>
$(document).ready(function() {
   // $(".listselect2").select2();
  // Thêm các tùy chọn của bạn vào đây.
        /*
        $('#data').DataTable( {
                scrollY:        "400px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                autoWidth: true,
                fixedColumns:   {
                    leftColumns: 2,
                }
            } );
        } ); */
            var table = $('#data').removeAttr('width').DataTable( {
                scrollY:        "450px",
                scrollX:        true,
                scrollCollapse: true,
                paging:         false,
                searching:      false,
                fixedColumns:   {
                    leftColumns: 2,
                }
            });
        } );
    
    </script>
@stop {{-- Scripts --}}
