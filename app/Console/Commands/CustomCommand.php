<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScraperController;

class CustomCommand extends Command
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
                logfile_system('+++++++++++++++++++++++ Hàm đang chạy bởi '.$min." phut \n");
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
        $scraper_controller = new ScraperController(); // make sure to import the controller
        $check0 = $scraper_controller->scrapProduct();
    }

    protected function run4Minute()
    {

    }

    protected function run7Minute()
    {

    }

    protected function run19Minute()
    {

    }

    protected function run57Minute()
    {

    }

    protected function run0Minute()
    {

    }
}
