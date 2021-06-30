<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WooApi;

class WooController extends Controller
{
    public function checkTemplate(Request $request) {
        $WooApi = new WooApi();
        return $WooApi->checkTemplate($request);
    }
}
