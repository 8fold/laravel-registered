<?php

namespace Eightfold\RegisteredLaravel\Middlewares;

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
                    'title' => 'Not authorized',
                    'text' => 'You are not authorized for this area or action.'
                ]);
        }

        return $next($request);
    }
}
