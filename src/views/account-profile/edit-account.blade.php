@extends('registered::layouts.app')

@section('content')
<h1>Edit account</h1>
<section>
    <h2>Password</h2>
    <form method="post" action="{{ url(Auth::user()->registration->editAccountPath) }}/update-password">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'type'=> 'password',
            'label' => 'Current password',
            'name' => 'current_password',
            'error' => ($errors->has('current_password')) ? $errors->first('current_password') : ''
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
        {!! UIKit::button(['label' => 'update password']) !!}
    </form>
</section>
<section>
    <h2>Email addresses</h2>
    <form method="post" action="{{ url(Auth::user()->registration->editAccountPath) }}/emails/add">
        {{ csrf_field() }}
        {!! UIKit::textInput([
            'type'=> 'email',
            'label' => trans('registered::registration.email_address_add'),
            'name' => 'email',
            'value' => (old('email')) ? old('email') : null,
            'placeholder' => 'john@example.com',
            'error' => ($errors->has('email')) ? $errors->first('email') : ''
        ]) !!}
        {!! UIKit::button(['label' => 'add email address']) !!}
    </form>
    <table>
        <thead>
            <tr>
                <th colspan="2">Registered addresses</th>
            </tr>
            <tr>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach(Auth::user()->registration->emails as $address)
            <tr>
                <td>
                    {{ $address->email }}
                    @if($address->is_default)
                        (primary)
                    @endif
                </td>
                <td>
                    @if(!$address->is_default)
                        <form method="post" action="{{ url(Auth::user()->registration->editAccountPath) }}/emails/primary">
                            {{ csrf_field() }}
                            {!! UIKit::hiddenInput([
                                'name' => 'address',
                                'value' => $address->email
                            ]) !!}
                            {!! UIKit::button(['label' => 'set as primary']) !!}
                        </form>
                        <form method="post" action="{{ url(Auth::user()->registration->editAccountPath) }}/emails/delete">
                            {{ csrf_field() }}
                            {!! UIKit::hiddenInput([
                                'name' => 'address',
                                'value' => $address->email
                            ]) !!}
                            {!! UIKit::button(['label' => 'delete']) !!}
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
