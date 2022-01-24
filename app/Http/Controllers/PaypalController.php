<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Paypal;

class PaypalController extends BaseController
{
    // list paypals
    public function listPaypal() {
        $lists = \DB::table('paypals as pp')
            ->leftjoin('store_infos', 'pp.store_info_id', '=', 'store_infos.id')
            ->select(
                'pp.id', 'pp.api_email', 'pp.api_pass', 'pp.api_signature','pp.api_client_id','pp.api_secret',
                'pp.status', 'pp.profit_limit', 'pp.profit_value',
                'store_infos.id as store_info_id', 'store_infos.name as store_name'
            )
            ->get()->toArray();
        return view('admin.paypal.list_paypals', compact('lists'));
    }

    //get form add new paypal
    public function getNewPaypal($id = null) {
        $status = [
            env('STATUS_PAYPAL_NEW') => 'New',
            env('STATUS_PAYPAL_READY') => 'Ready',
            env('STATUS_PAYPAL_ACTIVE') => 'Actived',
            env('STATUS_PAYPAL_DONE') => 'Done',
            env('STATUS_PAYPAL_LIMITED') => 'Limited Paypal'
        ];
        if (isset($id)) {
            $paypal = \DB::table('paypals as pp')
                ->select(
                    'pp.id', 'pp.api_email', 'pp.api_pass', 'pp.api_signature','pp.api_client_id','pp.api_secret',
                    'pp.status', 'pp.profit_limit', 'pp.profit_value', 'pp.api_merchant_id', 'pp.store_info_id'
                )
                ->where('id', $id)
                ->first();
        } else {
            $paypal = false;
        }
        $stores = \DB::table('store_infos')->select('id','name')->get()->toArray();
        return view('admin.paypal.add_new_paypal', compact('stores','paypal', 'status'));
    }

    //add new paypal
    public function addNewPaypalInfo(Request $request) {
        $paypalModel = new Paypal();
        return $paypalModel->addNewPaypalInfo($request);
    }

    //edit info paypal
    public function editPaypal(Request $request) {
        $paypalModel = new Paypal();
        return $paypalModel->editPaypal($request);
    }

    /*
     *  Paypal API
     * */

    // change paypal
    public function changePaypalInfo($store_info_id = null) {
        $paypalModel = new Paypal();
        $paypalModel->changePaypalInfo($store_info_id);
    }

    /*
     *  End Paypal API
     * */
}
