<p><strong>Thank you!</strong></p>
<p><a href="{{ url(env('APP_DOMAIN') . $confirmUrl) }}">Click here to confirm your email address</a> and sign in.</p>
<p>ps. If the link above is not active, copy and paste the following into the address bar of your browser:<br>
{{ url(env('APP_DOMAIN') . $confirmUrl) }}</p>
