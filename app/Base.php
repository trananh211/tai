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
    public function getSkuAutoId($string = null)
    {
        $sku_id = false;
        if ($string != '')
        {
            $sku_string = strtoupper($string);
            $check_exists = \DB::table("skus")->select('id')->where('sku',$sku_string)->first();
            if($check_exists == NULL) {
                $sku_id = \DB::table('skus')->insertGetId(['sku' => $sku_string]);
            } else {
                $sku_id = $check_exists->id;
            }
        }
        return $sku_id;
    }
}
