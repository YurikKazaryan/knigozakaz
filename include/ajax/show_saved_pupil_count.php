<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 22.08.17
 * Time: 0:51
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

if (CModule::IncludeModule("iblock")) {
    $arFilter = array(
        "IBLOCK_ID" => 37,
        "NAME" => get_schoolID($USER->GetID()),
        "PROPERTY_272" => "%"
    );

    $arSelectedFields = array(
        "ID",
        "PROPERTY_272"
    );

    $countInfos = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields);

    while ($countInfo = $countInfos->Fetch())
        $prop[$countInfo["ID"]] = $countInfo["PROPERTY_272_VALUE"];

    echo json_encode($prop);

}