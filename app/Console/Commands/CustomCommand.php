<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\BaseCommand as BaseCommand;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\WooController;
use App\Http\Controllers\PaypalController;

class CustomCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:custom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $array_minute = [ 57, 7, 3, 1];
//    protected $array_minute = [1];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minute = date('i');
        $array_minute = $this->array_minute;
        for ($i = 0; $i < sizeof($array_minute); $i++)
        {
            $min = 'time';
            $minute_compare = $array_minute[$i];
            if (($minute % $minute_compare) == 0)
            {
                $min = $minute_compare;
                logfile_system("\n".'===================== Hàm đang chạy bởi '.$min." phut ======================= \n");
                break;
            }
        }
        $this->runCommand($min);
    }

    protected function runCommand($minute)
    {
        switch ($minute) {
            case 1:
//                $this->run1MinuteTest();
                $this->run1Minute();
                break;
            case 3:
                $this->run3Minute();
                break;
            case 7:
                $this->run7Minute();
                break;
//            case 19:
//                $this->run19Minute();
//                break;
            case 57:
                $this->run57Minute();
                break;
            case 58:
            case 59:
                $this->run0Minute();
                break;
            default:
                echo 'khong run duoc vao thoi gian nay '. $minute;
                break;
        }
    }

    protected function run1MinuteTest()
    {
//        $woo_controller = new WooController();
//        $woo_controller->test();
        echo 'aa';
    }

    protected function run0Minute()
    {

    }

    protected function run1Minute()
    {
        $scraper_controller = new ScraperController();
        $woo_controller = new WooController();
        $check = $woo_controller->createProductWoo(); // tạo sản phẩm woo. Trả về false nếu đang tạo và true nếu không tạo
        if ($check) {
            $check = $scraper_controller->sendListProduct(); // lấy thông tin ảnh và title của sản phẩm
        }
    }

    protected function run3Minute()
    {
        $scraper_controller = new ScraperController();
        $woo_controller = new WooController();
        $check = $scraper_controller->sendListProduct(); // lấy thông tin ảnh và title của sản phẩm
        if ($check) {
            $check = $woo_controller->createImageProductWoo(); // tạo sản phẩm woo. Trả về false nếu đang tạo và true nếu không tạo
        }
    }

    protected function run7Minute()
    {
        $woo_controller = new WooController();
        $check = $woo_controller->createImageProductWoo(); // tạo sản phẩm woo. Trả về false nếu đang tạo và true nếu không tạo
    }

    protected function run19Minute()
    {

    }

    protected function run57Minute()
    {
        $scraper_controller = new ScraperController();
        $check = $scraper_controller->getWebScrap(); // bắt đầu cào từ web để lấy product list
    }

    private function test1()
    {
        echo "<pre>\n";
        $scraper_controller = new ScraperController();
        $woo_controller = new WooController();
//        $check = $scraper_controller->getWebScrap(); // bắt đầu cào từ web để lấy product list
//        $check = $scraper_controller->sendListProduct(); // lấy thông tin ảnh và title của sản phẩm
//        $check = $woo_controller->createProductWoo(); // tạo sản phẩm woo. Trả về false nếu đang tạo và true nếu không tạo
        $check = $woo_controller->createImageProductWoo(); // tạo sản phẩm woo. Trả về false nếu đang tạo và true nếu không tạo
        var_dump($check);
    }
}
