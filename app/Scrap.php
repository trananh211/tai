<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scrap extends Model
{
    //verify data scrap . Call API to server Node JS
    public function verifyDataScrapBeforeSave($body)
    {
        $header = getHeader();
        $url = env('URL_SERVER_VERIFY_DATA_SCRAP');
        $result = json_decode(postUrl($url, $header, $body), true);
        if (is_array($result) && array_key_exists('result', $result) && $result['result'] == 1)
        {
            $return = [
                'return' => 1,
                'alert' => 'success',
                'message' => $result['message'],
                'data' => $result['data']
            ];
        } else {
            $return = [
                'return' => 0,
                'alert' => 'error',
                'message' => $result['message']
            ];
        }
        return $return;
    }

    public function getListProduct($request)
    {
        $return = false;
        $message = '';

        $header_key = env('HEADER_VP6_KEY');
        $header_value = env('HEADER_VP6_VALUE');

        $bodyContent = json_decode($request->getContent(), true);

        if (array_key_exists($header_key, $bodyContent) && $bodyContent[$header_key] == $header_value)
        {
            $lstProducts = (array_key_exists('data', $bodyContent)) ? $bodyContent['data'] : [];
            if (sizeof($lstProducts) > 0)
            {
                $data = array();
                $web_scrap_id = $bodyContent['web_scrap_id'];
                // lấy ra toàn bộ url của website này đã từng crawl trước đó để so sánh với dữ liệu được trả về
                $lists = \DB::table('list_products')->where('web_scrap_id',$web_scrap_id)->pluck('product_link')->toArray();
                foreach ($lstProducts as $item)
                {
                    $link = trim($item['link']);
                    if (in_array($link, $lists)) { continue; }
                    $tmp = [
                        'web_scrap_id' => $web_scrap_id,
                        'product_name' => trim($item['title']),
                        'product_link' => $link,
                        'created_at' => dbTime(),
                        'updated_at' => dbTime()
                    ];
                    $data[] = $tmp;
                }
                if (sizeof($data) > 0)
                {
                    logfile_system('Phát hiện thêm '.sizeof($data).' link sản phẩm mới. Lưu vào hệ thống');
                    $result = \DB::table('list_products')->insert($data);
                    if ($result)
                    {
                        $return = true;
                        $message = 'Toàn bộ product đã được cập nhật thành công';
                    } else {
                        $message = 'Xảy ra lỗi không thể lưu dữ liệu vào database';
                    }
                } else {
                    $message = 'Không phát hiện thêm link sản phẩm nào mới.';
                    logfile_system($message);
                }
                // cập nhật trạng thái web_scraps thành success
                \DB::table('web_scraps')->where('id',$web_scrap_id)->update([
                    'status' => env('STATUS_SCRAP_PRODUCT_SUCCESS'),
                    'updated_at' => dbTime()
                ]);
            } else {
                $message = 'Không tồn tại data để crawl. Website trắng';
            }
        } else {
            $message = '[Warning] Phát hiện ra có người ngoài đang hack vào hệ thống. Bật chế độ bảo mật cao';
        }
        return array(
            'result' => $return,
            'message' => $message
        );
    }

    // hàm gửi thông tin scrap web từ client về server
    public function getWebScrap()
    {
        logfile_system(" ======================= Website Scrap ======================== ");
        $return = true;
        $message = '';
        $webs = \DB::table('web_scraps')->select('id','url','status','catalog_source')
            ->where('status', env('STATUS_SCRAP_PRODUCT_NEW'))
            ->orderBy('created_at','ASC')
            ->first();
        // nếu vẫn còn website để scrap.
        if ( $webs != NULL )
        {
            logfile_system('Đang gửi thông tin web scrap : '.$webs->url.' sang server');
            $header = getHeader();
            $header['web_scrap_id'] = $webs->id;
            $url = env('URL_SERVER_POST_DATA_SCRAP');
            $body = $webs->catalog_source;
            $r = json_decode(postUrl($url, $header, $body), true);
            // nếu gửi thành công dữ liệu data thì tiến hành cập nhật trạng thái
            if (is_array($r) && array_key_exists('result', $r) && $r['result'] == 1) {
                $web_scrap_id = (array_key_exists('web_scrap_id', $r)) ? $r['web_scrap_id'] : null;
                if ($web_scrap_id != null) {
                    logfile_system('Gửi thông tin web : '. $webs->url.' sang server thành công. Cập nhật trạng thái running');
                    $status = env('STATUS_SCRAP_PRODUCT_RUNNING');
                }
            } else {
                logfile_system('[Error] Gửi thông tin web : '. $webs->url.' sang server thất bại. Cập nhật trạng thái error');
                $message = $r['message'];
                $status = env('STATUS_SCRAP_PRODUCT_ERROR');

            }
            $re = \DB::table('web_scraps')->where('id', $webs->id)
                ->update([
                    'status' => $status,
                    'updated_at' => dbTime()
                ]);
            if ($re) {
                logfile_system('Cập nhật trạng thái thành công id : '.$webs->id.' website: '.$webs->url);
                $return = false;
            } else {
                logfile_system('[Error] Cập nhật trạng thái thất bại id : '.$webs->id.' website: '.$webs->url);
            }
        } else {
            $message = 'Đã hết website để crawl. Chuyển sang công việc khác';
        }
        logfile_system($message);
        return $return;
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
