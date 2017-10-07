<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Реформирование спецификаций");
if (!CSite::InGroup(array(1,6,9))) LocalRedirect('/auth/');
?><?$APPLICATION->IncludeComponent(
	"bav:reform.spec",
	"",
	Array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "N",
		"ERROR_LOG" => "Y"
	)
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>