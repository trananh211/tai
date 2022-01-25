<?php

namespace App;

use Automattic\WooCommerce\Client;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    /*WooCommerce API*/
    public function getConnectStore($url, $consumer_key, $consumer_secret)
    {
        $woocommerce = new Client(
            $url,
            $consumer_key,
            $consumer_secret,
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'timeout' => 400,
                'query_string_auth' => true,
                'verify_ssl' => false,
                'query_string_auth' => true // Force Basic Authentication as query string true and using under HTTPS
            ]
        );
        return $woocommerce;
    }

    /*
     * Hàm kiểm tra tồn tại sku chưa. Nếu chưa trả về false, tồn tại trả về true
    */
    public function checkExistSku($string = null) {
        $sku_string = strtoupper($string);
        $check_exists = \DB::table("skus")->select('id')->where('sku',$sku_string)->first();
        return  ($check_exists == null) ? false : true;
    }

    // Hàm trả về sku_id
    public function getSkuAutoId($string = null, $is_auto = 0)
    {
        $sku_id = false;
        if ($string != '')
        {
            $sku_string = ($is_auto == 1)? strtoupper(env('SKU_AUTO_STRING').$string) : strtoupper($string);
            $check_exists = \DB::table("skus")->select('id')->where('sku',$sku_string)->first();
            if($check_exists == NULL) {
                $sku_id = \DB::table('skus')->insertGetId(['sku' => $sku_string, 'is_auto' => $is_auto]);
            } else {
                $sku_id = $check_exists->id;
            }
        }
        return $sku_id;
    }

    // Hàm trả về product name với đúng điều kiện sku
    public function getProductName($name, $sku, $exclude_text, $first_title)
    {
        $name = str_replace($exclude_text, "",  $name);
        $product_name = ucwords(trim($first_title.' '.$name));
        $product_name .= ' '.$sku;
        return $product_name;
    }

    // Hàm trả về string và loại bỏ ký tự đặc biệt
    public function getStringSpecialRemove($string) {
        $string = preg_replace('/[^A-Za-z0-9\_]/', ' ', strip_tags($string)); // Removes special chars.
        return $string;
    }

    // Hàm trả về string và loại bỏ ký tự đặc biệt
    public function getStringNormal($string) {
        $string = str_replace('', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags($string)); // Removes special chars.
        return $string;
    }

    // hàm lọc phần tử rỗng và khoảng trắng array
    public function getArrayTrue($array) {
        // loại bỏ khoảng trắng array
        $array = array_map('trim', $array);
        // loại bỏ phần tử rỗng array
        $array = array_filter($array, 'strlen');
        return $array;
    }
}
