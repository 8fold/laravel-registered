@extends('registered::layouts.app', [
    'page_title' => 'Manage Acccount | '. Auth::user()->displayName .' | 8fold Professionals'
])

@section('main')
<article class="ef-my-profile sub-area ef-content">
    {!! user_nav() !!}
    <div>
<h1>Manage account</h1>
<section>
    <h2>Password</h2>
    {!! UIKit::form([
        'attributes' => [
            'method' => 'post',
            'action' => url(Auth::user()->registration->editAccountPath) .'/update-password'
        ],
        'content' => [
            [
                'element' => 'Eightfold\UIKit\UIKit::textinput',
                'type'=> 'password',
                'label' => 'Current password',
                'name' => 'current_password',
                'error' => ($errors->has('current_password'))
                    ? $errors->first('current_password')
                    : ''
            ],
            [
                'element' => 'Eightfold\UIKit\UIKit::textinput',
                'type'=> 'password',
                'label' => 'New password',
                'name' => 'new_password',
                'error' => ($errors->has('new_password'))
                    ? $errors->first('new_password')
                    : ''
            ],
            [
                'element' => 'Eightfold\UIKit\UIKit::textinput',
                'type'=> 'password',
                'label' => 'Confirm new password',
                'name' => 'confirm_password',
                'error' => ($errors->has('confirm_password'))
                    ? $errors->first('confirm_password')
                    : ''
            ],
            [
                'element' => 'Eightfold\UIKit\UIKit::formbutton',
                'label' => 'Update password'
            ],
            [
                'element' => 'Eightfold\LaravelUIKit\UIKit::csrffield'
            ]
        ]
    ]) !!}
</section>
<section>
    <h2>Email addresses</h2>
    {!! UIKit::form([
        'attributes' => [
            'method' => 'post',
            'action' => url(Auth::user()->registration->editAccountPath) .'/emails/add'
        ],
        'content' => [
            [
                'element' => 'Eightfold\UIKit\UIKit::textinput',
                'type'=> 'email',
                'label' => trans('registered::registration.email_address_add'),
                'name' => 'email',
                'placeholder' => 'john@example.com',
                'value' => (old('email'))
                    ? old('email')
                    : '',
                'error' => ($errors->has('email'))
                    ? $errors->first('email')
                    : ''
            ],
            [
                'element' => 'Eightfold\UIKit\UIKit::formbutton',
                'label' => 'Save email address'
            ],
            [
                'element' => 'Eightfold\LaravelUIKit\UIKit::csrffield'
            ]
        ]
    ]) !!}
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
            {!! UIKit::tr([
                'content' => [
                    [
                        'element' => 'td',
                        'content' => [
                            $address->email,
                            ($address->is_default) ? '(default)' : ''
                        ]
                    ],
                    [
                        'element' => 'td',
                        'content' => [
                            [
                                'element' => UIKit::class .'::resourceactions',
                                'no-actions' => Auth::user()->registration->defaultEmailString,
                                'action-buttons' => [
                                    [
                                        'tag' => $address->email,
                                        'action' => url(Auth::user()->registration->editAccountPath) .'/emails/primary',
                                        'inputs' => [
                                            '_token' => csrf_token(),
                                            'address' => $address->email
                                        ],
                                        'button-label' => 'make default'
                                    ],
                                    [
                                        'tag' => $address->email,
                                        'action' => url(Auth::user()->registration->editAccountPath) .'/emails/delete',
                                        'inputs' => [
                                            '_token' => csrf_token(),
                                            'address' => $address->email
                                        ],
                                        'button-label' => 'delete',
                                        'button-type' => 'destructive'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]) !!}
            @endforeach
        </tbody>
    </table>
</section>
    </div>
</article>
@endsection
