<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    public function index()
    {
        $session = UserSession::where('session_id', Session::getId())->first();

        return view('profile', ['user' => Auth::user(), 'userSession' => $session]);
    }
}
