<!DOCTYPE html>
<html lang="en">
<head>
{{-- 

Highly recommend replacing with @include('layouts.app') or whatever your equivalent is.
What we're really looking for is a template that has a @yield('content') and a menu
to display in the user's workspace. /{user-type}/{username}/*

See other comment below.

--}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (isset($page_title))
        <title>{{ $page_title }} | {{ env('COMPANY_NICE_NAME', 'Registered') }}</title>
    @else
        <title>{{ env('COMPANY_NICE_NAME', 'Registered') }}</title>
    @endif
    <script>
        window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token(),]); ?>
    </script>
</head>
<body>
<aside>
    <nav>
        <ul>
            @if (Auth::user())
            <li class="border-left">
                <a href="{{ url(Auth::user()->registration->profilePath) }}">my profile</a>
            </li>
            <li>
                <b><a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">sign out
                </a></b>
                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
            </li>
            @else
            <li><a href="{{ url('/register') }}">register</a></li>
            <li><a href="{{ url('/login') }}">sign in</a></li>
            @endif
        </ul>
    </nav>
</aside>
    <article class="ef-grid">
{{--  

There are a couple things to call out here.

1. All the page views in Registered operate in a "content" section.
2. Registered coes with a side bar navigation for navigating the user's workspace.
3. Registered, as an alias, provides a method for determining where the authenticated
   user is in their workspace or not.

--}}
        @if(Registered::isProfileArea())
            @include('registered::account-profile.user-nav')
        @endif
        @yield('content')
{{-- 

End the parts you care about.

--}}
    </article>
    <footer class="ef-grid">
        @yield('footer')
    </footer>
</body>
</html>
