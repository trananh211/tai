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

    // connect + setup store
    public function listStore()
    {
        $stores = \DB::table('store_infos')->select('*')->orderBy('type')->get()->toArray();
        return view('admin.list_stores', compact('stores'));
    }

    public function newTemplate($id)
    {
        $adminModel = new Admin();
        $result = $adminModel->newTemplate($id);
        $back = $result['back'];
        $url = $result['url'];
        $data = $result['data'];
        if ($back) {
            return back()->with('warning', 'Không đúng template ID');
        } else {
            return view($url,compact('data'));
        }
    }

    public function connectWoo()
    {
        return view('admin.connect_woo');
    }

    public function getWooInfo(Request $request)
    {
        $adminModel = new Admin();
        $return = $adminModel->getWooInfo($request);
        return back()->with($return['status'], $return['message']);
    }
    // End connect + setup store

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
                $lst_product = (array_key_exists('data', $result))? $result['data'] : null;
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
            return view('user.last_verify_scrap',
                compact('lst_product', 'platforms', 'typePageLoad', 'templates'));
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
        $lists = \DB::table('web_scraps')
            ->select('id', 'url', 'template_id', 'status')
            ->orderBy('template_id')
            ->get()->toArray();
        return view('user.list_scraper')->with(compact('lists'));
    }
}
