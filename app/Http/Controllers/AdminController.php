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

    public function listTemplate()
    {
        $templates = \DB::table('templates as t')
            ->leftjoin('store_infos as s','s.id', '=', 't.store_info_id')
            ->leftjoin('skus','t.sku_id', '=', 'skus.id')
            ->select(
                't.id', 't.name','t.product_name', 't.type_platform','t.status',
                's.name as store_name', 'skus.sku', 'skus.is_auto as sku_auto'
            )
            ->orderBy('id','DESC')
            ->orderBy('store_info_id')
            ->get()->toArray();
        return view('admin.list_templates',compact('templates'));
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
        $typeTag = $data['typeTag'];
        $templates = $data['templates'];
        $data_template = $data['data_template'];
        $product_template = $data['product_template'];
        return view('user.view_scraper')
            ->with(compact( 'platforms','data_template', 'product_template', 'typePageLoad', 'typeTag', 'templates'));
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
            $typeTag = $data['typeTag'];

            \Session::flash($alert,$message);
            return view('user.last_verify_scrap',
                compact('lst_product', 'platforms', 'typePageLoad', 'typeTag', 'templates'));
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
        $lists = \DB::table('web_scraps as wsc')
            ->leftjoin('templates as temp', 'wsc.template_id', '=', 'temp.id')
            ->leftjoin('skus', 'skus.id', '=', 'temp.sku_id')
            ->leftjoin('store_infos', 'temp.store_info_id', '=', 'store_infos.id')
            ->select(
                'wsc.id', 'wsc.url', 'wsc.template_id', 'wsc.status',
                'temp.name as template_name', 'temp.type_platform',
                'skus.sku', 'skus.is_auto as sku_auto',
                'store_infos.name as store_name'
            )
            ->orderBy('wsc.id','DESC')
            ->orderBy('wsc.template_id','ASC')
            ->get()->toArray();
        return view('user.list_scraper')->with(compact('lists'));
    }

    // xoá template
    public function deleteTemplate($id) {
        $adminModel = new Admin();
        return $adminModel->deleteTemplate($id);
    }
    // xoá scrap web
    public function deleteWebScrap($id) {
        $adminModel = new Admin();
        return $adminModel->deleteWebScrap($id);
    }

    // lưu sản phẩm được thêm bằng tay vào data base
    public function saveDataByHandle(Request $request) {
        $adminModel = new Admin();
        return $adminModel->saveDataByHandle($request);
    }

    // thêm sản phẩm bằng tay
    public function importProductWebScrap($id) {
        $info = \DB::table('web_scraps as wsc')
            ->leftjoin('templates as temp', 'wsc.template_id', '=', 'temp.id')
            ->leftjoin('skus', 'skus.id', '=', 'temp.sku_id')
            ->leftjoin('store_infos', 'temp.store_info_id', '=', 'store_infos.id')
            ->select(
                'wsc.id', 'wsc.url', 'wsc.template_id', 'wsc.status',
                'temp.name as template_name', 'temp.type_platform',
                'skus.sku', 'skus.is_auto as sku_auto',
                'store_infos.name as store_name'
            )
            ->where('wsc.id', $id)
            ->first();
        return view('user.import_product_by_handle', compact('info','id'));
    }
}
