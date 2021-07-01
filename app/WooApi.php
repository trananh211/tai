<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Automattic\WooCommerce\Client;
use Session;

class WooApi extends Base
{
    /*WooCommerce API*/
    protected function getConnectStore($url, $consumer_key, $consumer_secret)
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
                'verify_ssl' => false
            ]
        );
        return $woocommerce;
    }

    // ham check template woocommerct
    public function checkTemplate($request) {
        $rq = $request->all();
        unset($rq['_token']);
        if (is_array($rq))
        {
            \Session::put('template_new_woo',$rq);
        }
        $alert = 'error';
        $message = '';
        $back = true;
        $store_info_id = trim($rq['store_info_id']);
        $consumer_key = trim($rq['consumer_key']);
        $consumer_secret = trim($rq['consumer_secret']);
        $sku = trim($rq['sku']);
        $sku_auto = trim($rq['sku_auto']);
        $store_template_id = trim($rq['store_template_id']);
        if ($store_info_id == '' || $consumer_key == '' || $consumer_secret == '') {
            $message = 'Bạn phải chọn Store để tạo template';
        } else if ($sku == '' && $sku_auto == '') {
            $message = 'Bạn phải chọn 1 trong 2 trường SKU hoặc SKU AUTO';
        } else {
            // kiểm tra tồn tại sku chưa
            $string_sku = ($rq['sku'] != null) ? $rq['sku'] : env('SKU_AUTO_STRING').$rq['sku_auto'];
            $string_sku = trim($string_sku);
            $check_sku_exist = $this->checkExistSku($string_sku);
            if ($check_sku_exist) {
                $message = 'SKU : "'.$string_sku.'" đã tồn tại. Mời bạn chọn SKU khác';
            } else {
                // kiểm tra sự tồn tại của template hay chưa
                $check_template_exist = \DB::table('templates')->select('id')
                    ->where('store_info_id', $store_info_id)
                    ->where('store_template_id', $store_template_id)
                    ->first();
                if($check_template_exist == NULL) {
                    $back = false;
                } else {
                    $message = 'Đã tồn tại template này trong hệ thống rồi.';
                }
            }
        }
        if ($back) {
            return back()->with($alert, $message);
        } else {
            // kiểm tra sự tồn tại của Product ID trong store đang lấy template
            $woocommerce = $this->getConnectStore($rq['url'], $rq['consumer_key'], $rq['consumer_secret']);
            try {
                $results = true;
                $i = $woocommerce->get('products/' . $rq['store_template_id']);
            } catch (\Exception $e) {
                $results = false;
            }
            // neu ket noi duoc voi woo store
            if ($results) {
                \DB::beginTransaction();
                try {
                    // lấy ra Sku ID
                    $sku_id = $this->getSkuAutoId($string_sku);
                    //Convert template to Array
                    $tmp = json_decode(json_encode($i ,true),true);
                    $result_templates = $this->processTemplateData($tmp);
                    $data = [
                        'name' => trim($rq['name']),
                        'product_name' => $tmp['name'],
                        'store_template_id' => trim($rq['store_template_id']),
                        'sku_id' => $sku_id,
                        'type_platform' => env('STORE_WOO_ID'),
                        'store_info_id' => trim($rq['store_info_id']),
                        'woo_template_source' => json_encode($result_templates['woo_template_source']),
                        'created_at' => dbTime(),
                        'updated_at' => dbTime()
                    ];
                    $template_id = \DB::table('templates')->insertGetId($data);
                    // make variations
                    $variation_list = $result_templates['variation_list'];
                    if (sizeof($variation_list) > 0) {
                        $this->processTemplateVariation($woocommerce, $variation_list, $template_id, $store_info_id, $store_template_id);
                    }
                    // kiểm tra category
                    if (isset($i->categories[0])) {
                        $tem_category = $i->categories[0];
                        $category_name = $tem_category->name;
                        $woo_category_id = $tem_category->id;
                        $category_data = [
                            'category_name' => $category_name,
                            'woo_category_id' => $woo_category_id,
                            'store_info_id' => $store_info_id,
                            'template_id' => $template_id
                        ];
                        $this->processStoreCategoryWoo($woocommerce, $category_data);
                    }
                    \Session::forget('template_new_woo');
                    $alert = 'success';
                    $message = 'Connect với template thành công';
                    \DB::commit(); // if there was no errors, your query will be executed
                } catch (\Exception $e) {
                    $alert = 'error';
                    $message = 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
                    \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
                }
                return redirect('list-templates')->with($alert, $message);
            } else {
                $alert = 'error';
                $message = 'Không thể tìm thấy product id : '.$rq['store_template_id'].' của store : '.$rq['store_name'];
                return back()->with($alert, $message);
            }
        }
    }

    // Ham phân tích data template để trả về dữ liệu json phục vụ mục đích lưu vào database
    private function processTemplateData($data) {
        //xoa cac key khong can thiet
        $deleted = array('id', 'slug', 'permalink', 'price_html', 'images', '_links', 'meta_data');
        foreach ($deleted as $v) {
            unset($data[$v]);
        }
        // lấy dữ liệu variation list
        $variation_list = $data['variations'];
        $woo_template_source = $data;
        return [
            'woo_template_source' => $woo_template_source,
            'variation_list' => $variation_list
        ];
    }

    // Hàm lưu thông tin data variations list vào database
    private function processTemplateVariation($woocommerce, $variation_list, $template_id, $store_info_id, $store_template_id) {
        for ($j = 0; $j < sizeof($variation_list); $j++) {
            $varid = $variation_list[$j];
            $variation_data = $woocommerce->get('products/' . $template_id . '/variations/' . $varid);
            $insert_variation[] = [
                'store_variation_id' => $varid,
                'template_id' => $template_id,
                'store_template_id' => $store_template_id,
                'store_info_id' => $store_info_id,
                'variation_source' => json_encode($variation_data),
                'created_at' => dbTime(),
                'updated_at' => dbTime()
            ];
        }
        if (sizeof($insert_variation) > 0) {
            \DB::table('template_variations')->insert($insert_variation);
        }
    }

    // Hàm lưu thông tin category data vào database
    private function processStoreCategoryWoo($woocommerce, $data) {
        $category_name = $data['category_name'];
        $store_info_id = $data['store_info_id'];
        // kiểm tra với woo_categories có sẵn tại tool xem tồn tại chưa.
        $check_category = \DB::table('store_categories')->select('id')
            ->where([
                ['name', '=', $category_name],
                ['store_info_id', '=', $store_info_id]
            ])->first();
        if ($check_category != NULL) {
            $store_category_id = $check_category->id;
        } else {
            $data_check = ['slug' => $category_name];
            // kết nối tới woocommerce store để lấy thông tin
            $result = $woocommerce->get('products/categories', $data_check);
            $woo_category_id = $result[0]->id;
            $insert_data = [
                'name' => $category_name,
                'slug' => $result[0]->slug,
                'store_category_id' => $woo_category_id,
                'store_info_id' => $store_info_id,
                'type_platform' => env('STORE_WOO_ID'),
                'created_at' => dbTime(),
                'updated_at' => dbTime()
            ];
            $store_category_id = \DB::table('store_categories')->insertGetId($insert_data);
        }
        \DB::table('templates')->where('id',$data['template_id'])->update(['store_category_id' => $store_category_id]);
    }
}
