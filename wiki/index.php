<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("WIKI");

// Функция проверки параметров должна быть объявлена перед использованием
function checkAllowedParams($queryString, $allowedParams) {
    parse_str($queryString, $params);
    foreach ($params as $key => $value) {
        if (!isset($allowedParams[$key]) || $allowedParams[$key] != $value) {
            return false;
        }
    }
    return true;
}

// Проверка URL
$parsedUrl = parse_url($_SERVER['REQUEST_URI']);
$requestedPath = $parsedUrl['path'];
$allowedPath = '/wiki';

$normalizedRequested = rtrim($requestedPath, '/');
$normalizedAllowed = rtrim($allowedPath, '/');

$allowedParams = ['clear_cache' => 'Y'];

if ($normalizedRequested !== $normalizedAllowed || 
    (isset($parsedUrl['query']) && !checkAllowedParams($parsedUrl['query'], $allowedParams))) {
    define("ERROR_404", "Y");
    CHTTP::SetStatus("404 Not Found");
    include($_SERVER['DOCUMENT_ROOT'].'/404.php');
    exit();
}

$APPLICATION->IncludeComponent(
    "terentev:cards.list",
    "" 
);

$APPLICATION->IncludeComponent(
    "terentev:card.last.news",
    "", 
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>