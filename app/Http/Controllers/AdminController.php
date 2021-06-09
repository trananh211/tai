<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use App\Scrap;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * show dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    // Hiển thị trang scrap setup ban đầu
    public function viewScraper()
    {
        $data = dataDefine();
        $platforms = $data['platforms'];
        $typePageLoad = $data['typePageLoad'];
        $templates = $data['templates'];
        $data_template = $data['data_template'];
        $product_template = $data['product_template'];
        return view('user.view_scraper')
            ->with(compact( 'platforms','data_template', 'product_template', 'typePageLoad', 'templates'));
    }

    //Nhận data scrap setup ban đầu
    public function dataScrapSetup(Request $request)
    {
        $back = true;
        $adminModel = new Admin();
        $data_return = $adminModel->verifydataScrapSetup($request);
        // nếu sai định dạng.
        if ($data_return['return'] == 0)
        {
            $alert = $data_return['alert'];
            $message = $data_return['message'];
        } else { // Gửi sang verify source xem có crawl được data hay không
            $scrapModel = new Scrap();
            $result = $scrapModel->verifyDataScrapBeforeSave($data_return['verify_data']);
            if (is_array($result) && array_key_exists('return', $result) && $result['return'] == 1)
            {
//                $return = $adminModel->saveDataScrap($data);
                $lst_product = (array_key_exists('data', $result))? $result['data'] : null;
//                $lst_product = json_decode($lst_product, true);
                $alert = $result['alert'];
                $message = $result['message'];
                $back = false;
            } else {
                $alert = 'error';
                $message = (is_array($result) && array_key_exists('message', $result)) ? $result['message'] : 'Xảy ra lỗi ngoài mong muốn';
            }
        }
        if ($back)
        {
            return back()->with($alert, $message);
        } else {
            $data = dataDefine();
            $platforms = $data['platforms'];
            $typePageLoad = $data['typePageLoad'];
            $templates = $data['templates'];

            \Session::flash($alert,$message);
            return view('user.last_verify_scrap', compact('lst_product', 'platforms', 'typePageLoad', 'templates'));
        }

    }

    // lưu data scrap
    public function saveDataScrapSetup(Request $request)
    {
        $adminModel = new Admin();
        $result = $adminModel->saveDataScrapSetup($request);
        if ($result['result']) {
            $url = 'list-scraper';
        } else {
            $url = 'view-scraper';
        }
        return redirect($url)->with($result['alert'], $result['message']);
    }

    // list thông tin các danh sách website scraper
    public function getListScraper() {
        echo "aaaaa";
    }
}
