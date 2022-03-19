@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <a href="{{ url($type . '/'.$opportunity->id.'/convert_to_quotation/') }}"
           class="btn btn-primary" target="">{{trans('opportunity.convert_to_quotation')}}</a>
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')
    @if($user_data->inRole('admin'))
        <fieldset>
            <legend>{{trans('profile.history')}}</legend>
            <ul>
                @if($opportunity)
                    @foreach($opportunity->revisionHistory as $history )
                        <li>{{ $history->userResponsible()->first_name . ' '. trans('dashboard.changed') . ' '. $history->fieldName() }}
                            @if($history->fieldName() == 'sales_person'
                            && !is_null(\App\Models\User::find($history->oldValue()))
                            && !is_null(\App\Models\User::find($history->newValue())))
                                {{trans('dashboard.from').' '. \App\Models\User::find($history->oldValue())->full_name.
                                ' '. trans('dashboard.from').' '. \App\Models\User::find($history->newValue())->full_name }}
                            @elseif($history->fieldName() == 'sales_team'
                                && !is_null(\App\Models\Salesteam::find($history->oldValue()))
                                && !is_null(\App\Models\Salesteam::find($history->newValue()))))
                            {{trans('dashboard.from').' '. \App\Models\Salesteam::find($history->oldValue())->salesteam.
                            ' '. trans('dashboard.from').' '. \App\Models\Salesteam::find($history->newValue())->salesteam }}
                            @else {{trans('dashboard.from').' '. $history->oldValue().' '. trans('dashboard.from').' '. $history->newValue() }}
                            @endif</li>
                    @endforeach
                @endif
            </ul>
        </fieldset>
    @endif
@stop