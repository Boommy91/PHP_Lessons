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


function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
    ...
    echo ...;
}

function processHttpRequest($method, $uri, $headers, $body) {
    ...
    outputHttpResponse(...);
}


function parseTcpStringAsHttpRequest($string)
{

    $tempArray = explode("\n", $string);
    $method = $tempArray[0];

    if (str_contains("POST", $method) == false) {
        $uri = str_replace("POST ", "", $method);
        $method = "POST";
    } else {
        $uri = str_replace("GET ", "", $method);
        $method = "GET";
    }

    for ($i = 1; $i < sizeof($tempArray) - 2; $i++) {
        $tempHeadersArray = explode(': ', $tempArray[$i]);
        $headers[$i - 1] = array($tempHeadersArray[0], $tempHeadersArray[1]);
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
echo(json_encode($http, JSON_PRETTY_PRINT));
