<?php

    use App\Goutte\Client;
    use Symfony\Component\DomCrawler\Crawler;

    function tryCatch()
    {
        \DB::beginTransaction();
        try {

            \DB::commit(); // if there was no errors, your query will be executed
        } catch (\Exception $e) {

            $message = 'Xảy ra lỗi ngoài mong muốn: '.$e->getMessage();
            \DB::rollback(); // either it won't execute any statements and rollback your database to previous state
        }
    }

    function logfile($str){
        echo $str."\n";
        Log::channel('custom')->info($str);
    }

    function logfile_system($str){
        echo $str."\n";
        Log::channel('custom')->info($str);
    }

    function dbTime()
    {
        return date("Y-m-d H:i:s");
    }

    function convertTime($str_time)
    {
        return gmdate("H:i:s", $str_time);
    }

    function goUrl2($data, $requestFormat)
    {
        $url = 'https://www.dantri.com.vn';
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', $url);
        $content = $crawler->text();
        return $content;
    }


