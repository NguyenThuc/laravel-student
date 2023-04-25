<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{


    public function handle($request, Closure $next, ...$guards)
    {
        $user = Auth::guard('seller')->user();
        if ($user) {
            return $next($request);
        }

        return redirect(route('login'));

    }//end handle()


}//end class
