<aside id="site-nav" class="ef-width-one-fourth">
    <nav>
        <button class="collapsable"><i class="fa fa-user" aria-hidden="true"></i><span>User menu</span></button>
        <ul class="collapsed">
        @if (Auth::user())
            <li>
                <a href="{{ Auth::user()->registration->editAccountPath }}">Password & email</a>
            </li>
            @if (Auth::user()->canInviteUsers)
                <li>
                    <a href="{{ env('APP_DOMAIN') }}/invitations">Invite users</a>
                </li>
            @endif            
        @endif
        </ul>
    </nav>
</aside>
