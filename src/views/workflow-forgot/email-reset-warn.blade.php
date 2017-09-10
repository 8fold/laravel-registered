<p>We received a request to reset your password from a different email address than the one marked as default for your account. If this request was made by you, please follow the link below.</p>

<p><a href="{{ url('/reset-password/?token='. $user->registration->passwordReset->token) }}">Click here to reset your passwrod</a>.</p>

<p>Temporary password:<br>
{{ $user->registration->passwordReset->code }}</p>

<p>If, on the other hand, the request was <em>not</em> made by you, we highly recommend changing your password.</p>

<p>If the link above is not active, copy and paste the following into the address bar of your browser:<br>
{{ url('/reset-password/?token='. $user->registration->passwordReset->token) }}</p>
