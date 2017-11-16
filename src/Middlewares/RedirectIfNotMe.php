<?php

namespace Eightfold\Registered\Middlewares;

use Closure;
use Auth;

use Eightfold\UIKit\UIKit;

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
            $alert = UIKit::alert([
                'Not authorized',
                'You are not authorized for this area or action.'
            ]);
            return redirect($target)
                ->with('message', $alert);
        }

        return $next($request);
    }
}
