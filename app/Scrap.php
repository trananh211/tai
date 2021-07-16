<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use \DB;

class Scrap extends Model
{
    //verify data scrap . Call API to server Node JS
    public function verifyDataScrapBeforeSave($body)
    {
        $header = getHeader();
        $url = env('URL_SERVER_VERIFY_DATA_SCRAP');
        $result = json_decode(postUrl($url, $header, $body), true);
        if (is_array($result) && array_key_exists('result', $result) && $result['result'] == 1) {
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

        if (array_key_exists($header_key, $bodyContent) && $bodyContent[$header_key] == $header_value) {
            $lstProducts = (array_key_exists('data', $bodyContent)) ? $bodyContent['data'] : [];
            if (sizeof($lstProducts) > 0) {
                $data = array();
                $web_scrap_id = $bodyContent['web_scrap_id'];
                // lấy ra toàn bộ url của website này đã từng crawl trước đó để so sánh với dữ liệu được trả về
                $lists = \DB::table('list_products')->where('web_scrap_id', $web_scrap_id)->pluck('product_link')->toArray();
                $count = sizeof($lists) + 1;
                foreach ($lstProducts as $item) {
                    $link = trim($item['link']);
                    if (in_array($link, $lists)) {
                        continue;
                    }
                    $tmp = [
                        'web_scrap_id' => $web_scrap_id,
                        'product_name' => trim($item['title']),
                        'product_link' => $link,
                        'img' => trim($item['img']),
                        'count' => $count,
                        'created_at' => dbTime(),
                        'updated_at' => dbTime()
                    ];
                    $count++;
                    $data[] = $tmp;
                }
                if (sizeof($data) > 0) {
                    logfile_system('Phát hiện thêm ' . sizeof($data) . ' link sản phẩm mới. Lưu vào hệ thống');
                    $result = \DB::table('list_products')->insert($data);
                    if ($result) {
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
                \DB::table('web_scraps')->where('id', $web_scrap_id)->update([
                    'status' => env('STATUS_SCRAP_PRODUCT_READY'),
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
        $webs = \DB::table('web_scraps')->select('id', 'url', 'status', 'catalog_source')
            ->where('status', env('STATUS_SCRAP_PRODUCT_NEW'))
            ->orderBy('created_at', 'ASC')
            ->first();
        // nếu vẫn còn website để scrap.
        if ($webs != NULL) {
            logfile_system('Đang gửi thông tin web scrap : ' . $webs->url . ' sang server');
            $header = getHeader();
            $header['web_scrap_id'] = $webs->id;
            $url = env('URL_SERVER_POST_DATA_SCRAP');
            $body = $webs->catalog_source;
            $r = json_decode(postUrl($url, $header, $body), true);
            // nếu gửi thành công dữ liệu data thì tiến hành cập nhật trạng thái
            if (is_array($r) && array_key_exists('result', $r) && $r['result'] == 1) {
                $web_scrap_id = (array_key_exists('web_scrap_id', $r)) ? $r['web_scrap_id'] : null;
                if ($web_scrap_id != null) {
                    logfile_system('Gửi thông tin web : ' . $webs->url . ' sang server thành công. Cập nhật trạng thái running');
                    $status = env('STATUS_SCRAP_PRODUCT_RUNNING');
                }
            } else {
                logfile_system('[Error] Gửi thông tin web : ' . $webs->url . ' sang server thất bại. Cập nhật trạng thái error');
                $message = $r['message'];
                $status = env('STATUS_SCRAP_PRODUCT_ERROR');

            }
            $re = \DB::table('web_scraps')->where('id', $webs->id)
                ->update([
                    'status' => $status,
                    'updated_at' => dbTime()
                ]);
            if ($re) {
                logfile_system('Cập nhật trạng thái thành công id : ' . $webs->id . ' website: ' . $webs->url);
                $return = false;
            } else {
                logfile_system('[Error] Cập nhật trạng thái thất bại id : ' . $webs->id . ' website: ' . $webs->url);
            }
        } else {
            $message = 'Đã hết website để crawl. Chuyển sang công việc khác';
        }
        logfile_system($message);
        return $return;
    }

    // hàm kiểm tra xem có product nào đang running hoặc running quá thời gian cho phép hay không
    // không có trả về true.
    public function checkRunningScrapProduct()
    {
        $result = false;
        $lists = \DB::table('list_products')->select('id', 'updated_at')
            ->where('status', env('STATUS_SCRAP_PRODUCT_RUNNING'))
            ->get()->toArray();
        // nếu tồn tại product đang running, kiểm tra xem có quá thời gian cho phép hay chưa
        if (sizeof($lists) > 0) {
            logfile_system('Hiện tại đang có ' . sizeof($lists) . ' product đang crawling.');
            $now = dbTime();
            $compare_time = env('TIME_UP_CRAWLING') * 60; // thời gian là phút đổi ra giây
            $ar_time_up = [];
            // kiểm tra xem có product nào đang chạy quá thời hạn hay không
            foreach ($lists as $item) {
                // nếu thời gian hiện tại lớn hơn thời gian update quá compare time. => Product bị lỗi
                if ((strtotime($now) - strtotime($item->updated_at)) > $compare_time) {
                    $ar_time_up[] = $item->id;
                }
            }
            // nếu tồn tại product bị lỗi. Cập nhật lại trạng thái running thành lỗi
            if (sizeof($ar_time_up) > 0) {
                logfile_system('Phát hiện ra ' . sizeof($ar_time_up) . ' products đã crawl quá thời gian. Đổi sang trạng thái error');
                \DB::table('list_products')->whereIn('id', $ar_time_up)->update([
                    'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                    'updated_at' => $now
                ]);
            }
        } else { // nếu không có product đang running
            logfile_system('Không có product đang crawling. Bắt đầu crawl danh sách product mới');
            $result = true;
        }
        return $result;
    }

    // hàm lấy ra danh sách product cần được crawling
    public function getListRunningScrapProduct()
    {
        $return = false;
        $lists = \DB::table('list_products as lp')
            ->join('web_scraps as ws', 'ws.id', '=', 'lp.web_scrap_id')
            ->select(
                'lp.id as list_product_id', 'lp.product_name', 'lp.product_link',
                'ws.id as web_scrap_id', 'ws.product_source'
            )
            ->where('lp.status', env('STATUS_SCRAP_PRODUCT_NEW'))
            ->limit(env('LIMIT_PRODUCT_CRAWLING'))
            ->orderBy('lp.id', 'ASC')
            ->get()->toArray();
        if (sizeof($lists) > 0) {
            $list_product_id = [];
            foreach ($lists as $item) {
                $list_product_id[] = $item->list_product_id;
            }
            $lists = json_decode(json_encode($lists, true), true);
            $body = json_encode($lists);
            $header = getHeader();
            $url = env('URL_SERVER_POST_DATA_PRODUCT');
            $result = json_decode(postUrl($url, $header, $body), true);
            if (is_array($result) && array_key_exists('result', $result) && $result['result'] == 1) {
                $message = 'Gửi thành công dữ liệu của ' . sizeof($list_product_id) . ' products về server.';
                \DB::table('list_products')->whereIn('id', $list_product_id)->update([
                    'status' => env('STATUS_SCRAP_PRODUCT_RUNNING'),
                    'updated_at' => dbTime()
                ]);
            } else {
                $message = 'Xảy ra lỗi. Message server trả về : ' . $result['message'];
            }
        } else {
            $message = 'Đã hết product để crawling. Chuyển sang công việc tiếp theo';
            $return = true;
        }
        logfile_system($message);
        return [
            'return' => $return,
            'message' => $message
        ];
    }

    public function getProductData($request)
    {
        $return = false;
        $message = '';
        $header_key = env('HEADER_VP6_KEY');
        $header_value = env('HEADER_VP6_VALUE');
        $bodyContent = json_decode($request->getContent(), true);

        \DB::beginTransaction();
        try {
            if (array_key_exists($header_key, $bodyContent) && $bodyContent[$header_key] == $header_value) {
                $return = true;

                $list_products = [];
                $list_web_scraps = [];
                $product_detail = [];
                $product_detail_tmp = [];
                $list_products_error = [];
                $products = $bodyContent['data']['data'];

                // lọc toàn bộ data trong 1 vòng lặp
                foreach ($products as $item) {
                    $list_product_id = $item['list_product_id'];
                    $web_scrap_id = $item['web_scrap_id'];

                    $list_products[] = $list_product_id;
                    $list_web_scraps[$web_scrap_id] = $web_scrap_id;

                    // kiểm tra xem có sản phẩm trả về bị lỗi hay không
                    if ($item['result']) {
                        $i = 1;
                        foreach ($item['data']['images'] as $img) {
                            $tmp = [
                                'list_product_id' => $list_product_id,
                                'web_scrap_id' => $web_scrap_id,
                                'name' => $item['data']['title'],
                                'position' => $i,
                                'url' => $img,
                                'created_at' => dbTime(),
                                'updated_at' => dbTime()
                            ];
                            $product_detail_tmp[] = $tmp;
                            $i++;
                        }
                    } else {
                        $list_products_error[] = $list_product_id;
                        logfile_system('--[Error] Link sản phẩm bị lỗi : ' . $item['product_link']);
                    }
                }
                // kiểm tra trùng lặp url image
                $url_image_exists = \DB::table('product_images')
                    ->whereIn('web_scrap_id', $list_web_scraps)
                    ->whereIn('list_product_id', $list_products)
                    ->pluck('url')->toArray();
                if (sizeof($url_image_exists) > 0 && sizeof($product_detail_tmp) > 0) {
                    foreach ($product_detail_tmp as $key => $item) {
                        // nếu url đã từng được lưu
                        if (!in_array($item['url'], $url_image_exists)) {
                            $product_detail[] = $item;
                        }
                    }
                } else {
                    $product_detail = $product_detail_tmp;
                }

                // nếu server trả về result true
                if ($bodyContent['data']['result'] == 1) {
                    $status = env('STATUS_SCRAP_PRODUCT_READY');
                    //lưu toàn bộ danh sách image vào database
                    if (sizeof($product_detail) > 0) {
                        \DB::table('product_images')->insert($product_detail);
                    } else {
                        logfile_system('Không thể tạo thêm danh sách image tạo mới vì đã trùng trước đó rồi.');
                    }
                } else {
                    $status = env('STATUS_SCRAP_PRODUCT_ERROR');
                }
                // cập nhật trạng thái tổng của list product
                \DB::table('list_products')->whereIn('id', $list_products)->update([
                    'status' => $status,
                    'updated_at' => dbTime()
                ]);
                // nếu có sản phẩm bị lỗi
                if (sizeof($list_products_error) > 0) {
                    \DB::table('list_products')->whereIn('id', $list_products_error)->update([
                        'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                        'updated_at' => dbTime()
                    ]);
                }

                // kiểm tra xem đã hết list product thuộc website hay chưa
                $list_web_scraps_done = [];
                if (sizeof($list_web_scraps) > 0) {
                    foreach ($list_web_scraps as $web_scrap_id) {
                        $count = \DB::table('list_products')
                            ->select('id')
                            ->where('web_scrap_id', $web_scrap_id)
                            ->where('status', env('STATUS_SCRAP_PRODUCT_NEW'))
                            ->count();
                        if ($count == 0) {
                            $list_web_scraps_done[] = $web_scrap_id;
                        }
                    }
                    if (sizeof($list_web_scraps_done) > 0) {
                        \DB::table('web_scraps')->whereIn('id', $list_web_scraps_done)
                            ->update([
                                'status' => env('STATUS_SCRAP_PRODUCT_PROCESS'),
                                'updated_at' => dbTime()
                            ]);
                    }
                }

            } else {
                $message = '[Warning] Phát hiện ra có người ngoài đang hack vào hệ thống. Bật chế độ bảo mật cao';
            }
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $message = 'Xảy ra lỗi ngoài mong muốn: ' . $e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
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
        if (array_key_exists($header_key, $headerContent) && $headerContent[$header_key][0] == $header_value) {
            $bodyContent = $request->getContent();
            $lstProducts = json_decode($bodyContent, true);
            print_r($lstProducts);
            die();
//            var_dump($lstProducts);
//            die();
//            print_r($lstProducts);
//            logfile_system($lstProducts);
            if (sizeof($lstProducts) > 0) {
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
                if ($result) {
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
        $checkRunning = \DB::table('list_products')->select('id', 'updated_at')
            ->where('status', env('STATUS_SCRAP_PRODUCT_RUNNING'))
            ->get()->toArray();
        // neu ton tai thi kiem tra xem co qua thoi gian running hay chua
        if (sizeof($checkRunning) > 0) {
            $lists = array();
            $now = DBtime();
            $url = 'none';
            foreach ($checkRunning as $item) {
                //compare time > 10 minute
                if (strtotime($now) - strtotime($item->updated_at) > (env('TIME_SCRAPER_ONE_LAP') * 60)) {
                    $lists[] = $item->id;
                }
            }
            // nếu có product đã chờ quá thời gian. Chuyến trạng thái over time
            if (sizeof($lists) > 0) {
                $update = [
                    'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                    'updated_at' => DBtime()
                ];
                \DB::table('list_products')->whereIn('id', $lists)->update($update);
            }
            $message = '- Không crawl product new vì Đang có ' . sizeof($checkRunning) . ' product crawling. Phát hiện ' . sizeof($lists) . ' product đang overtime';
        } else {
            $lists = \DB::table('list_products')->select('id', 'scrap_id', 'product_name', 'product_link')
                ->where('status', env('STATUS_SCRAP_PRODUCT_NEW'))
                ->orderBy('scrap_id', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()->toArray();
            if (sizeof($lists) > 0) {
                $return = 1;
                $url = env('URL_SERVER_SCRAPER');
                $message = '- Đẩy thêm ' . sizeof($lists) . ' product để hệ thống crawl';
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
            if ($result['result']['return'] == 1) {
                $status = env('STATUS_SCRAP_PRODUCT_RUNNING');
            } else {
                $status = env('STATUS_SCRAP_PRODUCT_ERROR');
            }
            $status = env('STATUS_SCRAP_PRODUCT_NEW');
            $list_id = array();
            // lấy toàn bộ id product data lưu vào 1 mảng
            if (is_array($result['data']) && sizeof($result['data']) > 0) {
                foreach ($result['data']['data'] as $item) {
                    $list_id[] = $item->id;
                }
                // cập nhật trạng thái vào database
                if (sizeof($list_id) > 0) {
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
            $message = 'Không thể cập nhật trạng thái running của product crawl. Xảy ra lỗi ngoài mong muốn: ' . $e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        logfile_system($message);
        return $return;
    }
}
