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
}
