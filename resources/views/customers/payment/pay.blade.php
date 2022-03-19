@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

@section('styles')
    <script>
        var stripe_key = '{{Settings::get('stripe_publishable')}}';
        var action = '{{url($type.'/'.$invoice->id.'/stripe')}}';
    </script>

    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/stripe/index.js') }}" data-rel-js></script>

    <link rel="stylesheet" href="{{ asset('css/stripe/example2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stripe/index.css') }}">
@stop

{{-- Content --}}

@section('content')
    <div class="page-header clearfix">
    </div>
    <div class="panel panel-primary">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open(['url' => url('customers/payment/'.$invoice->id.'/paypal'), 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group">
                                {!! Form::label('title', trans('invoice.invoice_number'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::label('invoice_number', $invoice->invoice_number, null, ['id'=>'invoice_number', 'class' => 'form-control']) !!}
                                    {!! Form::hidden('invoice_number', $invoice->invoice_number) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('title', trans('invoice.unpaid_amount'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::label('unpaid_amount', (Settings::get('currency_position')=='left')?
                                        Settings::get('currency').$invoice->unpaid_amount:
                                        $invoice->unpaid_amount.' '.Settings::get('currency'), null, ['id'=>'invoice_number', 'class' => 'form-control']) !!}
                                    {!! Form::hidden('unpaid_amount', $invoice->unpaid_amount) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                @if(Settings::get('paypal_mode')=='sandbox')
                                    @if(Settings::get('paypal_sandbox_username')!="" && Settings::get('paypal_sandbox_password')!="")
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fa fa-check-square-o"></i> {{trans('payment.pay_paypal')}}
                                        </button>
                                    @endif
                                @else
                                    @if(Settings::get('paypal_live_username')!="" && Settings::get('paypal_live_password')!="")
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fa fa-check-square-o"></i> {{trans('payment.pay_paypal')}}
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            {{--<div class="form-group">--}}
                {{--@if(Settings::get('stripe_secret')!="" && Settings::get('stripe_publishable')!="")--}}
                    {{--{!! Form::open(['url' => url('customers/payment/'.$invoice->id.'/stripe'), 'method' => 'post']) !!}--}}
                    {{--<script--}}
                            {{--src="https://checkout.stripe.com/checkout.js" class="stripe-button"--}}
                            {{--data-key="{!!Settings::get('stripe_secret') !!}"--}}
                            {{--data-amount="{!! $invoice->unpaid_amount*100 !!}"--}}
                            {{--data-name="{!! $invoice->invoice_number !!}"--}}
                            {{--data-currency="{!! Settings::get('currency') !!}"--}}
                            {{--data-locale="auto">--}}
                    {{--</script>--}}
                    {{--{!! Form::close() !!}--}}
                {{--@endif--}}
            {{--</div>--}}
            <div>
                @if(Settings::get('stripe_secret')!="" && Settings::get('stripe_publishable')!="")
                    <button class="btn btn-primary pay_with_card">Pay With Card</button>
                    @endif
            </div>
            <div class="payWithCardModal">
                <div class="overlay">
                    <div class="overlay-Background"></div>
                </div>
                <div class="globalContent">
                    <main>
                        <div class="stripes">
                            <div class="stripe s1"></div>
                            <div class="stripe s2"></div>
                            <div class="stripe s3"></div>
                        </div>
                        <section class="container-lg">
                            <!--Example 1-->
                            <div class="cell example example2">
                                <div class="modal-header">
                                    <h3 class="modal-title inline-block">{{$invoice->invoice_number}}</h3>
                                    <span class="float-right modal_close">
                                        <span aria-hidden="true">×</span>
                                    </span>
                                </div>
                                <div class="modal-body">
                                    {!! Form::open(['url' => url('customers/payment/'.$invoice->id.'/stripe'), 'method' => 'post','id' =>'payment_form']) !!}
                                    <div class="row">
                                        <div class="field">
                                            <input id="example2-address" data-tid="elements_examples.form.address_placeholder" class="input empty" type="text" placeholder="185 Berry St" required="">
                                            <label for="example2-address" data-tid="elements_examples.form.address_label">Address</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="field">
                                            <input id="example2-city" data-tid="elements_examples.form.city_placeholder" class="input empty" type="text" placeholder="San Francisco" required="">
                                            <label for="example2-city" data-tid="elements_examples.form.city_label">City</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="field half-width">
                                            <input id="example2-state" data-tid="elements_examples.form.state_placeholder" class="input empty" type="text" placeholder="CA" required="">
                                            <label for="example2-state" data-tid="elements_examples.form.state_label">State</label>
                                            <div class="baseline"></div>
                                        </div>
                                        <div class="field half-width">
                                            <input id="example2-zip" data-tid="elements_examples.form.postal_code_placeholder" class="input empty" type="text" placeholder="94107" required="">
                                            <label for="example2-zip" data-tid="elements_examples.form.postal_code_label">ZIP</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="field">
                                            <div id="example2-card-number" class="input empty"></div>
                                            <label for="example2-card-number" data-tid="elements_examples.form.card_number_label">Card number</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="field half-width">
                                            <div id="example2-card-expiry" class="input empty"></div>
                                            <label for="example2-card-expiry" data-tid="elements_examples.form.card_expiry_label">Expiration</label>
                                            <div class="baseline"></div>
                                        </div>
                                        <div class="field half-width">
                                            <div id="example2-card-cvc" class="input empty"></div>
                                            <label for="example2-card-cvc" data-tid="elements_examples.form.card_cvc_label">CVC</label>
                                            <div class="baseline"></div>
                                        </div>
                                    </div>
                                    <button type="submit" data-tid="elements_examples.form.pay_button">Pay ${{$invoice->unpaid_amount}}</button>
                                    <div class="error" role="alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17">
                                            <path class="base" fill="#000"
                                                  d="M8.5,17 C3.80557963,17 0,13.1944204 0,8.5 C0,3.80557963 3.80557963,0 8.5,0 C13.1944204,0 17,3.80557963 17,8.5 C17,13.1944204 13.1944204,17 8.5,17 Z"></path>
                                            <path class="glyph" fill="#FFF"
                                                  d="M8.5,7.29791847 L6.12604076,4.92395924 C5.79409512,4.59201359 5.25590488,4.59201359 4.92395924,4.92395924 C4.59201359,5.25590488 4.59201359,5.79409512 4.92395924,6.12604076 L7.29791847,8.5 L4.92395924,10.8739592 C4.59201359,11.2059049 4.59201359,11.7440951 4.92395924,12.0760408 C5.25590488,12.4079864 5.79409512,12.4079864 6.12604076,12.0760408 L8.5,9.70208153 L10.8739592,12.0760408 C11.2059049,12.4079864 11.7440951,12.4079864 12.0760408,12.0760408 C12.4079864,11.7440951 12.4079864,11.2059049 12.0760408,10.8739592 L9.70208153,8.5 L12.0760408,6.12604076 C12.4079864,5.79409512 12.4079864,5.25590488 12.0760408,4.92395924 C11.7440951,4.59201359 11.2059049,4.59201359 10.8739592,4.92395924 L8.5,7.29791847 L8.5,7.29791847 Z"></path>
                                        </svg>
                                        <span class="message"></span></div>
                                    {!! Form::close() !!}
                                    <div class="success">
                                        <div class="icon">
                                            <svg width="84px" height="84px" viewBox="0 0 84 84" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <circle class="border" cx="42" cy="42" r="40" stroke-linecap="round" stroke-width="4" stroke="#000" fill="none"></circle>
                                                <path class="checkmark" stroke-linecap="round" stroke-linejoin="round" d="M23.375 42.5488281 36.8840688 56.0578969 64.891932 28.0500338" stroke-width="4" stroke="#000" fill="none"></path>
                                            </svg>
                                        </div>
                                        <h3 class="title" data-tid="elements_examples.success.title">Payment successful</h3>
                                        <a class="reset" href="#">
                                            <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <path fill="#000000" d="M15,7.05492878 C10.5000495,7.55237307 7,11.3674463 7,16 C7,20.9705627 11.0294373,25 16,25 C20.9705627,25 25,20.9705627 25,16 C25,15.3627484 24.4834055,14.8461538 23.8461538,14.8461538 C23.2089022,14.8461538 22.6923077,15.3627484 22.6923077,16 C22.6923077,19.6960595 19.6960595,22.6923077 16,22.6923077 C12.3039405,22.6923077 9.30769231,19.6960595 9.30769231,16 C9.30769231,12.3039405 12.3039405,9.30769231 16,9.30769231 L16,12.0841673 C16,12.1800431 16.0275652,12.2738974 16.0794108,12.354546 C16.2287368,12.5868311 16.5380938,12.6540826 16.7703788,12.5047565 L22.3457501,8.92058924 L22.3457501,8.92058924 C22.4060014,8.88185624 22.4572275,8.83063012 22.4959605,8.7703788 C22.6452866,8.53809377 22.5780351,8.22873685 22.3457501,8.07941076 L22.3457501,8.07941076 L16.7703788,4.49524351 C16.6897301,4.44339794 16.5958758,4.41583275 16.5,4.41583275 C16.2238576,4.41583275 16,4.63969037 16,4.91583275 L16,7 L15,7 L15,7.05492878 Z M16,32 C7.163444,32 0,24.836556 0,16 C0,7.163444 7.163444,0 16,0 C24.836556,0 32,7.163444 32,16 C32,24.836556 24.836556,32 16,32 Z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>


                        </section>
                    </main>
                </div>
            </div>
            <div class="form-group m-t-20">
                <div class="controls">
                    <a href="{{ url('customers/invoice/'.$invoice->id.'/show') }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                </div>
            </div>
        </div>
    </div>
@stop

{{-- page level scripts --}}
@section('footer_scripts')

@stop
@section('scripts')
    <script src="{{ asset('js/stripe/example2.js') }}" data-rel-js></script>
    <script>
        $(document).ready(function(){
            $(".pay_with_card").on("click",function(){
                $(".payWithCardModal").show();
            });
            $(".reset,.modal_close").on("click",function(){
                $(".payWithCardModal").hide();
            });
        })
    </script>
    @endsection

