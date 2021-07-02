<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
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
}
