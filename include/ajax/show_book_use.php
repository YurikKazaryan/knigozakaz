<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 16.09.17
 * Time: 17:54
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

if (CModule::IncludeModule("iblock")) {
    $bookId = $_POST["BOOKID"];

    //Ищем учебник в заказах

    $arFilter = array (
        "IBLOCK_ID" => 9,
        "PROPERTY_BOOK" => $bookId,
        //"PROPERTY_STATUS" => ["osclosed", "osrecieved", "oschecked", "oscheck"]
    );

    $arSelectedFields = array(
        "PROPERTY_SCHOOL_ID",
        "PROPERTY_COUNT"
    );

    $schools = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields);

    while ($school = $schools->Fetch()) {
        $schoolInfo = getSchoolInfo($school["PROPERTY_SCHOOL_ID_VALUE"]);
        $schoolInfo["BOOK_COUNT"] = $school["PROPERTY_COUNT_VALUE"];
        $data[$school["PROPERTY_SCHOOL_ID_VALUE"]] = $schoolInfo;
    }

    foreach ($data as $schoolId => $info) {
        $response[$schoolId]["RAION"] = getRegionName(getMunIdBySchoolId($schoolId));
        $response[$schoolId]["FULL_NAME"] = $info["FULL_NAME"];
        $response[$schoolId]["ADDRESS"] = $info["ADDRESS"];
        $response[$schoolId]["DIR_FIO"] = $info["DIR_FIO"];
        $response[$schoolId]["OTV_FIO"] = $info["OTV_FIO"];
        $response[$schoolId]["PHONE"] = $info["PHONE"];
        $response[$schoolId]["EMAIL"] = $info["EMAIL"];
        $response[$schoolId]["ORDER_COUNT"] = $info["BOOK_COUNT"];
    }
    echo json_encode($response);
}