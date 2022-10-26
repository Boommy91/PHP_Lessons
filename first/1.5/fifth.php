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
    $finalMessage = "GET / HTTP/1.1
Host: student.shpp.me
Accept: image/gif, image/jpeg, */*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0";
    echo $finalMessage;
}

function processHttpRequest($method, $uri, $headers, $body)
{
    $readFilePath = $uri;
    if (file_exists($readFilePath)) {
        $statuscode = '200';
        $statusmessage = 'OK';
        $textFileRead = file_get_contents($readFilePath);
        $tempArr = explode('/', $readFilePath);
        $fileName = $tempArr[sizeof($tempArr) - 1];

        foreach ($headers as $header) {
            if (strpos('Host', $header[0]) !== false) {
                if (strpos('student.shpp.me', $header[1]) !== false) {
                    file_put_contents('student/' . $fileName, $textFileRead);
                } elseif (strpos('another.shpp.me', $header[1]) !== false) {
                    file_put_contents('another/' . $fileName, $textFileRead);
                } else {
                    $statuscode = '404';
                    $statusmessage = 'Not Found';
                }
            }
        }

    } else {
        $statuscode = '404';
        $statusmessage = 'Not Found';
    }

    outputHttpResponse($statuscode, $statusmessage, $headers, $body);
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

