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
        $header_key = env('HEADER_VP6_KEY');
        $header_value = env('HEADER_VP6_VALUE');
        if (array_key_exists($header_key, $headerContent) && $headerContent[$header_key][0] == $header_value)
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

    // hàm nhận list images gửi về từ server
    public function getListProductImages($request)
    {
        $return = false;
        $message = '';
        $headerContent = $request->header();
        $header_key = env('HEADER_VP6_KEY');
        $header_value = env('HEADER_VP6_VALUE');
        if (array_key_exists($header_key, $headerContent) && $headerContent[$header_key][0] == $header_value)
        {
            $bodyContent = $request->getContent();
            $lstProducts = json_decode($bodyContent, true);
            print_r($lstProducts);
            die();
//            var_dump($lstProducts);
//            die();
//            print_r($lstProducts);
//            logfile_system($lstProducts);
            if (sizeof($lstProducts) > 0)
            {
                $result = true;
//                $data = array();
//                foreach ($lstProducts as $item)
//                {
//                    $tmp = [
//                        'scrap_id' => 1,
//                        'product_name' => trim($item['title']),
//                        'product_link' => trim($item['link']),
//                        'created_at' => dbTime(),
//                        'updated_at' => dbTime()
//                    ];
//                    $data[] = $tmp;
//                }
//                $result = \DB::table('list_products')->insert($data);
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
        $return = 0;
        $message = '';
        // kiem tra xem co product nao dang scrap khong.
        $checkRunning = \DB::table('list_products')->select('id','updated_at')
            ->where('status',env('STATUS_SCRAP_PRODUCT_RUNNING'))
            ->get()->toArray();
        // neu ton tai thi kiem tra xem co qua thoi gian running hay chua
        if (sizeof($checkRunning) > 0)
        {
            $lists = array();
            $now = DBtime();
            $url = 'none';
            foreach($checkRunning as $item)
            {
                //compare time > 10 minute
                if(strtotime($now) - strtotime($item->updated_at) > (env('TIME_SCRAPER_ONE_LAP')*60))
                {
                    $lists[] = $item->id;
                }
            }
            // nếu có product đã chờ quá thời gian. Chuyến trạng thái over time
            if( sizeof($lists) > 0)
            {
                $update = [
                    'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                    'updated_at' => DBtime()
                ];
                \DB::table('list_products')->whereIn('id',$lists)->update($update);
            }
            $message = '- Không crawl product new vì Đang có '.sizeof($checkRunning).' product crawling. Phát hiện '.sizeof($lists).' product đang overtime';
        } else {
            $lists = \DB::table('list_products')->select('id','scrap_id','product_name','product_link')
                ->where('status',env('STATUS_SCRAP_PRODUCT_NEW'))
                ->orderBy('scrap_id', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()->toArray();
            if (sizeof($lists) > 0)
            {
                $return = 1;
                $url = env('URL_SERVER_SCRAPER');
                $message = '- Đẩy thêm '.sizeof($lists).' product để hệ thống crawl';
            } else {
                $url = 'none';
                $message = '- Đã hết product có thể crawl. Chuyển sang công việc khác';
            }
        }
        $data = [
            'return' => $return,
            'message' => $message,
            'url' => $url,
            'data' => $lists
        ];
        return $data;
    }

    // Hàm lưu thông tin phản hồi scrap của product từ server
    public function saveProductRunning($result)
    {
        \DB::beginTransaction();
        try {
            if ($result['result']['return'] == 1)
            {
                $status = env('STATUS_SCRAP_PRODUCT_RUNNING');
            } else {
                $status = env('STATUS_SCRAP_PRODUCT_ERROR');
            }
            $status = env('STATUS_SCRAP_PRODUCT_NEW');
            $list_id = array();
            // lấy toàn bộ id product data lưu vào 1 mảng
            if (is_array($result['data']) && sizeof($result['data']) > 0)
            {
                foreach ($result['data']['data'] as $item)
                {
                    $list_id[] = $item->id;
                }
                // cập nhật trạng thái vào database
                if (sizeof($list_id) > 0)
                {
                    $result = \DB::table('list_products')
                        ->whereIn('id', $list_id)
                        ->update(['status' => $status, 'updated_at' => dbTime()]);
                }
                $message = 'Cập nhật thành công trạng thái running của product crawl';
            } else {
                $message = 'Đã hết data product để crawl';
            }
            $return = 1;
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $return = 0;
            $message = 'Không thể cập nhật trạng thái running của product crawl. Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        logfile_system($message);
        return $return;
    }
}
