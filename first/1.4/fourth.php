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
Content-Length: " . strlen($body) . "
Connection: Closed
Content-Type: text/html; charset=utf-8

$body";
    echo $finalMessage;
}

function processHttpRequest($method, $uri, $headers, $body)
{
    $fileName = 'password.txt';

    $tempArr = explode("&", $body);
    $loginArr = explode("=", $tempArr[0]);
    $passArr = explode("=", $tempArr[1]);
    if ($method == 'POST' && str_contains($uri, "checkLoginAndPassword")) {
        $statusmessage = 'OK';
    } else {
        $statusmessage = 'Not Found';
    }
    if (file_exists($fileName)) {
        $file = file_get_contents($fileName);
        if (str_contains($file, $loginArr[1]) && str_contains($file, $passArr[1])) {
            $statuscode = '200';
            $body = '<h1 style="color:green">FOUND</h1>';
        } else {
            $statuscode = '404';
            $statusmessage = 'Not Found';
            $body = '<h1 style="color:red">NOT FOUND</h1>';
        }
    } else {
        $statuscode = '500';
        $statusmessage = 'Internal Server Error';
    }


    outputHttpResponse($statuscode, $statusmessage, $headers, $body);
}


function sumInUri($uri)
{
    $result = 0;
    $uri = str_replace('=', ',', $uri);
    $uri = str_replace(' ', ',', $uri);
    $tempArray = explode(',', $uri);
    for ($i = 1; $i < sizeof($tempArray); $i++) {
        $result += $tempArray[$i];
    }
    return $result;
}


function parseTcpStringAsHttpRequest($string)
{
    $tempArray = explode("\n", $string);
    $firstStringArray = explode(" ", $tempArray[0]);
    $method = $firstStringArray[0];
    $uri = $firstStringArray[1];

    for ($i = 1; $i < sizeof($tempArray) - 1; $i++) {
        if (strlen($tempArray[$i] > 0)) {
            $tempHeadersArray = explode(': ', $tempArray[$i]);
            $headers[$i - 1] = array($tempHeadersArray[0], $tempHeadersArray[1]);
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

