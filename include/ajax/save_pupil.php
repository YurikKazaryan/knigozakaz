<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 22.08.17
 * Time: 0:05
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$K1 = trim($_POST["K1"]);
$K2 = trim($_POST["K2"]);
$K3 = trim($_POST["K3"]);
$K4 = trim($_POST["K4"]);
$K5 = trim($_POST["K5"]);
$K6 = trim($_POST["K6"]);
$K7 = trim($_POST["K7"]);
$K8 = trim($_POST["K8"]);
$K9 = trim($_POST["K9"]);
$K10 = trim($_POST["K10"]);
$K11 = trim($_POST["K11"]);

$result = array(
    "error" => 1,
    "id" => 0,
    "error_text" => ""
);

if (CModule::IncludeModule("iblock")) {
    $arNew = array(
        "MODIFIED_BY" => $USER->GetID(),
        "IBLOCK_SECTION_ID" => false,
        "IBLOCK_ID" => 37,
        "NAME" => get_schoolID($USER->GetID()),
        "ACTIVE" => "Y",
        "PROPERTY_VALUES" => array(
            "K1" => $K1,
            "K2" => $K2,
            "K3" => $K3,
            "K4" => $K4,
            "K5" => $K5,
            "K6" => $K6,
            "K7" => $K7,
            "K8" => $K8,
            "K9" => $K9,
            "K10" => $K10,
            "K11" => $K11,
        )
    );

    $el = new CIBlockElement;
    $newID = $el->Add($arNew);

    if ($newID) {
        $result["id"] = $newID;
        $result["error"] = 1;
    } else {
        $result["error"] = 0;
        $result["error_text"] = "Ошибка сохранения учеников! " . $el->LAST_ERROR;
    }

}

echo json_encode($result);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");