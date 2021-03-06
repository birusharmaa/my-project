<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Session;

use Closure;

class customAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = $request->path();
        if ($path == "login" && Session::get('user')) {
            return redirect('dashboard');
        } 
        else if ($path == "register" && Session::get('user')) {
            return redirect('dashboard');
        } 
        else if (($path != "login" && !Session::get('user')) && ($path != "register" && !Session::get('user'))) {
            return redirect('/login');
        }
        return $next($request);
    }
}
