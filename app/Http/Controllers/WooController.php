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
        // nếu pre create có data. tiếp tục check tag
        if ($pre_create['check_tag']) {
            $WooApi->processCreateTag($pre_create);
        } else {
            // nếu check tag xong. Chuyển sang tạo mới sản phẩm
            if ($pre_create['result']) {
                $process = $WooApi->processCreateProduct($pre_create);
                return $process['result'];
            } else {
                return $pre_create['result'];
            }
        }

    }
}
