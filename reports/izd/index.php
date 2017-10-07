<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Своды для издательств");
if (!CSite::InGroup(array(1,6,7,9))) LocalRedirect('/auth/');
?>

<?$APPLICATION->IncludeComponent(
	"bav:svod.izd",
	"",
	Array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>