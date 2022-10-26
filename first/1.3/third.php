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


function outputHttpResponse($statuscode, $statusmessage, $headers, $body)
{
    $finalMessage = "HTTP/1.1 $statuscode $statusmessage
Server: Apache/2.2.14 (Win32)
Connection: Closed
Content-Type: text/html; charset=utf-8
Content-Length: " . strlen($body) . "

$body";

    /*foreach ($headers as $header) {
        $finalMessage .= "\n";
        foreach ($header as $value) {
            $finalMessage .= $value;
        }
    }
    $finalMessage .= "\n" . $body;*/
    echo $finalMessage;
}

function processHttpRequest($method, $uri, $headers, $body)
{
    if ($method == 'GET' && strpos($uri, '?nums=')) {
        if (isBeginSum($uri)) {
            $statuscode = '200';
            $statusmessage = 'OK';
        } else {
            $statuscode = '400 ';
            $statusmessage = 'not found';
        }
    } else {
        $statuscode = "400 Bad Request";
    }
    $body = sumInUri($uri);
    outputHttpResponse($statuscode, $statusmessage, $headers, $body);
}

function isBeginSum($uri)
{
    $tempArray = explode("?", $uri);
    return $tempArray[0] == "/sum";
}

function sumInUri($uri)
{
    $result = 0;
    $uri = preg_replace("/[^0-9,]/", "", $uri);
    $tempArray = explode(',', $uri);

    foreach ($tempArray as $value) {
        $result += $value;
    }
    return $result;
}


function parseTcpStringAsHttpRequest($string)
{
    $tempArray = explode("\n", $string);
    $firstStringArray = explode(" ", $tempArray[0]);
    $method = $firstStringArray[0];
    $uri = $firstStringArray[1];

    foreach ($tempArray as $value) {
        if (strpos($value, ':')) {
            $tempHeadersArray = explode(': ', $value);
            $headers[] = array($tempHeadersArray[0], $tempHeadersArray[1]);
        }
    }
    $body = $tempArray[sizeof($tempArray) - 1];
    return array(
        "method" => $method,
        "uri" => $uri,
        "headers" => $headers,
        "body" => $body
    );
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);

