<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class Customer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Sentinel::inRole('customer')) {
            return redirect()->back();
        }
        return $next($request);
    }
}
