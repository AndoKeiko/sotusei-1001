<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $redirectTo = '/goals';

    protected function authenticated(Request $request, $user)
    {
        // ログイン後、goalsページにリダイレクト
        return redirect()->route('goals.index');
    }
}
