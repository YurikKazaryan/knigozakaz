<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Добавление учебника в инвентаризацию");
if (!$USER->IsAuthorized()) LocalRedirect('/auth/');
?>

<?
refreshInventoryCatalog();
if (in_array(8, $USER->GetUserGroupArray())) {
    $APPLICATION->IncludeComponent(
        "bav:inventory.add",
        ".default",
        array(
            "CACHE_TIME" => "3600",
            "CACHE_TYPE" => "N",
            "COMPONENT_TEMPLATE" => ".default",
            "MAX_RESULT" => "700",
            "USE_PATH" => "/inventory/"
        ),
        false
    );
}
else {?>
<div class="panel panel-warning">
    <div class="panel panel-heading">Сообщение системы</div>
    <div class="panel panel-body">Инвентаризация доступна только для администраторов школ!</div>
</div>
<?}?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>