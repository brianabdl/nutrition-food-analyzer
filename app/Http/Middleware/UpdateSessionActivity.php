<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class UpdateSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            DB::table('user_sessions')
              ->where('session_id', Session::getId())
              ->where('is_active', true)
              ->update(['last_activity' => now()]);
        }

        return $next($request);
    }
}
