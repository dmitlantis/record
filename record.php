<?php

preg_match_all('/href="([^"]+.mp3)/i', file_get_contents('http://www.radiorecord.ru/radio/top100/rr.txt'), $files);
set_time_limit(0);

$dir = $_GET['dir'] ?? $argv[1] ?? __DIR__;

if(!empty($files)) {
    foreach ($files[1] as $file) {
        $file = trim($file);
        $path = $dir . '/' . substr(str_replace('%20', ' ', basename($file)),4);
        if (!file_exists($path)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.radiorecord.ru/radio/stations/?st=rr');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
            curl_setopt($ch, CURLOPT_URL, $file);
            $fp = fopen($path, 'w');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $result = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            echo $file . '<br>' . PHP_EOL;
            if (filesize($path) < 1000) {
                exit(file_get_contents($path));
            }
            sleep(rand(.4, 1.5));
        }
    }
}
