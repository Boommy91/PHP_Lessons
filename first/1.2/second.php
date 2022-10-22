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

    $tempNum = 0;
    $paragraphPlaceArray = array();

    for ($i = 0; $i < strlen($string); $i++) {
        if (strpos($string, "\n", $i) > $tempNum) {

            $paragraphPlaceArray[] = strpos($string, "\n", $i);
            $tempNum = strpos($string, "\n", $i);
        }
    }
    foreach ($paragraphPlaceArray as $item) {
        echo $item . "  ";
    }
    $method = substr($string, 0, $paragraphPlaceArray[0]);
    // get method
    $uri = null;
    if (str_contains($method, "POST")) {
        $uri = str_replace("POST ", "", $method);
        $method = "POST";
    } else {
        $uri = str_replace("GET ", "", $method);
        $method = "GET";
    }
    $body = "bookId=12345&author=Tan+Ah+Teck";
    var_dump($uri);

    return array(
        "method" => $method,
        "uri" => "/doc/test HTTP/1.1",
//        "headers" => $headers,
        "body" => $body
    );
}

$http = parseTcpStringAsHttpRequest($contents);
echo(json_encode($http, JSON_PRETTY_PRINT));
