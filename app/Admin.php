<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;
use \DB;

class Admin extends User
{
    // connect + setup store
    public function getWooInfo($request)
    {
        $data = $request->all();
        unset($data['_token']);
        $data['type'] = env('STORE_WOO_ID');
        $data['created_at'] = dbTime();
        $data['updated_at'] = dbTime();
        \DB::beginTransaction();
        try {
            $return = true;
            $status = 'success';
            $message = 'Lưu vào hệ thống thành công';
            \DB::table('store_infos')->insert($data);
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $return = false;
            $status = 'error';
            $message = 'Không thể lưu vào thệ thống. Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        return [
            'return' => $return,
            'status' => $status,
            'message' => $message
        ];
    }

    public function newTemplate($id) {
        $back = false;
        $url = '';
        switch ($id) {
            case env('STORE_WOO_ID'):
                $url = 'admin.template_new_woo';
                break;
            case env('STORE_SHOPBASE_ID'):
                $url = 'admin.template_new_shop_base';
                break;
            default:
                $back = true;
                break;
        }
        $data = $this->getDataTemplateNew($id);
        return [
            'back' => $back,
            'url' => $url,
            'data' => $data
        ];
    }

    private function getDataTemplateNew($id) {
        $stores = [];
        switch ($id) {
            case env('STORE_WOO_ID'):
                $stores = \DB::table('store_infos')
                    ->select('id','name','url','consumer_key','consumer_secret')
                    ->where('type', $id)
                    ->get()->toArray();
                break;
            case env('STORE_SHOPBASE_ID'):
                $stores = \DB::table('store_infos')
                    ->select('*')
                    ->where('type', $id)
                    ->get()->toArray();
                break;
        }
        return [
            'stores' => $stores
        ];
    }
    // End connect + setup store

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
            $data['product_source'] != null && $data['type_tag'] != 0) {
            // kiểm tra tag trước
            if ($data['type_tag'] == env('TYPE_TAG_FIXED') && $data['tag_text'] == '') {
                $message = 'Khi chọn cố định 1 tag duy nhất. Bạn phải điền Tag cố định. Mời bạn thử lại';
            } else if ($data['type_tag'] == env('TYPE_TAG_POSITION_X') && ($data['tag_position'] == '' || !is_numeric($data['tag_position']))) {
                $message = 'Khi chọn tag theo vị trí của title. Bạn cần khai báo Vị trí tag và yêu cầu điền bằng số';
            } else {
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
            }
        } else {
            if($data['template_id'] == 0)
            {
                $message = 'Bạn phải chọn Template';
            } else if ($data['type_page_load'] == 0) {
                $message = 'Bạn phải chọn kiểu tải trang';
            } else if ($data['type_tag'] == 0) {
                $message = 'Bạn phải chọn kiểu khai báo Tag cho sản phẩm';
            } else if ($data['catalog_source'] == null) {
                $message = 'Bạn phải điền catalog source';
            } else if ($data['product_source'] == null) {
                $message = 'Bạn phải điền product source';
            }
        }

        $result = [
            'return' => $return,
            'alert' => $alert,
            'message' => $message,
            'data' => $data,
            'verify_data' => $verify_data
        ];
        return $result;
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

        // lấy ra kiểu platform đang scrap
        $type = \DB::table('templates')->select('type_platform')->where('id',$data['template_id'])->first();
        $type_platform_id = ($type != null) ? $type->type_platform : 0;

        $catalog_source = json_decode($data['catalog_source'], true);
        $product_source = json_decode($data['product_source'], true);

        $data['tag_position'] = ($data['tag_position'] != null) ? $data['tag_position'] : 0;
        $data['type_platform'] = $type_platform_id;
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

    // xoá template
    public function deleteTemplate($id) {
        $check = \DB::table('web_scraps')->select('id')->where('template_id',$id)->count();
        if ($check > 0) {
            $alert = 'error';
            $message = 'Không thể xoá Template này. Vẫn còn website scrap sử dụng template này. Cần xoá website scrap trước';
        } else {
            \DB::beginTransaction();
            try {
                $alert = 'success';
                \DB::table('template_variations')->where('template_id',$id)->delete();
                \DB::table('templates')->where('id',$id)->delete();
                $result = true;
                $message = 'Xoá thành công template';
                \DB::commit(); // if there was no errors, your query will be executed
            } catch (\Exception $e) {
                $alert = 'warning';
                $result = false;
                $message = 'Không thể xoá template này. Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
                \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
            }
        }
        return back()->with($alert, $message);
    }

    // xoá scrap web
    public function deleteWebScrap($id) {
        $check = \DB::table('list_products')->select('id')->where('web_scrap_id',$id)->count();
        if ($check > 0) {
            $alert = 'error';
            $message = 'Không thể xoá website scrap này. Vẫn còn product thuộc website này. Cần xoá product trước';
        } else {
            \DB::beginTransaction();
            try {
                $alert = 'success';
                \DB::table('web_scraps')->where('id',$id)->delete();
                $result = true;
                $message = 'Xoá thành công website scrap';
                \DB::commit(); // if there was no errors, your query will be executed
            } catch (\Exception $e) {
                $alert = 'warning';
                $result = false;
                $message = 'Không thể xoá website scrap này. Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
                \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
            }
        }
        return back()->with($alert, $message);
    }
}
