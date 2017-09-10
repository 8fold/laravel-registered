@extends('registered::layouts.app')

@section('content')
<section class="ef-content">
    <form role="form" method="POST" action="{{ Request::url() }}">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'type' => 'password',
            'label' => trans('registered::registration.password'),
            'name' => 'password',
            'error' => ($errors->has('password')) ? $errors->first('password') : ''
        ]) !!}
        {!! UIKit::textInput([
            'type'=> 'password',
            'label' => trans('registered::registration.password_confirm'),
            'name' => 'password_confirm',
            'error' => ($errors->has('password_confirm')) ? $errors->first('password_confirm') : ''
        ]) !!}
        {!! UIKit::button([
            'label' => trans('registered::registration.set_password')
        ]) !!}
    </form>
</section>
@endsection
