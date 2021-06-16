<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request)
                    ->header("Access-Control-Allow-Origin", "*")
                    ->header("Access-Controll-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTION")
                    ->header("Access-Controll-Allow-Headers", "Content-Type, Authorization, Accept, Accept-Language");
    }
}
