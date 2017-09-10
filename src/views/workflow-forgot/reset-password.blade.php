@extends('registered::layouts.app')
@section('content')
<section class="ef-content">
    <form role="form" method="POST" action="{{ url('/reset-password') }}">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'label' => 'Username',
            'name' => 'username',
            'error' => ($errors->has('username')) ? $errors->first('username') : ''
        ]) !!}
        {!! UIKit::textInput([
            'label' => 'Reset code',
            'name' => 'reset_code',
            'error' => ($errors->has('reset_code')) ? $errors->first('reset_code') : ''
        ]) !!}
        {!! UIKit::textInput([
            'type'=> 'password',
            'label' => 'New password',
            'name' => 'new_password',
            'error' => ($errors->has('new_password')) ? $errors->first('new_password') : ''
        ]) !!}
        {!! UIKit::textInput([
            'type'=> 'password',
            'label' => 'Confirm new password',
            'name' => 'confirm_password',
            'error' => ($errors->has('confirm_password')) ? $errors->first('confirm_password') : ''
        ]) !!}
        {!! UIKit::hiddenInput([
            'name' => 'reset_token',
            'value' => Request::get('token')
        ]) !!}
        {!! UIKit::button([
            'label' => 'update password'
        ]) !!}
    </form>
</section>
@endsection
