<?php

namespace Eightfold\RegistrationManagementLaravel\Middlewares;

use Closure;
use Auth;

class RedirectIfNotMe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $path = $request->path();
        $parts = explode('/', $path);
        $requestUsername = $parts[1];
        $myUsername = Auth::user()->username;

        if ($requestUsername !== $myUsername) {
            $target = Auth::user()->registration->profilePath;
            return redirect($target)
                ->with('message', [
                    'type' => 'warning',
                    'title' => 'Not allowed',
                    'text' => 'You are not allowed to view this page or perform this action.'
                ]);
        }

        return $next($request);
    }
}
