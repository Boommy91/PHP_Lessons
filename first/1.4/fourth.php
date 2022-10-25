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
    $finalMessage = "HTTP/1.1 $statuscode
Server: Apache/2.2.14 (Win32)
Connection: Closed
Content-Type: text/html; charset=utf-8
Content-Length: " . strlen($body) . "
$statusmessage
";

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
            $statuscode = '200 OK';
            $statusmessage = sumInUri($uri);
        } else {
            $statuscode = '400 Not Found';
            $statusmessage = 'not found';
        }
    } else {
        $statuscode = "400 Bad Request";
    }
    outputHttpResponse($statuscode, $statusmessage, $headers, $body);
}

function isBeginSum($uri)
{
    $tempArray = explode("?", $uri);
    if ($tempArray[0] == "/sum") {
        return true;
    }
    return false;
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

