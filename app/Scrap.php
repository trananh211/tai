<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scrap extends Model
{
    //
    public function getListProduct($request)
    {
        $return = false;
        $message = '';
        $headerContent = $request->header();
        if (array_key_exists("vp6", $headerContent) && $headerContent['vp6'][0] == '12345')
        {
            $bodyContent = $request->getContent();
            $lstProducts = json_decode($bodyContent, true);
            if (sizeof($lstProducts) > 0)
            {
                $data = array();
                foreach ($lstProducts as $item)
                {
                    $tmp = [
                        'scrap_id' => 1,
                        'product_name' => trim($item['title']),
                        'product_link' => trim($item['link']),
                        'created_at' => dbTime(),
                        'updated_at' => dbTime()
                    ];
                    $data[] = $tmp;
                }
                $result = \DB::table('list_products')->insert($data);
                if ($result)
                {
                    $return = true;
                    $message = 'Toàn bộ product đã được cập nhật thành công';
                } else {
                    $message = 'Xảy ra lỗi không thể lưu dữ liệu vào database';
                }
            }
        } else {
            $message = '[Warning] Phát hiện ra có người ngoài đang hack vào hệ thống. Bật chế độ bảo mật cao';
        }
        return array(
            'result' => $return,
            'message' => $message
        );
    }

    public function scrapProduct()
    {
        // kiem tra xem co product nao dang scrap khong.
        $checkRunning = \DB::table('list_products')->select('id','updated_at')
            ->where('status',env('STATUS_SCRAP_PRODUCT_RUNNING'))
            ->get()->toArray();
        // neu ton tai thi kiem tra xem co qua thoi gian running hay chua
        if (sizeof($checkRunning) > 0)
        {
            $now = DBtime();
            echo $now;
        } else {
            $list = \DB::table('list_products')->select('id','scrap_id','product_name','product_link')
                ->where('status',env('STATUS_SCRAP_PRODUCT_NEW'))
                ->orderBy('scrap_id', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()->toArray();
            print_r($list);
        }

    }
}
