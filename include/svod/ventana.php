<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 11.10.17
 * Time: 19:34
 */

/***************************************
 * Формирование сводной спецификации для Вентана-Граф
 *
 * Параметры (передаются через POST)
 *    MUN_ID - ID муниципалитета
 *********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require($_SERVER["DOCUMENT_ROOT"]."/include/report_ventana.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/report_prosv.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define("LANG", "ru");
define("NO_KEEP_STATISTIC", true);

/* Получаем файл свода */

//Определяем область
$regionID = getRegionFilter();

// Обработка параметра
$munID = intval(trim($_POST["MUN_ID"]));
$period = intval(trim($_POST["PERIOD"]));
$izd = 108;

$startDate = trim($_POST["START_DATE"]);
$startDate = ($startDate == '' ? false : strtotime($startDate));

if (CModule::IncludeModule("iblock")) {
    $templateFile = getSvodTemplate($izd, $period, $regionID);

    $PHPExcel = PHPExcel_IOFactory::load($templateFile);

    $IDCol = 2;
    $PriceCol = 10;
    $CountCol = 13;
    $CostCol = 14;

    $arFilter = Array (
        "IBLOCK_ID" => 9,
        "PROPERTY_STATUS" => "oschecked",
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
    foreach ($infos as $school => $orders) {
        $schoolInfo = getSchoolInfo($school);

        print($munName);
        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $sheet->setCellValue("L5", "Сводный заказ по: " . get_obl_name($munID));
        $sheet->setCellValueByColumnAndRow($CountCol + $j * 2, 5, $schoolInfo["NAME"] . ", " . $schoolInfo["INN"]);

        foreach ($orders as $order) {
            $i = 7;
            while ($sheet->getCell("A" . $i)->getValue() != "") {
                if ($sheet->getCellByColumnAndRow($IDCol, $i)->getValue() == $order["CODE1C"]) {
                    $sheet->setCellValueByColumnAndRow($CountCol + $j * 2, $i, $order["COUNT"]);
                }
                $i++;
            }
        }
        $j++;

        $PHPExcel->setActiveSheetIndex(1);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue("B" . $k, $schoolInfo["FULL_NAME"]);
        $sheet->setCellValue("B" . ($k + 1), "ЮрЛицо");
        $sheet->setCellValue("B" . ($k + 4), $schoolInfo["INN"]);
        $sheet->setCellValue("B" . ($k + 5), $schoolInfo["KPP"]);
        $sheet->setCellValue("B" . ($k + 6), $schoolInfo["OKPO"]);
        $sheet->setCellValue("B" . ($k + 8), $schoolInfo["ADDRESS"]);
        $sheet->setCellValue("B" . ($k + 9), $schoolInfo["ADDRESS"]);
        $sheet->setCellValue("B" . ($k + 10), $schoolInfo["ADDRESS"]);
        $sheet->setCellValue("B" . ($k + 11), $schoolInfo["PHONE"]);
        $sheet->setCellValue("B" . ($k + 13), $schoolInfo["EMAIL"]);
        $sheet->setCellValue("B" . ($k + 15), $schoolInfo["DIR_FIO"]);
        $sheet->setCellValue("B" . ($k + 16), $schoolInfo["BIK"]);
        $sheet->setCellValue("B" . ($k + 17), $schoolInfo["RASCH"]);
        $sheet->setCellValue("B" . ($k + 18), $schoolInfo["LS"]);
        $sheet->setCellValue("B" . ($k + 19), $schoolInfo["BANK"]);

        $k += 23;

        $PHPExcel->setActiveSheetIndex(0);
    }

    $tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

    $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
    $objWriter->save($tempFileName . ".XLSX");

    $result = array('file' => basename($tempFileName), 'error' => false);

    print(json_encode($result));
}