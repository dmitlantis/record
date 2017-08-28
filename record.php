<?php

preg_match_all('/href="([^"]+.mp3)/i', http('http://www.radiorecord.ru/radio/top100/rr.txt'), $files);
set_time_limit(0);

$dir = rtrim($_GET['dir'] ?? $argv[1] ?? __DIR__, '\\\/') . DIRECTORY_SEPARATOR;
$eol = !empty($argv) ? PHP_EOL : '<br>';
$dirFiles = scandir($dir);
$trackNames = [];

foreach ($dirFiles as $dirFile) {
    if ($dirFile != '.' && $dirFile != '..') {
        $trackNames[mb_substr($dirFile, 4)] = mb_substr($dirFile, 0 ,4);
    }
}

if(!empty($files)) {
    $skipped = '';
    foreach ($files[1] as $file) {
        $file = trim($file);
        $basename = urldecode(basename($file));
        $path = $dir . $basename;
        $trackName = mb_substr($basename, 4);
        $newPosition = mb_substr($basename, 0, 4);
        if (!isset($trackNames[$trackName])) {
            $result = http($file,'http://www.radiorecord.ru/radio/stations/?st=rr', $path);
            echo $basename . $eol;
            if (filesize($path) < 1000) {
                exit(file_get_contents($path));
            }
            sleep(rand(.4, 1.5));
        } elseif ($trackNames[$trackName] != $newPosition) {
            rename($dir . $trackNames[$trackName] . $trackName, $path);
            echo "$trackName: $trackNames[$trackName] -> $newPosition $eol";
        } else {
            $skipped .= $basename . $eol;
        }
    }
    echo $eol . 'Skipped:' . $eol . $skipped;
}


function http($url, $referrer = '', $path = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HEADER, false);
    if (!empty($referrer)) {
        curl_setopt($ch, CURLOPT_REFERER, $referrer);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
    curl_setopt($ch, CURLOPT_URL, $url);
    if (!empty($path)) {
        $fp = fopen($path, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
    } else {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    if (!empty($fp)) {
        fclose($fp);
    }
    return $result;
}