@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal-send_by_email"
           onclick="create_pdf({{$invoice->id}})"><i class="fa fa-envelope-o"></i> {{trans('invoice.email_send')}}</a>
        <a href="{{url('invoice/'.$invoice->id.'/print_quot')}}" class="btn btn-primary" target=""><i
                    class="fa fa-print"></i> {{trans('invoice.print')}}</a>
        @if(strtotime(date("m/d/Y"))>strtotime("+".$invoice->payment_term."",strtotime($invoice->invoice_due_date)))
            <button type="button" class="btn btn-danger">{{trans('invoice.invoice_expired')}}</button>
        @endif
    </div>

    @include('user/'.$type.'/_form')
    @if($user_data->inRole('admin'))
        <fieldset>
            <legend>{{trans('profile.history')}}</legend>
            <ul>
                @foreach($invoice->revisionHistory as $history )
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
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                                    class="fa fa-times-circle-o"></i></button>
                        <h4 class="modal-title"><strong>{{trans('invoice.email_send')}}</strong></h4>
                    </div>
                    <div id="sendby_ajax" class="center-edit"></div>
                    {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'send_invoice', 'name'=>"send_invoice"]) !!}
                    {!! Form::hidden('invoice_id', $invoice->id, ['class' => 'form-control', 'id'=>"invoice_id"]) !!}
                    <div class="modal-body">

                        <div class="form-group">
                            {!! Form::label('email_subject', trans('invoice.subject'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::text('email_subject', "Demo Company Invoice (Ref ".$invoice->invoice_number.')'
                                , ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('recipients', trans('invoice.recipients'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::select('recipients[]', isset($email_recipients)?$email_recipients:null, null, ['id'=>'recipients','class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="control-label"></label>
                       <textarea name="message_body" id="message_body" cols="80" rows="10" class="cke-editor resize_vertical">
                       	<p>
                            Hello {{(isset($invoice->customer)?$invoice->customer->full_name:"")}}
                            ,</p>
                            <p>Here is your order confirmation from Demo Company: </p>
                            <p style="border-left: 1px solid #8e0000; margin-left: 30px;">
                                &nbsp;&nbsp;<strong>REFERENCES</strong><br>
                                &nbsp;&nbsp;Order number:
                                <strong>{{ $invoice->invoice_number}}</strong><br>
                                &nbsp;&nbsp;Order total: <strong>{{ $invoice->grand_total}}</strong><br>
                                &nbsp;&nbsp;Order date: {{ date('m/d/Y H:i', strtotime($invoice->date))}}
                                <br>
                            </p>
                       </textarea>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="control-label">{{trans('invoice.file')}}</label>
                            <a href="" id="pdf_url" target="_blank"></a>
                            <input type="hidden" name="invoice_pdf" id="invoice_pdf" value=""
                                   class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer text-center">
                        <div id="sendby_submitbutton">
                            <button type="submit"
                                    class="btn btn-primary sendmail">{{trans('invoice.send')}}</button>
                        </div>
                    </div>
                    {!! Form::close() !!}

                </div>


            </div>
        </div>
@stop
