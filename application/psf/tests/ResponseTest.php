<?php

require_once "../bootstrap.php";

// Chrome Developer Tools の Networkタブを選択後リロードして
// Name欄からこのファイルを選択すればHTTP Headerは確認できる
// Safariでも同様にNetworkタブを利用する

$response = new \psf\core\Response();
$response->setStatusCode(404, 'Not Found');
$response->setHttpHeader("Test-Header", "Test-Value");
$response->setContent("404 Not Found.");
$response->send();

