@component('mail::message')
<div>
    {{ trans('emails.new_email_from') .' '. $userFrom->full_name }}
</div>
{{ trans('emails.subject') }} : {{ $subject }}
<h4>{{ trans('emails.message') }} :</h4>
{{$message}}
@endcomponent
