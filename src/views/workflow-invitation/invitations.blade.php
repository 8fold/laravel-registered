@extends('registered::layouts.app')

@section('content')
<article class="ef-grid">
    <section class="ef-content">
        <h1>Invitations</h1>
        <p>{{ $inviteCountString }}</p>
        @if ($canInvite)
        <form role="form" method="POST" action="{{ Request::url() }}">
            {{ csrf_field() }}
            {!! UIKit::textInput([
                'type' => 'email',
                'label' => trans('members.email_address'),
                'name' => 'email',
                'placeholder' => 'john@8fold.pro.com',
                'error' => ($errors->has('email')) ? $errors->first('email') : ''
            ]) !!}
            @if (Auth::user()->canChangeUserTypes)
            {!! UIKit::select([
                'label' => 'Select type of user',
                'name' => 'user_type',
                'options' => $userTypeOptions,
                'selected' => 'users'
            ]) !!}
            @endif
            {!! UIKit::button([
                'label' => trans('members.invite_member')
            ]) !!}
        </form>
        @else
            <p>You have no more invitations available at this time.</p>
        @endif
    </section>
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
                    {!! UIKit::button([
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
