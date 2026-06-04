<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function check(Request $request)
    {
        return response()->json([
            'is_authenticated' => (bool) $request->user(),
            'is_verified' => (bool) $request->user()?->hasVerifiedEmail(),
        ]);
    }
}
