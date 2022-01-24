<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\WooApi;
use File;
use DB;

class ApiController extends BaseController
{
    /*WOOCOMMERCE API*/
    public function testNewOrder($filename)
    {
        $files = \File::get(storage_path('file/' . $filename . '.json'));
        $data = json_decode($files, true);
        $api = new WooApi();
        if($filename%2 == 0){
            $webhook_source = 'https://clumsysaurus.com';
        } else {
            $webhook_source = 'https://sportgear247.com';
        }
        $store = DB::table('store_infos')
            ->where('url', 'LIKE', '%'.$webhook_source.'%')
            ->pluck('id')
            ->toArray();
        $api->createOrder($data, $store[0]);
    }

    public function newOrder(Request $request)
    {
        /*Get Header Request*/
        $header = getallheaders();
        logfile('[New Order] Phát hiện thấy order mới');
        if (is_array($header))
        {
            $woo_id = false;
            $woo_infos = \DB::table('store_infos')->pluck('id','url')->toArray();
            foreach ($header as $key => $value)
            {
                // kiểm tra xem có phải url không
                if (strpos($value, 'http') !== false) {
                    // $url = substr($value, 0, -1);
                    $url = $value;
                    if (array_key_exists($url, $woo_infos))
                    {
                        logfile('[New Order] from '.$url);
                        $woo_id = $woo_infos[$url];
                        break;
                    }
                }
            }
            /*Get data Request*/
            $data = @file_get_contents('php://input');
            $data = json_decode($data, true);

            /*Send data to processing*/
            if ($woo_id !== false)
            {
                if (sizeof($data) > 0)
                {
                    $api = new WooApi();
                    $api->createOrder($data, $woo_id);
                } else {
                    logfile('[Error data] Không nhận được data của new order truyền vào');
                }
            } else {
                logfile('[Error Id] Store Id không được tìm thấy');
            }
        } else {
            logfile('[Error Header] [New Order] Không tồn tại header');
        }
    }
}
