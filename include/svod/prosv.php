<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 19.10.17
 * Time: 23:06
 */

/***************************************
 * Формирование сводной спецификации для Просвещения
 *
 * Параметры (передаются через POST)
 *    MUN_ID - ID муниципалитета
 *    PERIOD - ID отчетного периода
 *    START_DATE - с какой даты начинать выбирать заказы
 *********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require($_SERVER["DOCUMENT_ROOT"]."/include/report_prosv_new.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

define("LANG", "ru");
define("NO_KEEP_STATISTIC", true);

/* Получаем файл свода */

//Определяем область
$regionID = getRegionFilter();

// Обработка параметра
$munID = intval(trim($_POST["MUN_ID"]));
$period = intval(trim($_POST["PERIOD"]));
$izd = 5;

$startDate = trim($_POST["START_DATE"]);
$startDate = ($startDate == '' ? false : strtotime($startDate));

if (CModule::IncludeModule("iblock")) {
    $templateFile = getSvodTemplate($izd, $period, $regionID);

    $PHPExcel = PHPExcel_IOFactory::load($templateFile);

    $IDCol = 2;
    $PriceCol = 10;
    $CountCol = 15;
    $CostCol = 26;

    $arFilter = Array (
        "IBLOCK_ID" => 9,
        "PROPERTY_STATUS" => "osdocs",
        "PROPERTY_PERIOD" => $period,
        "PROPERTY_REGION" => $regionID,
        "PROPERTY_SCHOOL_ID" => get_schoolID_by_mun($munID),
        "PROPERTY_IZD_ID" =>$izd
    );

    $arSelectedFields = Array (
        "PROPERTY_BOOK",
        "PROPERTY_COUNT",
        "PROPERTY_SCHOOL_ID"
    );

    $orders = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields);

    while ($order = $orders->Fetch()) {
        $arFilter = Array (
            "IBLOCK_ID" => 5,
            "ID" => $order["PROPERTY_BOOK_VALUE"]
        );

        $arSelectedFields = Array (
            "PROPERTY_CODE_1C"
        );

        $code1C = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields)->Fetch();

        $arFilter = Array (
            "IBLOCK_ID" => 34,
            "NAME" => $order["PROPERTY_BOOK_VALUE"],
            "PROPERTY_PERIOD" => $period
        );

        $arSelectedFields = Array (
            "PROPERTY_PRICE"
        );

        $price = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields)->Fetch();

        $info["CODE1C"] = $code1C["PROPERTY_CODE_1C_VALUE"];
        $info["COUNT"] = $order["PROPERTY_COUNT_VALUE"];
        $info["PRICE"] = $price["PROPERTY_PRICE_VALUE"];
        $info["SCHOOL"] = $order["PROPERTY_SCHOOL_ID_VALUE"];

        $data[] = $info;
    }

    unset($info);
    foreach ($data as $datum)
        $infos[$datum["SCHOOL"]][] = $datum;

    $j = 0;
    $k = 2;
    $p = 2;
    foreach ($infos as $school => $orders) {
        $schoolInfo = getSchoolInfo($school);

        //print($munName);
        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        //$sheet->setCellValue("X5", "Сводный заказ по: " . get_obl_name($munID));
        $sheet->setCellValueByColumnAndRow($CountCol + $j, 5, $schoolInfo["INN"]);
        $sheet->setCellValueByColumnAndRow($CountCol + $j, 6, $schoolInfo["NAME"]);

        foreach ($orders as $order) {
            $i =11;
            while ($sheet->getCell("A" . $i)->getValue() != "") {
                if ($sheet->getCellByColumnAndRow($IDCol, $i)->getValue() == $order["CODE1C"]) {
                    $sheet->setCellValueByColumnAndRow($CountCol + $j, $i, $order["COUNT"]);
                }
                $i++;
            }
        }
        $j++;

        $PHPExcel->setActiveSheetIndex(3);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValueByColumnAndRow(0, $p, $schoolInfo["RAJON"]);
        $sheet->setCellValueByColumnAndRow(1, $p, 1);
        $sheet->setCellValueByColumnAndRow(2, $p, $schoolInfo["FULL_NAME"]);
        $sheet->setCellValueByColumnAndRow(3, $p, $schoolInfo["DIR_FIO"] . ", директор, на основании" . $schoolInfo["DIR_DOC"]);
        $sheet->setCellValueByColumnAndRow(4, $p, $schoolInfo["ADDRESS"] . ", Тел.: " . $schoolInfo["PHONE"]
        . ", ИНН/КПП: " . $schoolInfo["INN"] . "/" . $schoolInfo["KPP"] . ", " . $schoolInfo["BANK"] . ", БИК: " . $schoolInfo["BIK"]
        . ", ОКПО: " . $schoolInfo["OKPO"] . ", р/с: " . $schoolInfo["RASCH"] . "л/с: " . $schoolInfo["LS"]);
        $sheet->setCellValueByColumnAndRow(5, $p, $schoolInfo["FULL_NAME"]);
        $sheet->setCellValueByColumnAndRow(6, $p, $schoolInfo["INN"]);
        $sheet->setCellValueByColumnAndRow(7, $p, $schoolInfo["ADDRESS"]);
        $sheet->setCellValueByColumnAndRow(8, $p, $schoolInfo["DIR_FIO"]);
        $sheet->setCellValueByColumnAndRow(9, $p, $schoolInfo["PHONE"] . ", " . $schoolInfo["EMAIL"]);

        $PHPExcel->setActiveSheetIndex(0);

        $p++;
    }

    $tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

    $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
    $objWriter->save($tempFileName . ".XLSX");

    $result = array('file' => basename($tempFileName), 'error' => false);

    print(json_encode($result));
}