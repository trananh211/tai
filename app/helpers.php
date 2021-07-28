<?php

    use App\Goutte\Client;
    use Symfony\Component\DomCrawler\Crawler;

    function tryCatch()
    {
        \DB::beginTransaction();
        try {

            $result = true;
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {

            $result = false;
            $message = 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        $i = 'json_data';
        $i = json_decode(json_encode($i ,true),true);
    }

    function logfile($str){
        echo $str."\n";
        \Log::channel('custom')->info($str);
    }

    function logfile_system($str){
//        $str .= "\n";
        \Log::channel('custom')->info($str);
        echo $str."\n";
    }

    function dbTime()
    {
        return date("Y-m-d H:i:s");
    }

    function convertTime($str_time)
    {
        return gmdate("H:i:s", $str_time);
    }

    function stores($key)
    {
        $stores = [
            'woo' => env('STORE_WOO_ID'),
            'shop_base' => env('STORE_SHOPBASE_ID')
        ];
        return (array_key_exists($key, $stores)) ? $stores[$key] : 0;
    }

    function getTypeStore($value)
    {
        $class = '';
        $view = '';
        switch ($value) {
            case env('STORE_DEFAUTL_ID'):
                $class = 'bg-orange';
                $view = "UP TAY";
                break;
            case env('STORE_WOO_ID'):
                $class = 'bg-purple';
                $view = "WooCommerce";
                break;
            case env('STORE_SHOPBASE_ID'):
                $class = 'bg-primary';
                $view = "Shop Base";
                break;
        }
        echo '<div class="color-palette-set">
                  <div class="'.$class.' text-center color-palette"><span>'.$view.'</span></div>
                </div>';
    }

    // Data định nghĩa của Scrap Page
    function dataDefine()
    {
        $platforms = [
            env('STORE_SHOPBASE_ID') => 'SHOP BASE',
            env('STORE_WOO_ID') => 'WOOCOMMERCE'
        ];
        $typePageLoad = [
            env('PAGE_LOAD_BUTTON') => 'KIỂU NÚT BẤM',
            env('PAGE_LOAD_SCROLL') => 'KIỂU CUỘN TRANG',
            env('PAGE_LOAD_ONE_PAGE') => 'CHỈ MỘT TRANG'
        ];

        $typeTag = [
            env('TYPE_TAG_FIXED') => 'Đặt cố định 1 Tag',
            env('TYPE_TAG_FIRST_TITLE') => 'Lấy ký tự đầu của Title',
            env('TYPE_TAG_LAST_TITLE') => 'Lấy ký tự cuối của Title',
            env('TYPE_TAG_POSITION_X') => 'Ký tự thứ X của Title',
        ];

        $templates = \DB::table('templates as temp')
            ->leftjoin('store_infos as info', 'temp.store_info_id', '=', 'info.id')
            ->select(
                'temp.id','temp.name','temp.product_name','temp.type_platform',
                'info.name as store_name'
            )
            ->orderBy('temp.type_platform')->get()->toArray();
        $data_template = '
            {
                "url": "https://vetz3d.com/shop?startId=6036571b4dd66d935ec5e512", // Địa chỉ website cần crawl
                "waitSelector": "div.ShopPage", // Dấu hiệu nhận biết khi trang load xong ( Có thể có hoặc không )
                "productItem": "div.ShopPage div.ProductItem", // Thuộc tính cha của product và có lặp lại với mỗi sản phẩm
                "productTitle": "div.BottomProduct > div.Title", // Dấu hiệu nhận biết title của sản phẩm
                "imageSelector": "img.product_card__image",
                "imageAttribute": "data-fallback",
                "imageHttps": "https:",
                "productLink": "a", // Dấu hiệu nhận biết thẻ Link của sản phẩm. Thường là "<a>", "<span>", "<div>"  
                "https_origin": "https://vetz3d.com", // Để trống hoặc chỉ điền khi link sản phẩm thiếu phần đầu website
                "btnNext": "button.ml-2", // Dấu hiệu nhận biết Nút bấm next trang (Để trống nếu kiểu là cuộn trang)
                "signalLastButtonNoClass": ".ShopPagination .d-inline-flex button", // Thuộc tính của nút bấm cuối cùng (Để trống nếu kiểu là cuộn trang)
                "signalAttribute": "class", //Dấu hiệu nhận biết nút bấm cuối cùng có thuộc tính này (Để trống nếu kiểu là cuộn trang)
                "signalClassLastButton": "buttonDisabled", //Dấu hiệu nhận biết nút bấm cuối cùng có thuộc tính này (Để trống nếu kiểu là cuộn trang)
                "url_end" : "https://vetz3d.com/shop?startId=60350adbbf2d6309e9d90385" // Last Page để kiểm tra nút bấm cuối cùng (Để trống nếu kiểu là cuộn trang)
            }
        ';

        $product_template = '
            {
                "productTitle": ".ProductMain .ProductTitle h1", // Title của trang sản phẩm
                "imageSelector": "#main-preview .ProductImage img.img-fluid", // Dấu hiệu nhận biết list Ảnh của trang sản phẩm
                "imageAttribute" : "src", // Thuộc tính của ảnh. có thể là src, href, span, title
                "https_origin": "https:"
            }
        ';

        $results = [
            'platforms' => $platforms,
            'typePageLoad' => $typePageLoad,
            'typeTag' => $typeTag,
            'templates' => $templates,
            'data_template' => $data_template,
            'product_template' => $product_template,
        ];
        return $results;
    }

    // hiển thị ra trạng thái của website
    function getStatus($status)
    {
        $class = '';
        $view = '';
        switch ($status) {
            case env('STATUS_SCRAP_PRODUCT_NEW'):
                $class = 'bg-primary';
                $view = "New";
                break;
            case env('STATUS_SCRAP_PRODUCT_ERROR'):
                $class = 'bg-error';
                $view = "Error";
                break;
            case env('STATUS_SCRAP_PRODUCT_RUNNING'):
                $class = 'bg-warning';
                $view = "Running";
                break;
            case env('STATUS_SCRAP_PRODUCT_READY'):
                $class = 'bg-warning';
                $view = "Ready";
                break;
            case env('STATUS_SCRAP_PRODUCT_PROCESS'):
                $class = 'bg-orange';
                $view = "Process";
                break;

            case env('STATUS_SCRAP_PRODUCT_FINISH'):
                $class = 'bg-success';
                $view = "Finish";
                break;
        }
        echo '<div class="color-palette-set">
                  <div class="'.$class.' text-center color-palette"><span>'.$view.'</span></div>
                </div>';
    }

    /* API SCRAP DATA*/
    function getHeader()
    {
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'testing/1.0',
            env('HEADER_VP6_KEY') => env('HEADER_VP6_VALUE')
        ];
        return $header;
    }

    function postUrl($url, $header, $body)
    {
        try {
            if (is_array($header) && sizeof($header) == 0)
            {
                $header = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'testing/1.0',
                    env('HEADER_VP6_KEY') => env('HEADER_VP6_VALUE')
                ];
            }
            $client = new \GuzzleHttp\Client(['headers' => $header]);
            $response = $client->request('POST', $url, [ 'body' => $body ]);
            $result = $response->getBody();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $data = [
                'status' => 400,
                'result' => 0,
                'message' => 'Xảy ra lỗi ngoài mong đợi : '.$message
            ];
            $result = json_encode($data, true);
        }
        return $result;
    }


    function verifyDataScrap($data)
    {
        $url = env('URL_SERVER_POST_DATA_SCRAP');
        $header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'testing/1.0',
            env('HEADER_VP6_KEY') => env('HEADER_VP6_VALUE')
        ];
        $body = $data['catalog_source'];
        $result = json_decode(postUrl($url, $header, $body), true);
        return $result;
    }
    /* END API SCRAP DATA*/


