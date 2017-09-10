@extends('registered::layouts.app')
@section('content')
<section class="ef-content">
    <div class="ef-width-one-half">
    <form role="form" method="POST" action="{{ url('/login') }}">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'label' => trans('registered::registration.username_email'),
            'hint' => 'samhall or sam@8fold.pro',
            'name' => 'username',
            'error' => ($errors->has('username')) ? $errors->first('username') : ''
        ]) !!}
        {!! UIKit::textInput([
            'type' => 'password',
            'label' => trans('registered::registration.password'),
            'name' => 'password',
            'error' => ($errors->has('password')) ? $errors->first('password') : ''
        ]) !!}
        {!! UIKit::button([
            'label' => trans('registered::registration.sign_in')
        ]) !!}
    </form>
    <p><a href="/forgot-password">Forgot password?</a></p>
    </div>
</section>
@endsection
