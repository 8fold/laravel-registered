@extends('registered::layouts.app')
@section('content')
<h1>Request password reset</h1>
<section class="ef-content">
    <form role="form" method="POST" action="{{ url('/forgot-password') }}">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'label' => 'username',
            'name' => 'username',
            'error' => ($errors->has('username')) ? $errors->first('username') : ''
        ]) !!}
        {!! UIKit::textInput([
            'label' => 'email address',
            'name' => 'email',
            'error' => ($errors->has('email')) ? $errors->first('email') : ''
        ]) !!}
        {!! UIKit::button([
            'label' => 'email link'
        ]) !!}
    </form>
</section>
@endsection
