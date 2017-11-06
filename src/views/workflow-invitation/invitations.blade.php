@extends('registered::layouts.app', ['page_title' => '8fold Professionals'])

@section('main')
<article class="ef-grid">
    <section class="ef-content">
        <h1>Invitations</h1>
        <p>{{ $inviteCountString }}</p>
        @if ($canInvite)
        <form role="form" method="POST" action="{{ Request::url() }}">
            {{ csrf_field() }}
            {!! UIKit::textinput([
                'type' => 'email',
                'label' => trans('registered::members.email_address'),
                'name' => 'email',
                'placeholder' => 'john@8fold.pro.com',
                'error' => ($errors->has('email')) ? $errors->first('email') : ''
            ]) !!}
            @if (Auth::user()->canChangeUserTypes)
            {!! UIKit::formselect([
                'label' => 'Select type of user',
                'name' => 'user_type',
                'options' => [$userTypeOptions],
                'selected' => ['users']
            ]) !!}
            @else
            {!! UIKit::hiddeninput([
                'name' => 'user_type',
                'value' => 'users'
            ]) !!}
            @endif
            {!! UIKit::formbutton([
                'label' => trans('registered::members.invite_member')
            ]) !!}
        </form>
        @else
            <p>You have no more invitations available at this time.</p>
        @endif
    </section>
    @if ($requests->count() > 0)
    <section class="ef-content">
        <h2>Invitation requests</h2>
        @foreach($requests as $request)
        <form role="form" method="POST" action="{{ Request::url() }}">
            {{ csrf_field() }}
            Invite <b>{{ $request->email }}</b> to join.
            {!! UIKit::hiddenInput([
                'name' => 'email',
                'value' => $request->email
            ]) !!}
            @if (Auth::user()->canChangeUserTypes)
            {!! UIKit::select([
                'label' => 'Select type of user',
                'name' => 'user_type',
                'options' => [$userTypeOptions],
                'selected' => 'users'
            ]) !!}
            @else
            {!! UIKit::hiddeninput([
                'name' => 'user_type',
                'value' => 'users'
            ]) !!}
            @endif
            {!! UIKit::formbutton([
                'label' => trans('registered::members.invite_member')
            ]) !!}
        </form>
        @endforeach
    </section>
    @endif
    @if ($unclaimedInvitations->count() > 0)
    <section class="ef-content">
        <h2>Pending invitations</h2>
        @foreach($unclaimedInvitations as $invitation)
            <div>
                <p><b>Sent to:</b> {{ $invitation->email }}</p>
                <p><b>Sent on:</b> {{ $invitation->created_at }}</p>
                <p>
                <form role="form" method="POST" action="{{ Request::url() }}/{{ $invitation->public_key }}">
                    {{ csrf_field() }}
                    {!! UIKit::formbutton([
                        'label' => 'Send again'
                    ]) !!}
                </form>
            </div>
        @endforeach
    </section>
    @endif
    @if ($claimedInvitations->count() > 0)
    <section class="ef-content">
        <h2>Past invitations</h2>
        @foreach($claimedInvitations as $invitation)
            <div>
                <p><b>Sent to:</b> {{ $invitation->email }}</p>
                <p><b>Sent on:</b> {{ $invitation->created_at }}</p>
                <p><b>Claimed on:</b> {{ $invitation->claimed_on }}</p>
            </div>
        @endforeach

    </section>
    @endif
</article>
@endsection
