<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $request->user()->authorizeRoles(['user', 'admin']);
        return view('user.dashbroad');
    }


    public function viewScraper()
    {
        return view('user.dashbroad');
    }
}
