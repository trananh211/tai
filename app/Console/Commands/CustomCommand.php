<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\BaseCommand as BaseCommand;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\WooController;

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

//    protected $array_minute = [ 57, 7, 4, 1];
    protected $array_minute = [1];

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
                $this->run1Minute();
                break;
            case 4:
                $this->run4Minute();
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

    protected function run1Minute()
    {
//        $check0 = $this->sendDataClawer();
        $check0 = $this->test1();
    }

    protected function run4Minute()
    {
        $scraper_controller = new ScraperController();
        $check = $scraper_controller->sendListProduct(); // lấy thông tin ảnh và title của sản phẩm
    }

    protected function run7Minute()
    {

    }

    protected function run19Minute()
    {

    }

    protected function run57Minute()
    {
        $scraper_controller = new ScraperController();
        $check = $scraper_controller->getWebScrap(); // bắt đầu cào từ web để lấy product list
    }

    /*Send data to server clawer*/
    private function sendDataClawer()
    {
        $scraper_controller = new ScraperController(); // make sure to import the controller
        $data = $scraper_controller->scrapProduct();
        if ($data['return'] == 1)
        {
            $result = $this->goUrl($data);
            $save_data = [
                'result' => $result,
                'data' => $data
            ];
            $return = $scraper_controller->saveProductRunning($save_data);
        } else {
            $return = 1;
        }
        logfile_system($data['message']);
        return $return;
    }

    private function test1()
    {
        echo "<pre>\n";
        $scraper_controller = new ScraperController();
        $woo_controller = new WooController();
//        $check = $scraper_controller->getWebScrap(); // bắt đầu cào từ web để lấy product list
//        $check = $scraper_controller->sendListProduct(); // lấy thông tin ảnh và title của sản phẩm
        $check = $woo_controller->createProductWoo(); // tạo sản phẩm woo
        var_dump($check);
    }
}
