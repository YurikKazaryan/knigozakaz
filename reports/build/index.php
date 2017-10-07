<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Конструктор отчётов 1");
if (!$USER->IsAuthorized())
    LocalRedirect('/auth/');

$APPLICATION->IncludeComponent(
    "bav:reports.build",
    ".default",
    array(
        "CACHE_TIME" => "3600",
        "CACHE_TYPE" => "N",
        "COMPONENT_TEMPLATE" => ".default",
        "MAX_RESULT" => "700",
        "USE_PATH" => "/reports/"
    ),
    false
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>