<?php

    use App\Goutte\Client;
    use Symfony\Component\DomCrawler\Crawler;

    function tryCatch()
    {
        \DB::beginTransaction();
        try {

            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {

            $message = 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
    }

    function logfile($str){
        echo $str."\n";
        Log::channel('custom')->info($str);
    }

    function logfile_system($str){
        echo $str."\n";
        Log::channel('custom')->info($str);
    }

    function dbTime()
    {
        return date("Y-m-d H:i:s");
    }

    function convertTime($str_time)
    {
        return gmdate("H:i:s", $str_time);
    }

    // Data định nghĩa của Scrap Page
    function dataDefine()
    {
        $platforms = [
            env('PLATFORM_SHOPBASE_ID') => 'SHOP BASE',
            env('PLATFORM_WOOCOMMERCE_ID') => 'WOOCOMMERCE SHOP'
        ];
        $typePageLoad = [
            env('PAGE_LOAD_BUTTON') => 'KIỂU NÚT BẤM',
            env('PAGE_LOAD_SCROLL') => 'KIỂU CUỘN TRANG',
            env('PAGE_LOAD_ONE_PAGE') => 'CHỈ MỘT TRANG'
        ];
        $templates = [
            '1' => 'Shopbase - Tshirt',
            '2' => 'Shopbase - LowTop',
        ];
        $data_template = '
            {
                "url": "https://vetz3d.com/shop?startId=6036571b4dd66d935ec5e512", // Địa chỉ website cần crawl
                "waitSelector": "div.ShopPage", // Chỉ xuất hiện khi trang load xong ( Có thể có hoặc không )
                "productItem": "div.ShopPage div.ProductItem",
                "productTitle": "div.BottomProduct > div.Title",
                "productLink": "a",
                "https_origin": "https://vetz3d.com",
                "btnNext": "button.ml-2",
                "signalParentButton": ".ShopPagination .d-inline-flex button",
                "signalAttribute": "class",
                "signalClassLastButton": "buttonDisabled",
                "url_end" : "https://vetz3d.com/shop?startId=60350adbbf2d6309e9d90385"
            }
        ';

        $product_template = '
            {
                "productTitle": ".ProductMain .ProductTitle h1",
                "imageSelector": "#main-preview .ProductImage img.img-fluid",
                "imageAttribute" : "src"
            }
        ';

        $results = [
            'platforms' => $platforms,
            'typePageLoad' => $typePageLoad,
            'templates' => $templates,
            'data_template' => $data_template,
            'product_template' => $product_template,
        ];
        return $results;
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

    /* SCRAP DATA*/
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
    /* END SCRAP DATA*/


