@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <!--
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal-send_by_email"
           onclick="create_pdf({{ $saleorder->id}})"><i class="fa fa-envelope-o"></i> {{trans('quotation.send_email')}}
        </a> -->
        <a href="{{url('sales_order/'.$saleorder->id.'/print_quot')}}" class="btn btn-primary"><i
                    class="fa fa-print"></i> {{trans('quotation.print')}}</a>
        @if(strtotime(date("Y-m-d"))>strtotime($saleorder->date_exp))
            <button type="button" class="btn btn-danger">{{trans('quotation.expired')}}</button>
        @else
            @if($user_data->hasAccess(['invoices.write']) && in_array($saleorder->status_order, array(1,2,3)) || $user_data->inRole('admin') && $saleorder->status == trans('sales_order.salesorder_sent'))
                <a href="{{url('sales_order/'.$saleorder->id.'/make_invoice')}}" class="btn btn-primary"><i
                            class="fa fa-share"></i> {{trans('quotation.invoice')}}</a>
            @endif
        @endif
    </div>
    <!-- ./ notifications -->
    @include('user/'.$type.'/_form')

    @if($user_data->inRole('admin'))
        <fieldset>
            <legend>{{trans('profile.history')}}</legend>
            <ul>
                @foreach($saleorder->revisionHistory as $history )
                    <li>{{ $history->userResponsible()->first_name }} changed {{ $history->fieldName() }}
                        from {{ $history->oldValue() }} to {{ $history->newValue() }}</li>
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
                        {!! Form::open(['url' => $type.'/send_saleorder', 'method' => 'post', 'files'=> true, 'id'=>'send_saleorder', 'name'=>"send_saleorder"]) !!}
                        {!! Form::hidden('saleorder_id', $saleorder->id, ['class' => 'form-control', 'id'=>"saleorder_id"]) !!}

                        <div class="form-group">
                            {!! Form::label('email_subject', trans('quotation.subject'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::text('email_subject', "Order (Ref ".$saleorder->sale_number.')'
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
                           Hello {{ (isset($saleorder->lead)?$saleorder->lead->contact_name:"")}}
                            ,</p>
                            <p>Đơn hàng của A/C: </p>
                            <p class="show-para-inv">
                                &nbsp;&nbsp;<strong>REFERENCES</strong><br>
                                &nbsp;&nbsp;Order number:
                                <strong>{{ $saleorder->sale_number}}</strong><br>
                                &nbsp;&nbsp;Order total: <strong>{{ $saleorder->grand_total}}</strong><br>
                                &nbsp;&nbsp;Order date: {{ date('m/d/Y H:i', strtotime($saleorder->start_date))}}
                                <br>
                                </p>
                       </textarea>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="control-label">{{trans('quotation.file')}}</label>
                            <a href="" id="pdf_url" target="_blank"></a>
                            <input type="hidden" name="saleorder_pdf" id="saleorder_pdf" value=""
                                   class="form-control">
                        </div>
                        <div class="modal-footer text-center">
                            <div id="sendby_submitbutton">
                                <button type="submit"
                                        class="btn btn-embossed btn-primary sendmail">{{trans('quotation.send')}}</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
@stop