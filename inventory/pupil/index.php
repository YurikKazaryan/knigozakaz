<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 20.08.17
 * Time: 22:26
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Количество учеников");

if (!$USER->IsAuthorized()) LocalRedirect('/auth/');
?>

<?if (in_array(8, $USER->GetUserGroupArray())) {
    $APPLICATION->IncludeComponent(
        "bav:pupil.add",
        ".default",
        Array(
            "CACHE_TIME" => "3600",
            "CACHE_TYPE" => "N",
            "MAX_RESULT" => "11",
            "USE_PATH" => "/inventory/",
            "COMPONENT_TEMPLATE" => ".default",
        ),
        false
    );
} else {
?>
    <div class="panel panel-warning">
        <div class="panel panel-heading">Сообщение системы</div>
        <div class="panel panel-body">Информация об учащихся доступна только администраторам школ!</div>
    </div>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>