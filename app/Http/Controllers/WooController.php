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

    // hàm tạo sản phẩm woocommerce
    public function createProductWoo() {
        $WooApi = new WooApi();
        $pre_create = $WooApi->preCreateProduct();
        // nếu pre create có data. tiếp tục tạo mới sp
        if ($pre_create['result']) {
               $process = $WooApi->processCreateProduct($pre_create);
        } else {
            return $pre_create['result'];
        }
    }
}
