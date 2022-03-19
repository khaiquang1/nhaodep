@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <a href="#" class="btn btn-primary m-b-10" data-toggle="modal" data-target="#modal-send_by_email"
           onclick="create_pdf({{ $quotation->id}})"><i class="fa fa-envelope-o"></i> {{trans('quotation.send_email')}}
        </a>
        <a href="{{url('quotation/'.$quotation->id.'/print_quot')}}" class="btn btn-primary m-b-10">
            <i class="fa fa-print"></i> {{trans('quotation.print')}}
        </a>
        @if(strtotime(date("m/d/Y"))>strtotime("+".$quotation->payment_term."",strtotime($quotation->expire_date)))
            <button type="button" class="btn btn-danger m-b-10">{{trans('quotation.expired')}}</button>
        @else
            {{--@if($user_data->hasAccess(['invoices.write']) || $user_data->inRole('admin'))--}}
                {{--<a href="{{url('quotation/'.$quotation->id.'/make_invoice')}}" class="btn btn-primary m-b-10">--}}
                    {{--<i class="fa fa-share"></i> {{trans('quotation.invoice')}}--}}
                {{--</a>--}}
            {{--@endif--}}
            @if($user_data->hasAccess(['sales_orders.write']) && $quotation->status == 'Quotation Accepted' || $user_data->inRole('admin') && $quotation->status == 'Quotation Accepted')
                <a href="{{ url('quotation/'.$quotation->id.'/confirm_sales_order' ) }}" class="btn btn-primary m-b-10">
                    <i class="fa fa-check"></i> {{ trans("table.confirm_sales_order") }}
                </a>
            @endif
        @endif
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')

    @if($user_data->inRole('admin'))
        <fieldset>
            <legend>{{trans('profile.history')}}</legend>
            <ul>
                @foreach($quotation->revisionHistory as $history )
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
            </ul>
        </fieldset>
        @endif

                <!-- START MODAL SEND BY EMAIL CONTENT -->
        <div class="modal fade" id="modal-send_by_email" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-times-circle-o"></i>
                        </button>
                        <h4 class="modal-title">
                            <strong>{{trans('quotation.send')}} </strong>{{trans('quotation.by_email')}}
                        </h4>
                    </div>
                    <div id="sendby_ajax center-edit">
                    </div>
                    <div class="modal-body">
                        {!! Form::open(['url' => $type.'/send_quotation', 'method' => 'post', 'files'=> true, 'id'=>'send_quotation', 'name'=>"send_quotation"]) !!}
                        {!! Form::hidden('quotation_id', $quotation->id, ['class' => 'form-control', 'id'=>"quotation_id"]) !!}

                        <div class="form-group">
                            {!! Form::label('email_subject', trans('quotation.subject'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::text('email_subject', "Quotation (Ref ".$quotation->quotations_number.')'
                                , ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('recipients', trans('quotation.recipients'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::select('recipients[]', isset($email_recipients)?$email_recipients:null, null, ['id'=>'recipients','class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="control-label"></label>
                       <textarea name="message_body" id="message_body" cols="80" rows="10" class="cke-editor resize_vertical">
                       	<p>
                            Hello {{ (isset($quotation->customer)?$quotation->customer->full_name:"")}}
                            ,</p>
                            <p>Here is your order confirmation from Demo Company: </p>
                            <p class="show-para-inv">
                                &nbsp;&nbsp;<strong>REFERENCES</strong><br>
                                &nbsp;&nbsp;Order number:
                                <strong>{{ $quotation->quotations_number}}</strong><br>
                                &nbsp;&nbsp;Order total: <strong>{{ $quotation->grand_total}}</strong><br>
                                &nbsp;&nbsp;Order date: {{ date('m/d/Y H:i', strtotime($quotation->start_date))}}
                                <br>
                            </p>
                       </textarea>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="control-label">{{trans('quotation.file')}}</label>
                            <a href="" id="pdf_url" target="_blank"></a>
                            <input type="hidden" name="quotation_pdf" id="quotation_pdf" value=""
                                   class="form-control">
                        </div>
                        <div class="modal-footer text-center">
                            <div id="sendby_submitbutton">
                                <button type="submit"
                                        class="btn btn-embossed btn-primary sendmail">{{trans('quotation.send')}}</button>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
@stop
