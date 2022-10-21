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
    $whereSpace = [];
    $temp = 0;
    $counter = 0;
    for ($i = 0; $i < strlen($string); $i++) {
        if (strpos($string, "\n", $i) > $temp) {
            $temp = strpos($string, "\n", $i);
            $whereSpace[$counter] = strpos($string, "\n", $i);
            $counter++;
        }
    }

    $headers = [];

    $method = substr($string, 0, $whereSpace[0]);
    $uri = substr($string, $whereSpace[0], $whereSpace[1]);
    $body = substr($string, $whereSpace[6]);
    echo $method . "  METHOD \n";
    echo $uri . "  URI \n";
    echo $body . " BODY \n";

    return array(
        "method" => $method,
        "uri" => $uri,
//        "headers" => ...,
        "body" => $body
    );
}

$http = parseTcpStringAsHttpRequest($contents);
echo(json_encode($http, JSON_PRETTY_PRINT));
