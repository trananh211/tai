<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7;

class BaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
    }

    /*
     *  Send data to URL
     * data [
     *      'url' => link,
     *      'data' => array
     * ]
     * */
    public function goUrl($data) {
        $result = false;
        $message = '';
        $url = $data['url'];
        $parameters = $data['data'];
        $header = [
            'Content-Type' => 'application/json',
            env('HEADER_VP6_KEY') => env('HEADER_VP6_VALUE')
        ];
        $client = new Client([
            // Base URI is used with relative requests
//            'base_uri' => 'http://httpbin.org',
            'headers' => $header,
            // You can set any number of default request options.
//            'timeout'  => 2.0,
            'body' => json_encode($parameters, true),
        ]);

        try {
            $crawler = $client->request('POST', $url, []);
            $status = $crawler->getStatusCode();
            if ($status == 200)
            {
                $body = $crawler->getBody();
                $content = $body->getContents();
                $result = json_decode($content, true);
                if (is_array($result) && $result['result'] == 1)
                {
                    $return = true;
                    $message = 'Đã nhận phản hồi thành công';
                } else {
                    $message = 'Phản hồi không đúng quy ước. Xảy ra lỗi';
                }
            }
        } catch (\Exception $e) {
            $message = 'Server không phản hồi theo luồng. Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
        }
        $data = [
            'return' => $result,
            'message' => $message
        ];
        return $data;
    }

    protected function getDataFromUrl()
    {
        $client = new Client([
            // Base URI is used with relative requests
//            'base_uri' => 'http://httpbin.org',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
//        $url = 'https://www.dantri.com.vn';
        $url = 'http://localhost:3000/xin-chao-2';
        $crawler = $client->request('GET', $url);
        $body = $crawler->getBody();
        $content = $body->getContents();
        return $content;
    }
}
