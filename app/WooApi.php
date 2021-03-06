<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Automattic\WooCommerce\Client;
use Session;
use \DB;

class WooApi extends Base
{
    /*
     *  CREATE TEMPLATE
     * */

    // ham check template woocommerct
    public function checkTemplate($request)
    {
        $rq = $request->all();
        unset($rq['_token']);
        if (is_array($rq)) {
            \Session::put('template_new_woo', $rq);
        }
        $alert = 'error';
        $message = '';
        $back = true;
        $store_info_id = trim($rq['store_info_id']);
        $consumer_key = trim($rq['consumer_key']);
        $consumer_secret = trim($rq['consumer_secret']);
        $store_template_id = trim($rq['store_template_id']);
        if ($store_info_id == '' || $consumer_key == '' || $consumer_secret == '') {
            $message = 'Bạn phải chọn Store để tạo template';
        } else {
            // kiểm tra sự tồn tại của template hay chưa
            $check_template_exist = \DB::table('templates')->select('id')
                ->where('store_info_id', $store_info_id)
                ->where('store_template_id', $store_template_id)
                ->first();
            if ($check_template_exist == NULL) {
                $back = false;
            } else {
                $message = 'Đã tồn tại template này trong hệ thống rồi.';
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
                    //Convert template to Array
                    $tmp = json_decode(json_encode($i, true), true);
                    $result_templates = $this->processTemplateData($tmp);
                    $data = [
                        'name' => trim($rq['name']),
                        'product_name' => $tmp['name'],
                        'store_template_id' => trim($rq['store_template_id']),
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
                    $message = 'Xảy ra lỗi ngoài mong muốn: ' . $e->getMessage();
                    \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
                }
                return redirect('list-templates')->with($alert, $message);
            } else {
                $alert = 'error';
                $message = 'Không thể tìm thấy product id : ' . $rq['store_template_id'] . ' của store : ' . $rq['store_name'];
                return back()->with($alert, $message);
            }
        }
    }

    // Ham phân tích data template để trả về dữ liệu json phục vụ mục đích lưu vào database
    private function processTemplateData($data)
    {
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
    private function processTemplateVariation($woocommerce, $variation_list, $template_id, $store_info_id, $store_template_id)
    {
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
    private function processStoreCategoryWoo($woocommerce, $data)
    {
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
        \DB::table('templates')->where('id', $data['template_id'])->update(['store_category_id' => $store_category_id]);
    }
    /* ================================= End create template ========================================*/


    /*
     *  CREAT PRODUCT
     *
     * */
    // Hàm chuẩn bị dữ liệu trước khi tạo mới sản phẩm
    public function preCreateProduct()
    {
        logfile_system('==================== CREATE PRODUCT WOOCOMMERCE =====================');
        // kiểm tra website đang ở trạng thái process
        $web_scrap = \DB::table('web_scraps as wsc')
            ->leftjoin('skus', 'wsc.sku_id', '=', 'skus.id')
            ->select(
                'wsc.id as web_scrap_id', 'wsc.url as web_scrap_url', 'wsc.template_id', 'wsc.exclude_text',
                'wsc.image_array', 'wsc.first_title', 'wsc.type_tag', 'wsc.tag_text', 'wsc.tag_position',
                'skus.sku', 'skus.is_auto'
            )
            ->where('wsc.status', env('STATUS_SCRAP_PRODUCT_PROCESS'))
            ->where('wsc.type_platform', env('STORE_WOO_ID'))
            ->first();
        $products = [];
        $templates = [];
        $template_variations = [];
        $check_tag = false;
        $result = false;
        if ($web_scrap == null) {
            logfile_system('Đã hết website để tạo mới product. Chuyển sang công việc khác');
            $result = true;
        } else {
            $web_scrap_id = $web_scrap->web_scrap_id;
            // kiểm tra xem product đã có đủ tag hết chưa.
            $check_tag_null = \DB::table('list_products')->select('id')
                ->where('status', env('STATUS_SCRAP_PRODUCT_READY'))
                ->where('web_scrap_id', $web_scrap_id)
                ->whereNull('store_tag_id')
                ->count();
            // nếu vẫn còn product chưa có tag.
            if ($check_tag_null > 0) {
                logfile_system('Phát hiện ra có ' . $check_tag_null . ' sản phẩm chưa được khai báo tag. Tạo luôn bây giờ');
                $check_tag = true;
            } else {
                logfile_system('Toàn bộ sản phẩm đã được khai báo tag. Chuyển sang tạo mới sản phẩm');
                // lấy ra danh sách product để tạo mới
                $products = \DB::table('list_products as lpd')
                    ->leftjoin('store_tags as tags', 'lpd.store_tag_id', '=', 'tags.id')
                    ->select(
                        'lpd.id as list_product_id', 'lpd.product_name', 'lpd.product_link', 'lpd.count', 'lpd.store_tag_id',
                        'tags.store_tag_id', 'tags.name as tag_name', 'tags.slug'
                    )
                    ->where('lpd.status', env('STATUS_SCRAP_PRODUCT_READY'))
                    ->where('lpd.web_scrap_id', $web_scrap_id)
                    ->orderBy('lpd.id', 'ASC')
                    ->limit(env('LIMIT_CREATE_WOO_PRODUCT'))
                    ->get()->toArray();
                if (sizeof($products) > 0) {
                    $template_id = $web_scrap->template_id;
                    $templates = \DB::table('templates as temp')
                        ->leftjoin('store_infos as info', 'temp.store_info_id', '=', 'info.id')
                        ->leftjoin('store_categories', 'temp.store_category_id', '=', 'store_categories.id')
                        ->select(
                            'store_categories.store_category_id', 'store_categories.name as store_category_name', 'store_categories.slug as store_category_slug',
                            'info.url', 'info.consumer_key', 'info.consumer_secret',
                            'temp.id as template_id', 'temp.woo_template_source'
                        )
                        ->where('temp.id', $template_id)
                        ->first();
                    $template_variations = \DB::table('template_variations')
                        ->select('id', 'template_id', 'variation_source')
                        ->where('template_id', $template_id)
                        ->get()->toArray();
                    $result = true;
                } else {
                    logfile_system('Website ' . $web_scrap->web_scrap_url . ' đã hết product để tạo mới. Cập nhật trạng thái web này.');
                    \DB::table('web_scraps')->where('id', $web_scrap_id)
                        ->update([
                            'status' => env('STATUS_SCRAP_PRODUCT_FINISH'),
                            'updated_at' => dbTime()
                        ]);
                }
            }
        }
        $results = [
            'result' => $result,
            'check_tag' => $check_tag,
            'web_scrap' => $web_scrap,
            'templates' => $templates,
            'template_variations' => $template_variations,
            'products' => $products
        ];
        return $results;
    }

    // Hàm kiểm tra vào tạo mới tag của product
    public function processCreateTag($preData)
    {
        $web_scrap = $preData['web_scrap'];
        $template_id = $web_scrap->template_id;
        $web_scrap_id = $web_scrap->web_scrap_id;
        // lấy ra thông tin kết nối với woocommerce
        $templates = \DB::table('templates as temp')
            ->leftjoin('store_infos as info', 'temp.store_info_id', '=', 'info.id')
            ->select(
                'info.id as store_info_id', 'info.url', 'info.consumer_key', 'info.consumer_secret',
                'temp.id as template_id'
            )
            ->where('temp.id', $template_id)
            ->first();
        // lấy ra danh sách product chưa có tag
        $products = \DB::table('list_products as lpd')
            ->select('lpd.id as list_product_id', 'lpd.product_name')
            ->where('lpd.status', env('STATUS_SCRAP_PRODUCT_READY'))
            ->where('lpd.web_scrap_id', $web_scrap_id)
            ->whereNull('lpd.store_tag_id')
            ->limit(env('LIMIT_CREATE_WOO_TAG'))
            ->get()->toArray();
        // nếu tồn tại product chưa có tag. Tạo mới tag
        if (sizeof($products) > 0) {
            $result = false;
            $check_tag = true;

            $array_tags = [];
            // chia làm 2 trường hợp. tag cố định và không cố định
            if ($web_scrap->type_tag == env('TYPE_TAG_FIXED')) {
                $tag_text = strtolower($this->getStringNormal($web_scrap->tag_text));
                foreach ($products as $product) {
                    $array_tags[$tag_text][] = $product->list_product_id;
                }
            } else {
                foreach ($products as $product) {
                    $product_name = $this->getStringSpecialRemove($product->product_name);
                    $ar_tmp_name = array_filter(explode(" ", $product_name), 'strlen');
                    // nếu tag là ký tự đầu tiên
                    if ($web_scrap->type_tag == env('TYPE_TAG_FIRST_TITLE')) {
                        $tag_text = reset($ar_tmp_name);
                    } else if ($web_scrap->type_tag == env('TYPE_TAG_LAST_TITLE')) { // nếu tag là ký tự cuối cùng
                        $tag_text = end($ar_tmp_name);
                    } else if ($web_scrap->type_tag == env('TYPE_TAG_POSITION_X')) { // nếu tag là kiểu vị trí
                        $position = $web_scrap->tag_position;
                        $tag_text = 'null';
                        $i = 1;
                        foreach ($ar_tmp_name as $text) {
                            if ($position == $i) {
                                $tag_text = $text;
                                break;
                            }
                            $i++;
                        }
                    }
                    $tag_text = strtolower($this->getStringNormal($tag_text));
                    $array_tags[$tag_text][] = $product->list_product_id;
                }
            }
            // trả về toàn bộ tag trong 1 mảng
            $list_tags = array_keys($array_tags);
            // kiểm tra xem hệ thống đã có tag hay chưa
            if (sizeof($list_tags) > 0) {
                $tags = \DB::table('store_tags')
                    ->select('id', 'name', 'slug')
                    ->whereIn('name', $list_tags)
                    ->where('store_info_id', $templates->store_info_id)
                    ->where('type_platform', env('STORE_WOO_ID'))
                    ->get()->toArray();
                $data_tags = [];
                // nếu có tags thì so sánh và lấy ra tag để import.
                if (sizeof($tags) > 0) {
                    foreach ($tags as $key => $tag_info) {
                        $tag_name = $tag_info->name;
                        $data_tags[$tag_name]['info'] = [
                            'store_tag_id' => $tag_info->id,
                            'name' => $tag_info->name,
                            'slug' => $tag_info->slug,
                        ];
                        // nếu tồn tại tên ở trong array tag. thì thêm info và xoá ở array tag
                        if (array_key_exists($tag_name, $array_tags)) {
                            logfile_system('Tồn tại tag : "' . $tag_name . '" trong database. Chỉ cần lấy ra để kết nối product.');
                            $data_tags[$tag_name]['lists'] = $array_tags[$tag_name];
                            unset($array_tags[$tag_name]);
                        }
                    }
                }
//                if (array_key_exists('null', $array_tags)) {
//                    \DB::table('list_products')->whereIn('id',$array_tags['null'])->update([
//                        'store_tag_id' => 0,
//                        'updated_at' => dbTime()
//                    ]);
//                    unset($array_tags['null']);
//                }

                // nếu array tag vẫn cần tạo tag mới.
                if (sizeof($array_tags) > 0) {
                    // kết nối với woocomerce store và tạo tag
                    $woocommerce = $this->getConnectStore($templates->url, $templates->consumer_key, $templates->consumer_secret);
                    foreach ($array_tags as $tag_name => $list_product_ids) {
                        $store_tags_data = [];
                        $data = [
                            'slug' => $tag_name,
                        ];
                        logfile_system('Kết nối tới woocomerce để quét thông tin tag : ' . $tag_name);
                        // kết nối tới woocommerce store để lấy thông tin
                        $result = $woocommerce->get('products/tags', $data);
                        //nếu không thấy thông tin thì tạo mới
                        if (sizeof($result) == 0) {
                            logfile_system('- Không tìm thấy thông tin tag : ' . $tag_name . '. Tạo mới và lưu vào database');
                            $data = [
                                'name' => $tag_name
                            ];
                            try {
                                $i = $woocommerce->post('products/tags', $data);
                                $store_tags_data = [
                                    'name' => $i->name,
                                    'slug' => $i->slug,
                                    'type_platform' => env('STORE_WOO_ID'),
                                    'store_tag_id' => $i->id,
                                    'store_info_id' => $templates->store_info_id,
                                    'created_at' => dbTime(),
                                    'updated_at' => dbTime()
                                ];
                                $store_tag_name = $i->name;
                                $store_tag_slug = $i->slug;
                            } catch (\Exception $e) {
                                $data = [
                                    'name' => 'null'
                                ];
                                $result = $woocommerce->get('products/tags', $data);
                                $store_tags_data = [
                                    'name' => $result[0]->name,
                                    'slug' => $result[0]->slug,
                                    'type_platform' => env('STORE_WOO_ID'),
                                    'store_tag_id' => $result[0]->id,
                                    'store_info_id' => $templates->store_info_id,
                                    'created_at' => dbTime(),
                                    'updated_at' => dbTime()
                                ];
                                $store_tag_name = $result[0]->name;
                                $store_tag_slug = $result[0]->slug;
                            }

                        } else { // nếu thấy thông tin thì lấy dữ liệu và lưu về database
                            logfile_system('- Đã tồn tại thông tin tag : ' . $tag_name . '. Tạo mới và lưu vào database');
                            $store_tags_data = [
                                'name' => $result[0]->name,
                                'slug' => $result[0]->slug,
                                'type_platform' => env('STORE_WOO_ID'),
                                'store_tag_id' => $result[0]->id,
                                'store_info_id' => $templates->store_info_id,
                                'created_at' => dbTime(),
                                'updated_at' => dbTime()
                            ];
                            $store_tag_name = $result[0]->name;
                            $store_tag_slug = $result[0]->slug;
                        }
                        // nếu tồn tại store_tag_data thì tạo mới
                        if (sizeof($store_tags_data) > 0) {
                            logfile_system('- Lưu thông tin của tags : "' . $tag_name . '" vào database thành công');
                            $store_tags_id = \DB::table('store_tags')->insertGetId($store_tags_data);
                            $data_tags[$tag_name]['info'] = [
                                'store_tag_id' => $store_tags_id,
                                'name' => $store_tag_name,
                                'slug' => $store_tag_slug
                            ];
                            // nếu tồn tại tên ở trong array tag. thì thêm info và xoá ở array tag
                            if (array_key_exists($tag_name, $array_tags)) {
                                $data_tags[$tag_name]['lists'] = $list_product_ids;
                            }

                        }

                    }
                }
            }
            // nếu tồn thông tin data tag
            if (sizeof($data_tags) > 0) {
                foreach ($data_tags as $tag_name => $info) {
                    $update_data = [
                        'store_tag_id' => $info['info']['store_tag_id'],
                        'updated_at' => dbTime()
                    ];
                    if (array_key_exists('lists', $info) && sizeof($info['lists']) > 0) {
                        \DB::table('list_products')->whereIn('id', $info['lists'])->update($update_data);
                    }
                }
            }
        } else {
            $result = true;
            $check_tag = false;
        }
        $preData['result'] = $result;
        $preData['$check_tag'] = $check_tag;

        return $preData;
    }

    // bắt đầu tạo mới sản phẩm
    public function processCreateProduct($preData)
    {
        $web_scrap = $preData['web_scrap'];
        $products = $preData['products'];
        $templates = $preData['templates'];
        $template_variations = $preData['template_variations'];

        // nếu tồn tại product để tạo mới
        if (sizeof($products) > 0) {
            \DB::beginTransaction();
            try {
                $woocommerce = $this->getConnectStore($templates->url, $templates->consumer_key, $templates->consumer_secret);
                $product_success = [];
                $product_error = [];
                // bắt đầu tạo mới sản phẩm.
                foreach ($products as $product) {
                    try {
                        $prod_data = [];
                        // trả về sku
                        $sku = ($web_scrap->is_auto == 1) ? $web_scrap->sku . (env('SKU_AUTO_BEGIN') + $product->count) : $web_scrap->sku;
                        $product_name = $this->getProductName($product->product_name, $sku, $web_scrap->exclude_text, $web_scrap->first_title);

                        // chuẩn bị dữ liệu data
                        $prod_data = $this->preProductData(json_decode($templates->woo_template_source, true));
                        // edit lại prod_data
                        $prod_data['name'] = $product_name;
                        $prod_data['categories'] = [
                            ['id' => $templates->store_category_id]
                        ];
                        if (is_numeric($product->store_tag_id) && $product->store_tag_id > 0) {
                            $prod_data['tags'][] = [
                                'id' => $product->store_tag_id,
                                'name' => $product->tag_name,
                                'slug' => $product->slug
                            ];
                        }
                        //Kết nối với woocommerce
                        $save_product = $woocommerce->post('products', $prod_data);
                        $woo_product_id = $save_product->id;
                        \DB::table('list_products')->where('id',$product->list_product_id)->update([
                            'store_product_id' => $woo_product_id,
                            'type_platform' => env('STORE_WOO_ID')
                        ]);
                        // tạo variation của product
                        if (sizeof($template_variations) > 0) {
                            foreach ($template_variations as $variation) {
                                $variation_data = $this->preVariationProductData(json_decode($variation->variation_source, true));
                                $woocommerce->post('products/' . $woo_product_id . '/variations', $variation_data);
                            }
                        }
                        logfile_system('-- Tạo thành công sản phẩm ' . $product_name);
                        $product_success[] = $product->list_product_id;
                    } catch (\Exception $e) {
                        logfile_system('-- [Error] Tạo sản phẩm ' . $product_name . ' thất bại');
                        $product_error[] = $product->list_product_id;
                    }
                }
                // nếu product tạo thành công
                if (sizeof($product_success) > 0) {
                    logfile_system('-- Cập nhật trạng thái ' . sizeof($product_success) . ' products tạo thành công vào database');
                    \DB::table('list_products')->whereIn('id', $product_success)->update([
                        'status' => env('STATUS_SCRAP_PRODUCT_PROCESS'),
                        'updated_at' => dbTime()
                    ]);
                }
                // nếu product tạo thất bại
                if (sizeof($product_error) > 0) {
                    logfile_system('-- Cập nhật trạng thái ' . sizeof($product_success) . ' products tạo thất bại vào database');
                    \DB::table('list_products')->whereIn('id', $product_error)->update([
                        'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                        'updated_at' => dbTime()
                    ]);
                }
                \DB::commit(); // if there was no errors, your query will be executed
            } catch (\Exception $e) {
                $message = 'Xảy ra lỗi ngoài mong muốn: ' . $e->getMessage();
                \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
            }
            $result = false;
        } else {
            logfile_system('Đã hết sản phẩm để tạo mới. Chuyển sang công việc khác');
            $result = true;
        }
        $results = [
            'result' => $result
        ];
        return $results;
    }

    private function preProductData($json)
    {
        $data = [
            'name' => $json['name'],
            'type' => $json['type'],
            'status' => 'draft',
            'description' => html_entity_decode($json['description']),
            'price' => $json['price'],
            'regular_price' => $json['regular_price'],
            'sale_price' => $json['sale_price'],
            'on_sale' => $json['on_sale'],
            'stock_status' => $json['stock_status'],
            'reviews_allowed' => $json['reviews_allowed'],
            'tags' => $json['tags'],
            'attributes' => $json['attributes'],
            'images' => [],
            'date_created' => date("Y-m-d H:i:s", strtotime(" -1 days"))
        ];
        return $data;
    }

    private function preVariationProductData($variation_json)
    {
        $variation_data = [
            'price' => $variation_json['price'],
            'regular_price' => $variation_json['regular_price'],
            'sale_price' => $variation_json['sale_price'],
            'status' => $variation_json['status'],
            'attributes' => $variation_json['attributes'],
            'menu_order' => $variation_json['menu_order']
        ];
        return $variation_data;
    }

    // Hàm tạo ảnh product
    public function createImageProductWoo() {
        logfile_system('Up ảnh sản phẩm lên product.');
        // lấy ra danh sách sản phẩm chưa up image
        $products = \DB::table('list_products as lpd')
            ->select(
                'lpd.id', 'lpd.web_scrap_id', 'product_name','store_product_id'
            )
            ->where('lpd.type_platform', env('STORE_WOO_ID'))
            ->where('lpd.status',env('STATUS_SCRAP_PRODUCT_PROCESS'))
            ->limit(env('LIMIT_PRODUCT_IMAGE_IMPORT'))
            ->get()->toArray();
        if (sizeof($products) > 0) {
            $list_web_scrap_id = [];
            $list_product_id = [];
            $list_products = [];
            foreach ($products as $product) {
                $list_product_id[] = $product->id;
                $list_web_scrap_id[] = $product->web_scrap_id;
                $list_products[$product->web_scrap_id][$product->id] = json_decode(json_encode($product), true);
            }
            // lấy ra danh sách website và images
            $web_scraps = \DB::table('web_scraps as wsc')
                ->leftjoin('templates as tmp','wsc.template_id', '=', 'tmp.id')
                ->leftjoin('store_infos as info', 'tmp.store_info_id', '=', 'info.id')
                ->select(
                    'wsc.id as web_scrap_id', 'wsc.image_array', 'tmp.store_info_id',
                    'info.url', 'info.consumer_key', 'info.consumer_secret'
                )
                ->whereIn('wsc.id',$list_web_scrap_id)
                ->get()->toArray();
            $data = [];
            if (sizeof($web_scraps) > 0) {
                $list_web_scraps = [];
                foreach ($web_scraps as $info) {
                    $image_array = (strlen($info->image_array) > 0) ? $info->image_array : '1,2,3,4';
                    $tmp_img = explode(',', $image_array);
                    $tmp_img = $this->getArrayTrue($tmp_img);
                    $web_scrap_id = $info->web_scrap_id;
                    $list_web_scraps[$web_scrap_id] = json_decode(json_encode($info), true);
                    $list_web_scraps[$web_scrap_id]['image_sort'] = $tmp_img;
                    if (array_key_exists($web_scrap_id, $list_products)) {
                        $data[$web_scrap_id]['info'] = json_decode(json_encode($info), true);
                        $data[$web_scrap_id]['data'] = $list_products[$web_scrap_id];
                    }
                }
                // lấy danh sách images
                $list_images = \DB::table('product_images as img')
                    ->select('img.web_scrap_id', 'img.name', 'img.list_product_id', 'img.url as link', 'img.position')
                    ->whereIn('img.list_product_id', $list_product_id)
                    ->get()->toArray();
                $images = [];
                // sort image
                foreach ($list_web_scraps as $web_scrap_id => $info) {
                    $image_sort = $info['image_sort'];
                    foreach ($image_sort as $position) {
                        foreach ($list_images as $image) {
                            if ($image->position == $position) {
                                $images[$image->list_product_id][] = [
                                    'src' => $image->link,
                                    'name' => $image->name,
                                    'alt' => $image->name
                                ];
                            }
                        }
                    }
                }

                $list_product_success = [];
                $list_product_error = [];
                if (sizeof($data) > 0) {
                    foreach ($data as $web_scrap_id => $item) {
                        // kết nối tới woocommerce
                        $woocommerce = $this->getConnectStore($item['info']['url'], $item['info']['consumer_key'], $item['info']['consumer_secret']);
                        // lặp các product và up ảnh sản phẩm
                        foreach ($item['data'] as $list_product_id => $product) {
                            $woo_product_id = $product['store_product_id'];
                            $woo_product_name = $product['product_name'];
                            $data_image = (array_key_exists($list_product_id, $images)) ? $images[$list_product_id] : [];
                            // nếu product tồn tại ảnh. thì upload.
                            if (sizeof($data_image) > 0) {
                                $data = [
                                    'id' => $woo_product_id,
                                    'status' => 'publish',
                                    'images' => $data_image
                                ];
                                $result = $woocommerce->put('products/' . $woo_product_id, $data);
                                try {
                                    $try = true;
                                    $result = $woocommerce->put('products/' . $woo_product_id, $data);
                                } catch (\Exception $e) {
                                    $try = false;
                                }
                                if ($try)
                                {
                                    $woo_slug = $result->permalink;
//                                    \DB::table('scrap_products')->where('id',$scrap_product_id)->update(['woo_slug' => $woo_slug]);
                                    logfile_system('-- Đã chuẩn bị thành công data image của sản phẩm ' . $woo_product_name);
                                    $list_product_success[$list_product_id] = $list_product_id;
                                } else {
                                    $list_product_error[$list_product_id] = $list_product_id;
                                    logfile_system('-- Thất bại. Không chuẩn bị được image data của sản phẩm ' . $woo_product_name);
                                }

                            } else { // nếu không tồn tại ảnh thì bỏ qua
                                $list_product_error[$list_product_id] = $list_product_id;
                            }
                        }
                    }

                    if (sizeof($list_product_success) > 0) {
                        \DB::table('list_products')->whereIn('id', $list_product_success)->update([
                            'status' => env('STATUS_SCRAP_PRODUCT_FINISH'),
                            'updated_at' => dbTime()
                        ]);
                        \DB::table('product_images')->whereIn('list_product_id', $list_product_success)->update([
                            'status' => env('STATUS_SCRAP_PRODUCT_FINISH'),
                            'updated_at' => dbTime()
                        ]);
                    }

                    if (sizeof($list_product_error) > 0) {
                        \DB::table('list_products')->whereIn('id', $list_product_error)->update([
                            'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                            'updated_at' => dbTime()
                        ]);
                        \DB::table('product_images')->whereIn('list_product_id', $list_product_error)->update([
                            'status' => env('STATUS_SCRAP_PRODUCT_ERROR'),
                            'updated_at' => dbTime()
                        ]);
                    }
                }
            }
            $result = false;
        } else {
            logfile_system('Đã hết product để upload Image. Chuyển sang công việc khác');
            $result = true;
        }
        return [
            'result' => $result
        ];
    }
    /* ================================= End create product ========================================*/

    /*
     *  API new order, update order, update tracking info to paypal
     * */
    private function getWooSkuInfo()
    {
        return \DB::table('store_infos')->pluck('sku', 'id')->toArray();
    }

    // lấy thông tin của paypal hiện tại
    private function getPaypalInfo($woo_id)
    {
        $check_paypal = \DB::table('paypals')
            ->select('id','profit_limit','profit_value')->where('store_info_id', $woo_id)
            ->where('status', env('STATUS_PAYPAL_ACTIVE'))->first();
        return $check_paypal;
    }

    private function changePaypalInfo($paypal_id, $store_info_id)
    {
        logfile_system('Đang thực hiện thay đổi paypay id: ' . $paypal_id . ' của store : ' . $store_info_id);
        \DB::beginTransaction();
        try {
            // lấy ra paypal đang đủ điều kiện
            $other_paypal = \DB::table('paypals')
                ->select('id', 'api_email', 'api_pass', 'api_merchant_id', 'api_signature', 'api_client_id', 'api_secret')
                ->where('store_info_id', $store_info_id)
                ->where('status', env('STATUS_PAYPAL_READY'))
                ->where('id', '<>', $paypal_id)
                ->orderby('id', 'ASC')
                ->first();
            // nếu toàn bộ paypal account đã được luân phiên sử dụng. chúng ta sẽ reset lại toàn bộ account và thực hiện lại ở order sau
            if ($other_paypal == NULL) {
                \DB::table('paypals')
                    ->where('store_info_id', $store_info_id)
                    ->whereNotIn('status', array(env('STATUS_PAYPAL_NEW'), env('STATUS_PAYPAL_LIMITED')))
                    ->where('id', '<>', $paypal_id)
                    ->update([
                        'status' => env('STATUS_PAYPAL_READY'),
                        'profit_value' => 0,
                        'updated_at' => dbTime()
                    ]);
            } else { // nếu có paypal thoả mãn yêu cầu. thực hiện change cổng thanh toán
                $stores = \DB::table('store_infos as info')
                    ->select('info.url', 'info.consumer_key', 'info.consumer_secret')
                    ->where('id', $store_info_id)
                    ->first();
                // kết nối với woocomerce store
                $woocommerce = $this->getConnectStore($stores->url, $stores->consumer_key, $stores->consumer_secret);
                // chuẩn bị data thay đổi
                $data = [
                    'settings' => [
                        env('PAYPAL_TYPE').'_username' => $other_paypal->api_email,
                        env('PAYPAL_TYPE').'_password' => $other_paypal->api_pass,
                        env('PAYPAL_TYPE').'_signature' => $other_paypal->api_signature,
                        env('PAYPAL_TYPE').'_client_id' => $other_paypal->api_client_id,
                        env('PAYPAL_TYPE').'_client_secret' => $other_paypal->api_secret
                    ]
                ];
                $r1 = $woocommerce->get('payment_gateways');
                print_r($r1);
                // thực hiện thay đổi payment gateways
                $r = $woocommerce->put('payment_gateways/' . env('PAYPAL_GATEWAVE'), $data);
                if ($r) {
                    // chuyển paypal cũ về trạng thái đã sử dụng xong
                    \DB::table('paypals')->where('id', $paypal_id)->update(['status' => env('STATUS_PAYPAL_DONE')]);
                    // chuyển paypal mới về trạng thái đang sử dụng
                    \DB::table('paypals')->where('id', $other_paypal->id)->update(['status' => env('STATUS_PAYPAL_ACTIVE')]);
                    logfile_system('Chuyển paypal id từ ' . $paypal_id . ' sang ' . $other_paypal->id . ' thành công');
                }
            }
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $return = false;
            $save = "[Error] Save to database error.";
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
    }

    /*Create new order*/
    public function createOrder($data, $woo_id)
    {
        $db = array();
        $str = '';
        logfile_system('=====================CREATE NEW ORDER=======================');
        if (sizeof($data['line_items']) > 0) {
            logfile_system('Store ' . $woo_id . ' has new ' . sizeof($data['line_items']) . ' order item.');
            $woo_infos = $this->getWooSkuInfo();
            $paypal_id = 0;
            $paypal_profit_limit = 1;
            $paypal_profit_value = 1;
            $paypal_info = $this->getPaypalInfo($woo_id);
            if ($paypal_info != null) {
                $paypal_id = $paypal_info->id;
                $paypal_profit_limit = $paypal_info->profit_limit;
                $paypal_profit_value = $paypal_info->profit_value;
            }
            $lst_product = array();
            $tmp_orders = array();
            $change_pp = false;
            foreach ($data['line_items'] as $key => $value) {
                if (in_array($data['status'], array('failed', 'cancelled', 'pending'))) {
                    continue;
                }
                if (!in_array($data['number'], $tmp_orders))
                {
                    $shipping_cost = $data['shipping_total'];
                    $tmp_orders[] = $data['number'];
                } else {
                    $shipping_cost = 0;
                }
                if (strpos(($value['name']), "-") !== false) {
                    $value['name'] = trim(explode("-", $value['name'])[0]);
                }
                $db[] = [
                    'store_info_id' => $woo_id,
                    'order_id' => $data['id'],
                    'number' => $data['number'],
                    'order_status' => $data['status'],
                    'product_id' => $value['product_id'],
                    'product_name' => $value['name'],
                    'quantity' => $value['quantity'],
                    'payment_method' => trim($data['payment_method_title']),
                    'paypal_id' => (trim(strtolower($data['payment_method_title'])) == 'paypal') ? $paypal_id : 0,
                    'customer_note' => trim(htmlentities($data['customer_note'])),
                    'transaction_id' => $data['transaction_id'],
                    'price' => $value['price'],
                    'shipping_cost' => $shipping_cost,
                    'email' => $data['billing']['email'],
                    'last_name' => trim($data['shipping']['last_name']),
                    'first_name' => trim($data['shipping']['first_name']),
                    'fullname' => $data['shipping']['first_name'] . ' ' . $data['shipping']['last_name'],
                    'address' => (strlen($data['shipping']['address_2']) > 0) ? $data['shipping']['address_1'] . ', ' . $data['shipping']['address_2'] : $data['shipping']['address_1'],
                    'city' => $data['shipping']['city'],
                    'postcode' => $data['shipping']['postcode'],
                    'country' => $data['shipping']['country'],
                    'state' => $data['shipping']['state'],
                    'phone' => $data['billing']['phone'],
                    'created_at' => dbTime(),
                    'updated_at' => dbTime()
                ];
                $lst_product[] = $value['product_id'];
                $price_total = $value['price'] + $shipping_cost;
                $paypal_profit_value += $price_total;
                if ($paypal_profit_value >= $paypal_profit_limit) {
                    $change_pp = true;
                }
            }
        }
        // nếu số tiền lớn hơn giới hạn. thực hiện change pp
        if ($change_pp) {
            $this->changePaypalInfo($paypal_id, $woo_id);
        }
        if (sizeof($db) > 0) {
            \DB::beginTransaction();
            try {
                // cập nhật paypal value vào paypal hiện tại
                \DB::table('paypals')->where('id',$paypal_id)->update(['profit_value' => $paypal_profit_value]);
                // tạo mới new order
                \DB::table('woo_orders')->insert($db);
                $str = "Tạo mới order. Save to database successfully";
                \DB::commit(); // if there was no errors, your query will be executed
            } catch (\Exception $e) {
                $str = "[Error] Tạo mới order. Save to database error.";
                \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
            }
        } else {
            $str = 'Không phải là đơn hàng thanh toán thành công. Bỏ qua';
        }
        logfile_system($str . "\n");
    }
    /* End API new order, update order , update tracking info paypal*/

    public function changeInfoProduct()
    {
        $return = false;
        \DB::beginTransaction();
        try {
            logfile_system('==== Bắt đầu thay đổi thông tin product =========================');
            // lấy data từ bảng scrap_products
            $products = $this->getInfoPreProduct();
            // nếu tồn tại thông tin cần thay đổi. Thực hiện thay đổi
            if ($products['result']) {
                $return = $this->ChangingInfoProduct($products['store_info'], $products['products']);
            } else {
                $return = true;
            }
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
            $message = 'Xảy ra lỗi nội bộ : ' . $e->getMessage();
            logfile_system($message);
        }
        return $return;
    }

    private function getInfoPreProduct()
    {
        $result = false;
        $store_info = array();
        $products = array();
        logfile_system('-- Bắt đầu kiểm tra web_scraps theo thứ tự');
        $check = \DB::table('web_scraps')
            ->select('id')->where('t_status', env('T_STATUS_CHANGE_INFO_RUNNING'))->first();
        $web_scrap_id = false;
        if ($check != NULL) {
            logfile_system('-- Vẫn còn web scraps '.$check->id.' đang running. Chờ web scrap này hoàn thành trước');
            $web_scrap_id = $check->id;
        } else {
            // lấy web scrap gần nhất
            $web_scraps = \DB::table('web_scraps')->select('id')
                ->where('t_status', env('T_STATUS_CHANGE_INFO_READY'))->first();
            if ($web_scraps != NULL) {
                $web_scrap_id = $web_scraps->id;
            }
        }
        if ($web_scrap_id != false) {
            $products = \DB::table('list_products as lp')
                ->select(
                    'lp.id as product_id', 'lp.web_scrap_id', 'lp.store_product_id as woo_product_id',
                    'lp.product_name', 'lp.count'
                )
                ->where('lp.t_status', env('T_STATUS_CHANGE_INFO_READY'))
                ->where('lp.web_scrap_id', $web_scrap_id)
                ->limit(env('LIMIT_CHANGE_INFO_PRODUCT'))
                ->get()->toArray();

            $store_info = \DB::table('web_scraps as wsc')
                ->leftjoin('templates as t', function ($join) {
                    $join->on('t.id', '=', 'wsc.template_id');
                })
                ->leftjoin('skus as sku', function ($join) {
                    $join->on('sku.id', '=', 'wsc.sku_id');
                })
                ->leftjoin('store_infos as info', function ($join) {
                    $join->on('info.id', '=', 't.store_info_id');
                })
                ->select(
                    'wsc.id as web_scrap_id', 'wsc.exclude_text', 'wsc.product_name_change', 'wsc.product_name_exclude',
                    'wsc.first_title',
                    't.name as template_name', 't.origin_price', 't.sale_price', 't.woo_template_source',
                    'sku.sku', 'sku.is_auto',
                    'info.id as store_info_id', 'info.url', 'info.consumer_key', 'info.consumer_secret'
                )
                ->where('wsc.id', $web_scrap_id)
                ->first();
            $result = true;
        } else {
            logfile_system('-- Đã hết web scraps để thay đổi thông tin product');
        }
        $results = [
            'result' => $result,
            'store_info' => $store_info,
            'products' => $products
        ];
        return $results;
    }

    private function ChangingInfoProduct($info, $products) {
        $return = false;
        $scrap_id_success = array();
        $scrap_id_error = array();
        // nếu tồn tại products thì bắt đầu thay đổi.
        if (sizeof($products) > 0) {
            // cập nhật trạng thái web scrap thành Changing
            \DB::table('web_scraps')->where('id', $info->web_scrap_id)
                ->update(['t_status' => env('T_STATUS_CHANGE_INFO_RUNNING')]);
            $woocommerce = $this->getConnectStore($info->url, $info->consumer_key, $info->consumer_secret);
            $info_template = json_decode($info->woo_template_source, true);
            foreach ($products as $product) {
                // trả về sku
                $sku = ($info->is_auto == 1) ? $info->sku . (env('SKU_AUTO_BEGIN') + $product->count) : $info->sku;
                $product_name_tmp = $this->getProductName($product->product_name, $sku, $info->exclude_text, $info->first_title);
                $product_name = str_replace(trim(ucwords($info->product_name_exclude)), trim(ucwords($info->product_name_change)), $product_name_tmp);
                // trả về giá nếu tồn tại giá
                $price = ($info->origin_price > 0 || $info->sale_price > 0)? ($info->sale_price > 0) ? $info->sale_price : $info->origin_price : $info_template['price'];
                $regular_price = ($info->origin_price > 0) ? $info->origin_price : $info_template['regular_price'];
                $price = (string) $price;
                $regular_price = (string) $regular_price;

                $update = [
                    'name' => $product_name,
                    'price' => $price,
                    'regular_price' => $regular_price,
                    'sale_price' => $price
                ];

                $data_update_variations = array();
                try {
                    $woo_product_id = $product->woo_product_id;
                    logfile_system('-- Đang thay thông tin product id : ' . $woo_product_id.' có name: '.$product_name);
                    $result_change = $woocommerce->put('products/' . $product->woo_product_id, $update);
                    $variations_id = $result_change->variations;
                    if (sizeof($variations_id) > 0)
                    {
                        foreach ($variations_id as $vari_id)
                        {
                            $data_update_variations['update'][] = [
                                'id' => $vari_id,
                                'price' => $price,
                                'regular_price' => $regular_price,
                                'sale_price' => $price
                            ];
                        }
                        $result_variations = $woocommerce->post('products/'.$woo_product_id.'/variations/batch', $data_update_variations);
                    }
                    $check = true;
                } catch (\Exception $e) {
                    $check = false;
                    logfile_system('-- Không connect được với product id : ' . $product->woo_product_id.' có name: '.$product_name);
                }
                if ($check) {
                    $scrap_id_success[] = $product->product_id;
                } else {
                    $scrap_id_error[] = $product->product_id;
                }
            }
            if (sizeof($scrap_id_success) > 0) {
                \DB::table('list_products')->whereIn('id',$scrap_id_success)
                    ->update(['t_status' => env('T_STATUS_CHANGE_INFO_NEW')]);
            }
            if (sizeof($scrap_id_error) > 0) {
                \DB::table('list_products')->whereIn('id',$scrap_id_error)
                    ->update(['t_status' => env('T_STATUS_CHANGE_INFO_ERROR')]);
            }
        } else {
//            $return = true;
            // đã hết product để thay đổi thông tin. Cập nhật trạng thái web scraps
            \DB::table('web_scraps')->where('id', $info->web_scrap_id)
                ->update(['t_status' => env('T_STATUS_CHANGE_INFO_NEW')]);
        }
        return $return;
    }

}
