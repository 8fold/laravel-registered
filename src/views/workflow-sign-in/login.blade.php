@extends('registered::layouts.app', [
    'page_title' => 'Sign in | 8fold Professionals'
])
@section('main')
{!! UIKit::form([
    'attributes' => [
        'action' => url('/login'),
        'method' => 'post'
    ],
    'content' => [
        [
            'element' => 'Eightfold\UIKit\UIKit::textinput',
            'label' => trans('registered::registration.username_email'),
            'hint' => 'samhall or sam@8fold.pro',
            'name' => 'username',
            'error' => ($errors->has('username'))
                ? $errors->first('username')
                : ''
        ],
        [
            'element' => 'Eightfold\UIKit\UIKit::textinput',
            'type' => 'password',
            'label' => trans('registered::registration.password'),
            'name' => 'password',
            'error' => ($errors->has('password'))
                ? $errors->first('password')
                : ''
        ],
        [
            'element' => 'Eightfold\UIKit\UIKit::formbutton',
            'label' => trans('registered::registration.sign_in')
        ],
        [
            'element' => 'Eightfold\UIKit\UIKit::hiddeninput',
            'name' => '_token',
            'value' => csrf_token()
        ]
    ]
]) !!}
@endsection
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
