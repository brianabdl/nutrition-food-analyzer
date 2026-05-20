<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function activeUsers()
    {
        try {
            // Expire stale sessions
            DB::table('user_sessions')
                ->where('is_active', true)
                ->where('last_activity', '<', now()->subMinutes(30))
                ->update(['is_active' => false]);

            $users = UserSession::active()
                ->select('nim', 'name', 'login_time', 'last_activity')
                ->get()
                ->map(function ($s) {
                    $s->idle_seconds = now()->diffInSeconds($s->last_activity);
                    return $s;
                });

            return response()->json([
                'success' => true,
                'data'    => [
                    'count'     => $users->count(),
                    'users'     => $users,
                    'timestamp' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Failed to fetch active users'], 500);
        }
    }
}
