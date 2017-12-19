<?php
/********************************************************************
 * Поиск книг для добавления в инвентаризацию
 *
 * Параметры (передаются через POST)
 *    FIND_STR - образец для поиска
 *    MAX_RESULT - максимальное количество найденных книг в результате
 ********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

if (CModule::IncludeModule('iblock')) {
    $arFilter = array(
        "IBLOCK_ID" => 37,
        "NAME" => getSchoolID($USER->GetID()),
        "PROPERTY_272" => "%"
    );

    $arSelectedFilter = array (
        "PROPERTY_272"
    );

    $classes = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFilter);

    while ($class = $classes->Fetch())
        $data[] = substr($class["PROPERTY_272_VALUE"], 0, strpos($class["PROPERTY_272_VALUE"], ":"));

    sort($data);

    echo json_encode($data);
}