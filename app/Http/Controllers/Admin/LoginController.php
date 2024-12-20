<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function store()
    {
        return response()->json([
            'token' => 'fake-jwt-token',
        ]);
    }
}
