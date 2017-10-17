<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Загрузка прайсов");
if (!$USER->IsAdmin()) LocalRedirect('/');
?><?$APPLICATION->IncludeComponent(
	"bav:price.load", 
	".default", 
	array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "N",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>