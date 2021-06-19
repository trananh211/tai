<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;

class Admin extends User
{
    //Nhận data scrap setup ban đầu
    public function verifydataScrapSetup($request)
    {
        $data = $request->all();
        unset($data['_token']);
        if (is_array($data))
        {
            Session::put('get_scrap',$data);
        }
        $catalog_source = json_decode($data['catalog_source'], true);
        $product_source = json_decode($data['product_source'], true);
        $verify_data = [
            'catalog_source' => $catalog_source,
            'product_source' => $product_source
        ];
        $verify_data = json_encode($verify_data, true);

        $alert = 'error';
        $message = '';
        $return = 0;
        if ($data['template_id'] != 0 && $data['type_page_load'] != 0 && $data['catalog_source'] != null &&
            $data['product_source'] != null) {
            if (is_array($catalog_source) && is_array($product_source))
            {
                $template_id = $data['template_id'];
                $url = $catalog_source['url'];
                $data['url'] = $url;
                $check_exist = \DB::table('web_scraps')->select('id')
                    ->where(['template_id' => $template_id, 'url' => $url])->first();
                // nếu đã tồn tại link đã scrap trước đó.
                if ($check_exist != NULL)
                {
                    $alert = 'warning';
                    $message = 'Không thể thực hiện vì bạn đã yêu cầu Crawl websilte : '.$url.' trước đó rồi.';
                } else {
                    $catalog_source['typePageLoad'] = $data['type_page_load'];
                    $data['catalog_source'] = json_encode($catalog_source);
                    $data['product_source'] = json_encode($product_source);
                    $return = 1;
                    $alert = 'success';
                    $message = 'Toàn bộ data được pass đúng định dạng.';
                }
            } else {
                if (!is_array($catalog_source)) {
                    $message = 'Sai định dạng Catalog Source. Bạn cần điền lại cho đúng định dạng.';
                } else if (!is_array($product_source)) {
                    $message = 'Sai định dạng Product Source. Bạn cần điền lại cho đúng định dạng.';
                }
            }
        } else {
            if($data['template_id'] == 0)
            {
                $message = 'Bạn phải chọn Template';
            } else if ($data['type_page_load'] == 0) {
                $message = 'Bạn phải chọn kiểu tải trang';
            } else if ($data['catalog_source'] == null) {
                $message = 'Bạn phải điền catalog source';
            } else if ($data['product_source'] == null) {
                $message = 'Bạn phải điền product source';
            }
        }

        return [
            'return' => $return,
            'alert' => $alert,
            'message' => $message,
            'data' => $data,
            'verify_data' => $verify_data
        ];
    }

    // lưu thông tin website scrap vào database sau khi verify thành công
    public function saveDataScrapSetup($request)
    {
        $data = $request->all();
        $result = false;
        $alert = 'error';
        $message = 'Không lưu dữ liệu được vào database. ';

        unset($data['_token']);
        unset($data['type_page_load']);

        $catalog_source = json_decode($data['catalog_source'], true);
        $product_source = json_decode($data['product_source'], true);

        $data['url'] = $catalog_source['url'];
        $data['catalog_source'] = json_encode($catalog_source);
        $data['product_source'] = json_encode($product_source);
        $data['created_at'] = dbTime();
        $data['updated_at'] = dbTime();

        \DB::beginTransaction();
        try {
            $r = \DB::table('web_scraps')->insert($data);
            if ($r) {
                $result = true;
                \Session::forget('get_scrap');
            }
            $alert = 'success';
            $message = 'Đã lưu thành công dữ liệu.';
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $message .= 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        return [
            'result' => $result,
            'alert' => $alert,
            'message' => $message
        ];
    }
}
