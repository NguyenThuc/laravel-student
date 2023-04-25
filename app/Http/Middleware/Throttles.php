<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests as Middleware;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
class Throttles extends Middleware
{

    use AuthenticatesUsers;


    public function handle($request, Closure $next, $maxAttempts=5, $decayMinutes=60, $prefix='')
    {
        $controller = new Controller();
        $key = $prefix.$this->requestSignature($request);
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $this->incrementLoginAttempts($request);
            return $controller->responseError(trans('auth.throttle_failed_login'), Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->incrementLoginAttempts($request);
        $this->limiter->hit($key, ($decayMinutes * 60));
        $response = $next($request);
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );

    }//end handle()


    private function requestSignature($request)
    {
        return sha1($request->email);

    }//end requestSignature()


}//end class
