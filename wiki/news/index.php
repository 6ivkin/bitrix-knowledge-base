<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("WIKI");?><?$APPLICATION->IncludeComponent(
	"terentev:card.news",
	""
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>