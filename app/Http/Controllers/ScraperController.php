<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Scrap;

class ScraperController extends BaseController
{
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

    public function scrapProduct()
    {
        $scrapModel = new Scrap();
        $scrapModel->scrapProduct();
    }
}
