<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function encryptCode(Request $request)
    {
        $code = $request->get('code');

        $secret = Crypt::encrypt($code);

        return response()->json(['key' => $secret]);
    }

    public function decryptCode(Request $request)
    {
        $secret = $request->get('secret');

        $code = Crypt::decrypt($secret);

        return response()->json(['key' => $code]);

    }
}
