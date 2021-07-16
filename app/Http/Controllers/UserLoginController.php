<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Route;

class UserLoginController extends Controller
{
    public function __construct()
    {
//        $this->middleware('guest:user', ['except' => ['logout']]);
        $this->middleware('auth');
    }

    public function index()
    {
        die('aaaa');
        return view('auth.admin.admin_login');
    }
}
