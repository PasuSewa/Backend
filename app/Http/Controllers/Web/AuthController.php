<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $rules = ['required', 'integer', 'min:100000', 'max:999999'];

        $request->validate([
            '2fa_code_email' => $rules,
            '2fa_code' => $rules
        ]);
    }
}
