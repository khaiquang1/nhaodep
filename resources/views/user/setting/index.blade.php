@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" type="text/css">
@stop
{{-- Content --}}
@section('content')
    @include('vendor.flash.message')
    <div class="panel panel-primary" xmlns:v-bind="http://symfony.com/schema/routing">
        <div class="panel-body">
            {!! Form::open(['url' => '/setting', 'method' => 'post', 'files'=> true]) !!}
            <div class="nav-tabs-custom" id="setting_tabs">
                @if($errors->any())
                    <div class="form-group has-error">
                        @foreach($errors->all() as $error)
                            <span class="help-block">{{ $error }}</span>
                        @endforeach
                    </div>
                @endif
                    <ul class="nav nav-tabs Set-list" id="settings">
                        <li class="active">
                            <a href="#general_configuration"
                               data-toggle="tab" title="{{ trans('settings.general_configuration') }}"><i
                                        class="material-icons md-24">build</i></a>
                        </li>
                        <li>
                            <a href="#email_configuration"
                               data-toggle="tab" title="{{ trans('settings.email_configuration') }}"><i
                                        class="material-icons md-24">email</i></a>
                        </li>
                        <li>
                            <a href="#payment_configuration"
                               data-toggle="tab" title="{{ trans('settings.payment_configuration') }}"><i
                                        class="material-icons md-24">attach_money</i></a>
                        </li>
                        <li>
                            <a href="#start_number_prefix_configuration"
                               data-toggle="tab" title="{{ trans('settings.start_number_prefix_configuration') }}"><i
                                        class="material-icons md-24">settings_applications</i></a>
                        </li>
                        <li>
                            <a href="#pusher_configuration"
                               data-toggle="tab" title="{{ trans('settings.pusher_configuration') }}"><i
                                        class="material-icons md-24">receipt</i></a>
                        </li>
                        <li>
                            <a href="#paypal_settings"
                               data-toggle="tab" title="{{ trans('settings.paypal_settings') }}"><i
                                        class="material-icons md-24">payment</i></a>
                        </li>
                        <li>
                            <a href="#stripe_settings"
                               data-toggle="tab" title="{{ trans('settings.stripe_settings') }}"><i
                                        class="material-icons md-24">vpn_key</i></a>
                        </li>
                        <li>
                            <a href="#backup_configuration"
                               data-toggle="tab" title="{{ trans('settings.backup_configuration') }}"><i
                                        class="material-icons md-24">backup</i></a>
                        </li>
                        <li>
                            <a href="#code_configuration"
                               data-toggle="tab" title="{{ trans('settings.code_configuration') }}"><i
                                        class="material-icons md-24">code_tracking</i></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane  active" id="general_configuration" >
                            <div class="form-group required {{ $errors->has('site_logo') ? 'has-error' : '' }} ">
                                {!! Form::label('site_logo_file', trans('settings.site_logo'), ['class' => 'control-label']) !!}
                                <div class="controls row" v-image-preview>
                                    <div class="col-xs-12">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                                <img id="image-preview" width="300">
                                                <img src="{{ url('uploads/site/'.Settings::get('site_logo')) }}"
                                                     alt="Image" class="ima-responsive">
                                            </div>
                                            <div>
                                           <span class="btn btn-default btn-file">
                                        <span class="fileinput-new">{{trans('Select Logo')}}</span>
                                        <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                                        <input type="file" name="site_logo_file">
                                    </span>
                                                <a href="#" class="btn btn-default fileinput-exists"
                                                   data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <span class="help-block">{{ $errors->first('site_logo', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('pdf_logo') ? 'has-error' : '' }} ">
                                {!! Form::label('pdf_logo_file', trans('settings.pdf_logo'), ['class' => 'control-label']) !!}
                                <div class="controls row" v-image-preview>
                                    <div class="col-xs-12">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                                <img id="image-preview" width="300">
                                                @if(isset($settings['pdf_logo']))
                                                    <img src="{{ url('uploads/site/'.Settings::get('pdf_logo')) }}"
                                                         alt="Image" class="ima-responsive">
                                                @endif
                                            </div>
                                            <div>
                                            <span class="btn btn-default btn-file">
                                        <span class="fileinput-new">{{trans('Select Logo')}}</span>
                                        <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                                        <input type="file" name="pdf_logo_file">
                                    </span>
                                                <a href="#" class="btn btn-default fileinput-exists"
                                                   data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <span class="help-block">{{ $errors->first('pdf_logo', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('site_name') ? 'has-error' : '' }}">
                                {!! Form::label('site_name', trans('settings.site_name'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('site_name', old('site_name', Settings::get('site_name')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('site_name', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('address1') ? 'has-error' : '' }}">
                                {!! Form::label('site_name', trans('settings.address1'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('address1', old('address1', Settings::get('address1')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('address1', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('address2') ? 'has-error' : '' }}">
                                {!! Form::label('address2', trans('settings.address2'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('address2', old('address2', Settings::get('address2')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('address2', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('site_email') ? 'has-error' : '' }}">
                                {!! Form::label('site_email', trans('settings.site_email'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('site_email', old('site_email', Settings::get('site_email')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('site_email', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('phone') ? 'has-error' : '' }}">
                                {!! Form::label('phone', trans('settings.phone'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('phone', old('phone', Settings::get('phone')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('fax') ? 'has-error' : '' }}">
                                {!! Form::label('fax', trans('settings.fax'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('fax', old('fax', Settings::get('fax')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('currency') ? 'has-error' : '' }}">
                                {!! Form::label('currency', trans('settings.currency'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('currency', $currency, old('currency', Settings::get('currency')), ['id'=>'currency','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('currency', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('currency_position') ? 'has-error' : '' }}">
                                {!! Form::label('currency', trans('settings.currency_position'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('currency_position', $currency_positions, old('currency_position', Settings::get('currency_position')), ['id'=>'currency_position','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('currency_position', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('allowed_extensions') ? 'has-error' : '' }}">
                                {!! Form::label('allowed_extensions', trans('settings.allowed_extensions'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('allowed_extensions', old('allowed_extensions', Settings::get('allowed_extensions')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('allowed_extensions', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('max_upload_file_size') ? 'has-error' : '' }}">
                                {!! Form::label('max_upload_file_size', trans('settings.max_upload_file_size'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('max_upload_file_size', $max_upload_file_size, old('max_upload_file_size', Settings::get('max_upload_file_size')), ['id'=>'max_upload_file_size','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('max_upload_file_size', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('minimum_characters') ? 'has-error' : '' }}">
                                {!! Form::label('minimum_characters', trans('settings.minimum_characters'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('minimum_characters', old('minimum_characters', Settings::get('minimum_characters')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('minimum_characters', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('date_format') ? 'has-error' : '' }}">
                                {!! Form::label('date_format', trans('settings.date_format'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    <div class="radio">
                                        {!! Form::radio('date_format', 'F j,Y',(Settings::get('date_format')=='F j,Y')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('true', date('F j,Y'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('date_format', 'Y-d-m',(Settings::get('date_format')=='Y-d-m')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('date_format', date('Y-d-m'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('date_format', 'd.m.Y.',(Settings::get('date_format')=='d.m.Y.')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('date_format', date('d.m.Y.'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('date_format', 'd.m.Y',(Settings::get('date_format')=='d.m.Y')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('date_format', date('d.m.Y'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('date_format', 'd/m/Y',(Settings::get('date_format')=='d/m/Y')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('date_format', date('d/m/Y'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('date_format', 'm/d/Y',(Settings::get('date_format')=='m/d/Y')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('date_format', date('m/d/Y'))  !!}
                                    </div>
                                    <div class="form-inline">
                                        {{-- {!! Form::radio('date_format', 'm/d/Y',false,['class' => 'icheck', 'id'=>'date_format_custom_radio'))  !!} --}}
                                        {!! Form::label('custom_format', trans('settings.custom_format'))  !!}
                                        {!! Form::input('text','date_format_custom', Settings::get('date_format'), ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <span class="help-block">{{ $errors->first('date_format', ':message') }}</span>
                            </div>
                            <a href="{{url('http://php.net/manual/en/function.date.php')}}">{!! trans('settings.date_time_format') !!}</a>
                            <div class="form-group required {{ $errors->has('time_format') ? 'has-error' : '' }}">
                                {!! Form::label('time_format', trans('settings.time_format'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    <div class="radio">
                                        {!! Form::radio('time_format', 'g:i a',(Settings::get('time_format')=='g:i a')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('time_format', date('g:i a'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('time_format', 'g:i A',(Settings::get('time_format')=='g:i A')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('time_format', date('g:i A'))  !!}
                                    </div>
                                    <div class="radio">
                                        {!! Form::radio('time_format', 'H:i',(Settings::get('time_format')=='H:i')?true:false,['class' => 'icheck'])  !!}
                                        {!! Form::label('time_format', date('H:i'))  !!}
                                    </div>
                                    <div class="form-inline">
                                        {{-- {!! Form::radio('time_format', 'H:i',false,['class' => 'icheck', 'id'=>'time_format_custom_radio'))  !!} --}}
                                        {!! Form::label('custom_format', trans('settings.custom_format'))  !!}
                                        {!! Form::input('text','time_format_custom', Settings::get('time_format'), ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <span class="help-block">{{ $errors->first('date_format', ':message') }}</span>
                            </div>
                            <div class="form-group required {{ $errors->has('language') ? 'has-error' : '' }}">
                                {!! Form::label('language', trans('settings.language'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('language', $languages, old('language', Settings::get('language')), ['id'=>'language','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('language', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="email_configuration" >
                            <div class="form-group required {{ $errors->has('email_driver') ? 'has-error' : '' }}">
                                {!! Form::label('email_driver', trans('settings.email_driver'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    <div class="form-inline">
                                        <div class="radio">
                                            {!! Form::radio('email_driver', 'mail',(Settings::get('email_driver')=='mail')?true:false,['id'=>'mail','class' => 'email_driver icheck'])  !!}
                                            {!! Form::label('true', 'MAIL')  !!}
                                        </div>
                                        <div class="radio">
                                            {!! Form::radio('email_driver', 'smtp', (Settings::get('email_driver')=='smtp')?true:false,['id'=>'smtp','class' => 'email_driver icheck'])  !!}
                                            {!! Form::label('false', 'SMTP') !!}
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('email_driver', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group smtp required {{ $errors->has('email_host') ? 'has-error' : '' }}">
                                {!! Form::label('email_host', trans('settings.email_host'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','email_host', old('email_host', Settings::get('email_host')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('email_host', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group smtp required {{ $errors->has('email_port') ? 'has-error' : '' }}">
                                {!! Form::label('email_port', trans('settings.email_port'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','email_port', old('email_port', Settings::get('email_port')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('email_port', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group smtp required {{ $errors->has('email_username') ? 'has-error' : '' }}">
                                {!! Form::label('email_username', trans('settings.email_username'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','email_username', old('email_username', Settings::get('email_username')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('email_username', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group smtp required {{ $errors->has('email_password') ? 'has-error' : '' }}">
                                {!! Form::label('email_password', trans('settings.email_password'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','email_password', old('email_password', Settings::get('email_password')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('email_password', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="payment_configuration" >
                            <div class="form-group required {{ $errors->has('sales_tax') ? 'has-error' : '' }}">
                                {!! Form::label('sales_tax', trans('settings.sales_tax').'%', ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','sales_tax', old('sales_tax', floatval(Settings::get('sales_tax'))), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('sales_tax', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('payment_term1') ? 'has-error' : '' }}">
                                {!! Form::label('payment_term1', trans('settings.payment_term1'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','payment_term1', old('payment_term1', Settings::get('payment_term1')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('payment_term1', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('payment_term2') ? 'has-error' : '' }}">
                                {!! Form::label('payment_term2', trans('settings.payment_term2'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','payment_term2', old('payment_term2', Settings::get('payment_term2')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('payment_term2', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('payment_term3') ? 'has-error' : '' }}">
                                {!! Form::label('payment_term3', trans('settings.payment_term3'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','payment_term3', old('payment_term3', Settings::get('payment_term3')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('payment_term3', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('opportunities_reminder_days') ? 'has-error' : '' }}">
                                {!! Form::label('opportunities_reminder_days', trans('settings.opportunities_reminder_days'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','opportunities_reminder_days', old('opportunities_reminder_days', Settings::get('opportunities_reminder_days')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('opportunities_reminder_days', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('contract_renewal_days') ? 'has-error' : '' }}">
                                {!! Form::label('contract_renewal_days', trans('settings.contract_renewal_days'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','contract_renewal_days', old('contract_renewal_days', Settings::get('contract_renewal_days')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('contract_renewal_days', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('invoice_reminder_days') ? 'has-error' : '' }}">
                                {!! Form::label('invoice_reminder_days', trans('settings.invoice_reminder_days'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','invoice_reminder_days', old('invoice_reminder_days', Settings::get('invoice_reminder_days')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('invoice_reminder_days', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="start_number_prefix_configuration">
                            <div class="form-group required {{ $errors->has('quotation_prefix') ? 'has-error' : '' }}">
                                {!! Form::label('quotation_prefix', trans('settings.quotation_prefix'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('quotation_prefix', old('quotation_prefix', Settings::get('quotation_prefix')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('quotation_prefix', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('quotation_start_number') ? 'has-error' : '' }}">
                                {!! Form::label('quotation_start_number', trans('settings.quotation_start_number'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','quotation_start_number', old('quotation_start_number', Settings::get('quotation_start_number')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('quotation_start_number', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('quotation_template') ? 'has-error' : '' }}">
                                {!! Form::label('quotation_template', trans('settings.quotation_template'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('quotation_template', $quotation_template, old('quotation_template', Settings::get('quotation_template')), ['id'=>'quotation_template','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('quotation_template', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('sales_prefix') ? 'has-error' : '' }}">
                                {!! Form::label('sales_prefix', trans('settings.sales_prefix'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('sales_prefix', old('sales_prefix', Settings::get('sales_prefix')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('sales_prefix', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('sales_start_number') ? 'has-error' : '' }}">
                                {!! Form::label('sales_start_number', trans('settings.sales_start_number'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','sales_start_number', old('sales_start_number', Settings::get('sales_start_number')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('sales_start_number', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('saleorder_template') ? 'has-error' : '' }}">
                                {!! Form::label('saleorder_template', trans('settings.saleorder_template'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('saleorder_template', $saleorder_template, old('saleorder_template', Settings::get('saleorder_template')), ['id'=>'saleorder_template','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('saleorder_template', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('invoice_prefix') ? 'has-error' : '' }}">
                                {!! Form::label('invoice_prefix', trans('settings.invoice_prefix'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('invoice_prefix', old('invoice_prefix', Settings::get('invoice_prefix')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('invoice_prefix', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('invoice_start_number') ? 'has-error' : '' }}">
                                {!! Form::label('invoice_start_number', trans('settings.invoice_start_number'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','invoice_start_number', old('invoice_start_number', Settings::get('invoice_start_number')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('invoice_start_number', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('invoice_template') ? 'has-error' : '' }}">
                                {!! Form::label('invoice_template', trans('settings.invoice_template'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::select('invoice_template', $invoice_template, old('invoice_template', Settings::get('invoice_template')), ['id'=>'invoice_template','class' => 'form-control select2']) !!}
                                    <span class="help-block">{{ $errors->first('invoice_template', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('invoice_payment_prefix') ? 'has-error' : '' }}">
                                {!! Form::label('invoice_payment_prefix', trans('settings.invoice_payment_prefix'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::text('invoice_payment_prefix', old('invoice_payment_prefix', Settings::get('invoice_payment_prefix')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('invoice_payment_prefix', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('invoice_payment_start_number') ? 'has-error' : '' }}">
                                {!! Form::label('invoice_payment_start_number', trans('settings.invoice_payment_start_number'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('number','invoice_payment_start_number', old('invoice_payment_start_number', Settings::get('invoice_payment_start_number')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('invoice_payment_start_number', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="pusher_configuration">
                            <div class="form-group required {{ $errors->has('pusher_app_id') ? 'has-error' : '' }}">
                                {!! Form::label('pusher_app_id', trans('settings.pusher_app_id'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','pusher_app_id', old('pusher_app_id', Settings::get('pusher_app_id')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('pusher_app_id', ':message') }}</span>
                                </div>
                            </div>

                            <div class="form-group required {{ $errors->has('pusher_key') ? 'has-error' : '' }}">
                                {!! Form::label('pusher_key', trans('settings.pusher_key'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','pusher_key', old('pusher_key', Settings::get('pusher_key')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('pusher_key', ':message') }}</span>
                                </div>
                            </div>

                            <div class="form-group required {{ $errors->has('pusher_secret') ? 'has-error' : '' }}">
                                {!! Form::label('pusher_secret', trans('settings.pusher_secret'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','pusher_secret', old('pusher_secret', Settings::get('pusher_secret')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('pusher_secret', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="paypal_settings">
                            <div class="form-group required {{ $errors->has('paypal_mode') ? 'has-error' : '' }}">
                                {!! Form::label('paypal_mode', trans('settings.paypal_mode'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    <div class="form-inline">
                                        <div class="radio">
                                            {!! Form::radio('paypal_mode', 'sandbox',(Settings::get('paypal_mode')=='sandbox')?true:false,['class' => 'icheck sandbox'])  !!}
                                            {!! Form::label('true', trans('settings.sandbox'))  !!}
                                        </div>
                                        <div class="radio">
                                            {!! Form::radio('paypal_mode', 'live', (Settings::get('paypal_mode')=='live')?true:false,['class' => 'icheck live'])  !!}
                                            {!! Form::label('false', trans('settings.live')) !!}
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('paypal_mode', ':message') }}</span>
                                </div>
                            </div>
                            <div class="paypal_sandbox">
                                <div class="form-group required {{ $errors->has('paypal_sandbox_username') ? 'has-error' : '' }}">
                                    {!! Form::label('paypal_sandbox_username', trans('settings.paypal_sandbox_username'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input('text','paypal_sandbox_username', old('paypal_sandbox_username', Settings::get('paypal_sandbox_username')), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('paypal_sandbox_username', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group required {{ $errors->has('paypal_sandbox_password') ? 'has-error' : '' }}">
                                    {!! Form::label('paypal_sandbox_password', trans('settings.paypal_sandbox_password'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input('text','paypal_sandbox_password', old('paypal_sandbox_password', Settings::get('paypal_sandbox_password')), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('paypal_sandbox_password', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group required {{ $errors->has('paypal_sandbox_signature') ? 'has-error' : '' }}">
                                    {!! Form::label('paypal_sandbox_signature', trans('settings.paypal_sandbox_signature'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input('text','paypal_sandbox_signature', old('paypal_sandbox_signature', Settings::get('paypal_sandbox_signature')), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('paypal_sandbox_signature', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="paypal_live">
                                <div class="form-group required {{ $errors->has('paypal_live_username') ? 'has-error' : '' }}">
                                    {!! Form::label('paypal_live_username', trans('settings.paypal_live_username'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input('text','paypal_live_username', old('paypal_live_username', Settings::get('paypal_live_username')), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('paypal_live_username', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group required {{ $errors->has('paypal_live_password') ? 'has-error' : '' }}">
                                    {!! Form::label('paypal_live_password', trans('settings.paypal_live_password'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input('text','paypal_live_password', old('paypal_live_password', Settings::get('paypal_live_password')), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('paypal_live_password', ':message') }}</span>
                                    </div>
                                </div>

                                <div class="form-group required {{ $errors->has('paypal_live_signature') ? 'has-error' : '' }}">
                                    {!! Form::label('paypal_live_signature', trans('settings.paypal_live_signature'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input('text','paypal_live_signature', old('paypal_live_signature', Settings::get('paypal_live_signature')), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('paypal_live_signature', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="stripe_settings">
                            <div class="form-group required {{ $errors->has('stripe_publishable') ? 'has-error' : '' }}">
                                {!! Form::label('stripe_publishable', trans('settings.stripe_publishable'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','stripe_publishable', old('stripe_publishable', Settings::get('stripe_publishable')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('stripe_publishable', ':message') }}</span>
                                </div>
                            </div>
                            <div class="form-group required {{ $errors->has('stripe_secret') ? 'has-error' : '' }}">
                                {!! Form::label('stripe_secret', trans('settings.stripe_secret'), ['class' => 'control-label']) !!}
                                <div class="controls">
                                    {!! Form::input('text','stripe_secret', old('stripe_secret', Settings::get('stripe_secret')), ['class' => 'form-control']) !!}
                                    <span class="help-block">{{ $errors->first('stripe_secret', ':message') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="backup_configuration" >
                            <backup-settings type="{{ Settings::get('backup_type') }}"
                                             :options="{{ $backup_type }}" inline-template>
                                <div>
                                    <div class="form-group required {{ $errors->has('backup_type') ? 'has-error' : '' }}">
                                        {!! Form::label('backup_type', trans('settings.backup_type'), ['class' => 'control-label']) !!}
                                        <div class="controls">
                                            <select v-model="backup_type" name="backup_type" class="form-control">
                                                <option v-for="option in options" :value="option.id">@{{ option.text }}</option>
                                            </select>
                                            <span class="help-block">{{ $errors->first('backup_type', ':message') }}</span>
                                        </div>
                                    </div>
                                    {{-- Dropbox --}}
                                    <div v-if="backup_type=='dropbox'">
                                        <div class="form-group required {{ $errors->has('disk_dbox_token') ? 'has-error' : '' }}">
                                            {!! Form::label('disk_dbox_token', trans('settings.disk_dbox_token'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('disk_dbox_token', old('disk_dbox_token', Settings::get('disk_dbox_token')), ['class' => 'form-control'])
                                                !!}
                                                <span class="help-block">{{ $errors->first('disk_dbox_token', ':message') }}</span>
                                            </div>
                                        </div>
                                        <div class="form-group required {{ $errors->has('disk_dbox_app') ? 'has-error' : '' }}">
                                            {!! Form::label('disk_dbox_app', trans('settings.disk_dbox_app'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('disk_dbox_app', old('disk_dbox_app', Settings::get('disk_dbox_app')), ['class' => 'form-control']) !!}
                                                <span class="help-block">{{ $errors->first('disk_dbox_app', ':message') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="backup_type=='s3'">
                                        {{-- AWS S3 --}}
                                        <div class="form-group required {{ $errors->has('disk_aws_key') ? 'has-error' : '' }}">
                                            {!! Form::label('disk_aws_key', trans('settings.disk_aws_key'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('disk_aws_key', old('disk_aws_key', Settings::get('disk_aws_key')), ['class' => 'form-control']) !!}
                                                <span class="help-block">{{ $errors->first('disk_aws_key', ':message') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group required {{ $errors->has('disk_aws_secret') ? 'has-error' : '' }}">
                                            {!! Form::label('disk_aws_secret', trans('settings.disk_aws_secret'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('disk_aws_secret', old('disk_aws_secret', Settings::get('disk_aws_secret')), ['class' => 'form-control'])
                                                !!}
                                                <span class="help-block">{{ $errors->first('disk_aws_secret', ':message') }}</span>
                                            </div>
                                        </div>


                                        <div class="form-group required {{ $errors->has('disk_aws_bucket') ? 'has-error' : '' }}">
                                            {!! Form::label('disk_aws_bucket', trans('settings.disk_aws_bucket'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('disk_aws_bucket', old('disk_aws_bucket', Settings::get('disk_aws_bucket')), ['class' => 'form-control'])
                                                !!}
                                                <span class="help-block">{{ $errors->first('site_nbucket', ':message') }}</span>
                                            </div>
                                        </div>


                                        <div class="form-group required {{ $errors->has('disk_aws_region') ? 'has-error' : '' }}">
                                            {!! Form::label('disk_aws_region', trans('settings.disk_aws_region'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('disk_aws_region', old('disk_aws_region', Settings::get('disk_aws_region')), ['class' => 'form-control'])
                                                !!}
                                                <span class="help-block">{{ $errors->first('site_nregion', ':message') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </backup-settings>
                        </div>
                        <!-- Code tracking -->
                        <div class="tab-pane" id="code_configuration" >
                            <code-settings type="{{ Settings::get('backup_type') }}"
                                             :options="{{ $backup_type }}" inline-template>
                                <div>
                                    
                                    <div class="form-group required {{ $errors->has('backup_type') ? 'has-error' : '' }}">
                                        {!! Form::label('backup_type', trans('settings.backup_type'), ['class' => 'control-label']) !!}
                                        <div class="controls">
                                            <select v-model="backup_type" name="backup_type" class="form-control">
                                                <option v-for="option in options" :value="option.id">@{{ option.text }}</option>
                                            </select>
                                            <span class="help-block">{{ $errors->first('backup_type', ':message') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                            </code-settings>
                        </div>
                        <!-- End code tracking -->

                    </div>
            </div>
            <!-- Form Actions -->
            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                </div>
            </div>
            <!-- ./ form actions -->

            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/icheck.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            $("#backup_type").select2({
                theme: 'bootstrap',
                placeholder: 'Backup Service'
            });

            $("input[name='date_format']").on('ifChecked', function () {
                if ("date_format_custom_radio" != $(this).attr("id"))
                    $("input[name='date_format_custom']").val($(this).val()).siblings('.example').text($(this).siblings('span').text());
            });
            $("input[name='date_format_custom']").focus(function () {
                $("#date_format_custom_radio").attr("checked", "checked");
            });

            $("input[name='time_format']").on('ifChecked', function () {
                if ("time_format_custom_radio" != $(this).attr("id"))
                    $("input[name='time_format_custom']").val($(this).val()).siblings('.example').text($(this).siblings('span').text());
            });
            $("input[name='time_format_custom']").focus(function () {
                $("#time_format_custom_radio").attr("checked", "checked");
            });
            $("input[name='date_format_custom'], input[name='time_format_custom']").on('ifChecked', function () {
                var format = $(this);
                format.siblings('img').css('visibility', 'visible');
                $.post(ajaxurl, {
                    action: 'date_format_custom' == format.attr('name') ? 'date_format' : 'time_format',
                    date: format.val()
                }, function (d) {
                    format.siblings('img').css('visibility', 'hidden');
                    format.siblings('.example').text(d);
                });
            });
            $('.smtp').hide();
            $("#smtp").on("ifChecked",function(){
                $('.smtp').show();
            });
            $("#smtp").on("ifUnchecked",function(){
                $('.smtp').hide();
            });
            if($("#smtp").closest(".iradio_minimal-blue").hasClass("checked")){
                $(".smtp").show();
            }else{
                $(".smtp").hide();
            }

            $(".paypal_live,.paypal_sandbox").hide();
            if('{{ !empty(Settings::get('paypal_mode')) }}'){
                if('{{ Settings::get('paypal_mode') }}'=='sandbox'){
                    $(".paypal_sandbox").show();
                }else{
                    $(".paypal_live").show();
                }
            }
            $(".sandbox").on("ifChecked",function(){
                $(".paypal_sandbox").show();
                $(".paypal_live").hide();
            });
            $(".sandbox").on("ifUnchecked",function(){
                $(".paypal_sandbox").hide();
                $(".paypal_live").show();
            });
        })
    </script>
@stop
