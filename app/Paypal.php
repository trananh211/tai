<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Automattic\WooCommerce\Client;
use \DB;

class Paypal extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'paypals';

    //add new paypal
    public function addNewPaypalInfo($request) {
        $data = $request->all();
        $alert = 'error';
        $message = '';
        unset($data['_token']);

        \DB::beginTransaction();
        try {
            \DB::table('paypals')->insert($data);
            $alert = 'success';
            $message = 'Đã lưu thành công dữ liệu.';
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $message .= 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        return redirect('list-paypal')->with($alert, $message);
    }

    //edit paypal
    public function editPaypal($request) {
        $alert = 'error';
        $message = '';
        $data = $request->all();

        $id = $data['id'];
        unset($data['id']);
        unset($data['_token']);
        $data['updated_at'] = dbTime();

        \DB::beginTransaction();
        try {
            \DB::table('paypals')->where('id', $id)->update($data);
            $alert = 'success';
            $message = 'Đã sửa thành công dữ liệu.';
            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {
            $message .= 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
        return back()->with($alert, $message);
    }

    /*
     *  PAYPAL API
     * */
    // change paypal
    public function changePaypalInfo($store_info_id = null) {
        echo 'aaa';
//        $store_info_id = 5;
//        $stores = \DB::table('store_infos as info')
//            ->select('info.url', 'info.consumer_key', 'info.consumer_secret')
//            ->where('id', $store_info_id)
//            ->first();
//        // kết nối với woocomerce store và tạo tag
//        $woocommerce = $this->getConnectStore1($stores->url, $stores->consumer_key, $stores->consumer_secret);
//        print_r($woocommerce->get('payment_gateways'));
    }

    /* End Paypal API*/
}
