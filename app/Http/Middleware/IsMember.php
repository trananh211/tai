<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
//use Role;

class IsMember
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
        if ($request->user()->authorizeRoles(['user', 'admin']))
        {
            return $next($request);
        }
        return redirect('home')->with('error','You have not admin access');
    }
}
