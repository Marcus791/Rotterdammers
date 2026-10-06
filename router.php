<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(data|includes|router\.php)(/|$)#', $path)) {
    http_response_code(403);
    exit('Geen toegang.');
}
return false;
