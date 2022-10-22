<?php

// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
function readHttpLikeInput()
{
    $f = fopen('php://stdin', 'r');
    $store = "";
    $toread = 0;
    while ($line = fgets($f)) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/', $line, $m))
            $toread = $m[1] * 1;
        if ($line == "\r\n")
            break;
    }
    if ($toread > 0)
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function parseTcpStringAsHttpRequest($string)
{
    if (strpos($string, "POST")) {
        echo "YES";
    }

    return null; /*array(
        "method" => $method,
        "uri" => $uri,
//        "headers" => ...,
        "body" => $body
    );*/
}

$http = parseTcpStringAsHttpRequest($contents);
echo(json_encode($http, JSON_PRETTY_PRINT));
