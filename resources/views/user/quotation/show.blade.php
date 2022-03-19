@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="page-header clearfix">
                <a href="#" class="btn btn-primary m-b-10" data-toggle="modal" data-target="#modal-send_by_email"
                   onclick="create_pdf({{ $quotation->id}})"><i
                            class="fa fa-envelope-o"></i> {{trans('quotation.send_email')}}
                </a>
                <a href="{{url('quotation/'.$quotation->id.'/print_quot')}}" class="btn btn-primary m-b-10">
                    <i class="fa fa-print"></i> {{trans('quotation.print')}}
                </a>
                @if(strtotime(date("m/d/Y"))>strtotime("+".$quotation->payment_term."",strtotime($quotation->expire_date)))
                    <button type="button" class="btn btn-danger m-b-10">{{trans('quotation.expired')}}</button>
                @else
                    @if($user_data->hasAccess(['invoices.write']) && $quotation->status == 'Quotation Accepted' || $user_data->inRole('admin') && $quotation->status == 'Quotation Accepted')
                        <a href="{{url('quotation/'.$quotation->id.'/make_invoice')}}" class="btn btn-primary m-b-10">
                            <i class="fa fa-share"></i> {{trans('quotation.invoice')}}
                        </a>
                    @endif
                    @if($user_data->hasAccess(['sales_orders.write']) && $quotation->status == 'Quotation Accepted' || $user_data->inRole('admin') && $quotation->status == 'Quotation Accepted')
                        <a href="{{ url('quotation/'.$quotation->id.'/confirm_sales_order' ) }}"
                           class="btn btn-primary m-b-10">
                            <i class="fa fa-check"></i> {{ trans("table.confirm_sales_order") }}
                        </a>
                    @endif
                @endif
            </div>
            <div class="details">
                @include('user/'.$type.'/_details')
            </div>
        </div>
    </div>

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
                <div id="sendby_ajax" class="center-edit"></div>
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
                        <button type="submit"
                                class="btn btn-embossed btn-primary sendmail">{{trans('quotation.send')}}</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script>
        $(document).ready(function(){
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });
        });
        function create_pdf(quotation_id) {
            $.ajax({
                type: "GET",
                url: "{{url('quotation' )}}/" + quotation_id + "/ajax_create_pdf",
                data: {'_token': '{{csrf_token()}}'},
                success: function (msg) {
                    if (msg != '') {
                        $("#pdf_url").attr("href", msg);
                        var index = msg.lastIndexOf("/") + 1;
                        var filename = msg.substr(index);
                        $("#pdf_url").html(filename);
                        $("#quotation_pdf").val(filename);
                    }
                }
            });
        }
        $("#send_quotation").bootstrapValidator({
            fields: {
                'recipients[]': {
                    validators: {
                        notEmpty: {
                            message: 'The recipients field is required'
                        }
                    }
                },
                message_body:{
                    validators: {
                        notEmpty: {
                            message: 'The message field is required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function(e) {
            e.preventDefault();
            $.post( "{{url('quotation/send_quotation')}}",
                $('#send_quotation').serialize()
            )
                .done(function( msg ) {
                    $('body,html').animate({scrollTop: 0}, 200);
                    $("#sendby_ajax").html(msg);
                    setTimeout(function(){
                        $("#sendby_ajax").hide();
                    },5000);
                    $("#modal-send_by_email").modal('hide');
                });
        });
        $("#modal-send_by_email").on('hide.bs.modal', function () {
            $("#recipients").find("option").attr('selected',false);
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });
            $("#send_quotation").data('bootstrapValidator').resetForm();
        });
    </script>
@stop