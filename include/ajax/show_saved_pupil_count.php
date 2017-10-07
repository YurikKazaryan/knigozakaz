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
    $arFilter = array("IBLOCK_ID" => 37, "NAME" => get_schoolID($USER->GetID()));
    $el = CIBlockElement::GetList(Array("ID" => "DESC"), $arFilter, false, Array("nTopCount" => 1))->Fetch();

    $el = $el["ID"];

    $properties = CIBlockElement::GetProperty(37, $el);

    while ($result = $properties->GetNext())
        $prop[$result["CODE"]] = $result["VALUE"];

    echo json_encode($prop);

}