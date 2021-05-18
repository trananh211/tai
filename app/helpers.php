<?php
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
