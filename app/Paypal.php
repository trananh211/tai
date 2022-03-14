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
//        print_r($data);
//        die();
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

    //Test
    public function testPaymentGateWay() {
        $store_info_id = 5;
        $paypal_id = 1;
        $stores = \DB::table('store_infos as info')
            ->select('info.url', 'info.consumer_key', 'info.consumer_secret')
            ->where('id', $store_info_id)
            ->first();
        $other_paypal = \DB::table('paypals')
            ->select('id', 'api_email', 'api_username' ,'api_pass', 'api_merchant_id', 'api_signature', 'api_client_id', 'api_secret')
            ->where('store_info_id', $store_info_id)
            ->where('status', env('STATUS_PAYPAL_READY'))
            ->where('id', '<>', $paypal_id)
            ->orderby('id', 'ASC')
            ->first();
        // kết nối với woocomerce store
        $woocommerce = $this->getConnectStore($stores->url, $stores->consumer_key, $stores->consumer_secret);
        // chuẩn bị data thay đổi
        $data = [
//            'settings' => [
//                env('PAYPAL_TYPE').'_username' => $other_paypal->api_email,
//                env('PAYPAL_TYPE').'_password' => $other_paypal->api_pass,
//                env('PAYPAL_TYPE').'_signature' => $other_paypal->api_signature,
//                env('PAYPAL_TYPE').'_client_id' => $other_paypal->api_client_id,
//                env('PAYPAL_TYPE').'_client_secret' => $other_paypal->api_secret
//            ]
            'settings' => [
                'email' => $other_paypal->api_email,
                env('PAYPAL_TYPE').'_api_username' => $other_paypal->api_username,
                env('PAYPAL_TYPE').'_api_password' => $other_paypal->api_pass,
                env('PAYPAL_TYPE').'_api_signature' => $other_paypal->api_signature
            ]
        ];
//        $r1 = $woocommerce->get('payment_gateways');
//        print_r($r1);
        $r = $woocommerce->put('payment_gateways/' . env('PAYPAL_GATEWAVE2'), $data);
        print_r($r);
    }
}
