<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Scrap;

class ScraperController extends BaseController
{
    /* API */
    public function getListProduct(Request $request){
        $scrapModel = new Scrap();
        try {
            $result = $scrapModel->getListProduct($request);
            if ($result['result'])
            {
                return $this->sendResponse([1], $result['message']);
            } else {
                return $this->sendError($result['message']);
            }
        } catch (\Exception $e) {
            return $this->sendError( $e->getMessage(),'Xảy ra lỗi ngoài mong đợi');
        }
    }

    public function getListProductImages(Request $request){
        $scrapModel = new Scrap();
        try {
            $result = $scrapModel->getListProductImages($request);
            if ($result['result'])
            {
                return $this->sendResponse([1], $result['message']);
            } else {
                return $this->sendError($result['message']);
            }
        } catch (\Exception $e) {
            return $this->sendError( $e->getMessage(),'Xảy ra lỗi ngoài mong đợi');
        }
    }

    public function scrapProduct()
    {
        $scrapModel = new Scrap();
        return $scrapModel->scrapProduct();
    }

    // Hàm lưu thông tin phản hồi scrap của product từ server
    public function saveProductRunning($result)
    {
        $scrapModel = new Scrap();
        return $scrapModel->saveProductRunning($result);
    }
    /*END API*/

    public function getWebScrap()
    {
        $scrapModel = new Scrap();
        return $scrapModel->getWebScrap();
    }

    // hàm gửi thông tin product từ client tới server
    public function sendListProduct()
    {
        logfile_system('=========================== List Product send to Crawl ===========================');
        $result = false;
        $scrapModel = new Scrap();
        // kiểm tra xem có product nào đang scrap hay không
        $check_running = $scrapModel->checkRunningScrapProduct();
        if ($check_running)
        {
            //lấy ra danh sách product mới và gửi tới server
            $return = $scrapModel->getListRunningScrapProduct();
            $result = $return['return'];
        }
        return $result;
    }

    // hàm nhận thông tin product từ server gửi tới client
    public function getProductData(Request $request) {
        $scrapModel = new Scrap();
        try {
            $result = $scrapModel->getProductData($request);
            if ($result['result'])
            {
                return $this->sendResponse([1], $result['message']);
            } else {
                return $this->sendError($result['message']);
            }
        } catch (\Exception $e) {
            return $this->sendError( $e->getMessage(),'Xảy ra lỗi ngoài mong đợi');
        }
    }
}
