@extends('registered::layouts.app')

@section('content')
{{-- @if ($invitationRequired) --}}
{{-- {!! UIKit::alert([
    'title' => 'Limited release',
    'text' => '<p>8fold.pro is currently an invitation only site. If you have not received an invitation code, you will not be able to register.</p>'
]) !!}
@endif --}}
<section class="ef-content">
    <form class="ef-width-one-half" role="form" method="POST" action="{{ url('/register') }}">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'type'=> 'email',
            'label' => trans('registered::registration.email_address'),
            'name' => 'email',
            'value' => (isset($email)) ? $email : null,
            'placeholder' => 'john@example.com',
            'error' => ($errors->has('email')) ? $errors->first('email') : ''
        ]) !!}
        {!! UIKit::textInput([
            'label' => trans('registered::registration.username'),
            'name' => 'username',
            'hint' => trans('registered::registration.username_hint'),
            'placeholder' => 'johnsmith',
            'error' => ($errors->has('username')) ? $errors->first('username') : ''
        ]) !!}
        @if ($invitationRequired)
            {!! UIKit::textInput([
                'label' => trans('registered::registration.invitation_code'),
                'name' => 'invite_code',
                'error' => ($errors->has('invite_code')) ? $errors->first('invite_code') : ''
            ]) !!}
            @if (isset($invitationToken))
                <input type="hidden" name="invitation_token" value="{{ $invitationToken }}">
            @endif
        @endif
        {!! UIKit::button([
            'label' => trans('registered::registration.register')
        ]) !!}
    </form>
</section>
@endsection
