@extends('layouts.app', [
    'markdown' => [
        'biography-editor'
    ]
])

@section('content')
<h1>Edit profile</h1>
<section>
    <h2>Profile names</h2>
    <form method="post" action="./edit/update-names">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'label' => trans('registered::members.username'),
            'name' => 'username',
            'value' => (old('username')) ? old('username') : Auth::user()->username,
            'error' => ($errors->has('username')) ? $errors->first('username') : ''
        ]) !!}
        {!! UIKit::textInput([
            'label' => trans('registered::members.first_name'),
            'name' => 'first_name',
            'value' => Auth::user()->registration->first_name,
            'error' => ($errors->has('first_name')) ? $errors->first('first_name') : ''
        ]) !!}
        {!! UIKit::textInput([
            'label' => trans('registered::members.last_name'),
            'name' => 'last_name',
            'value' => Auth::user()->registration->last_name,
            'error' => ($errors->has('last_name')) ? $errors->first('last_name') : ''
        ]) !!}
        {!! UIKit::button(['label' => 'update profile information']) !!}
    </form>
</section>
@endsection
