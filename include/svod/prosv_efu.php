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
$izd = intval(trim($_POST["IZD"]));

$startDate = trim($_POST["START_DATE"]);
$startDate = ($startDate == '' ? false : strtotime($startDate));

if (CModule::IncludeModule("iblock")) {
    //$templateFile = getSvodTemplate($izd, $period, $regionID);

    $PHPExcel = new PHPExcel();
    $PHPExcel->createSheet();

    $IDCol = 2;
    $PriceCol = 10;
    $CountCol = 15;
    $CostCol = 26;

    $arFilter = Array (
        "IBLOCK_ID" => 9,
        "PROPERTY_STATUS" => "oschecked",
        "PROPERTY_PERIOD" => $period,
        "PROPERTY_REGION" => $regionID,
        "PROPERTY_SCHOOL_ID" => get_schoolID_by_mun($munID),
        "PROPERTY_IZD_ID" => $izd
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
            "PROPERTY_CODE_1C",
            "PROPERTY_AUTHOR",
            "PROPERTY_TITLE",
            "PROPERTY_CLASS",
        );

        $book = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields)->Fetch();

        $arFilter = Array (
            "IBLOCK_ID" => 34,
            "NAME" => $order["PROPERTY_BOOK_VALUE"],
            "PROPERTY_PERIOD" => $period
        );

        $arSelectedFields = Array (
            "PROPERTY_PRICE"
        );

        $price = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields)->Fetch();

        //$info["CODE1C"] = $code1C["PROPERTY_CODE_1C_VALUE"];
        $info["COUNT"] = $order["PROPERTY_COUNT_VALUE"];
        $info["AUTHOR"] = $book["PROPERTY_AUTHOR_VALUE"];
        $info["TITLE"] = $book["PROPERTY_TITLE_VALUE"];
        $info["CLASS"] = $book["PROPERTY_CLASS_VALUE"];
        $info["PRICE"] = $price["PROPERTY_PRICE_VALUE"];
        $info["SCHOOL"] = $order["PROPERTY_SCHOOL_ID_VALUE"];

        $data[] = $info;
    }

    unset($info);
    foreach ($data as $datum)
        $infos[$datum["SCHOOL"]][] = $datum;

    $j = 2;
    $p = 2;

    $PHPExcel->setActiveSheetIndex(1);
    $sheet = $PHPExcel->getActiveSheet();

    $sheet->setCellValueByColumnAndRow(0, 1, "Район")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(1, 1, "Кол-во заказчиков")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(2, 1, "Название организации")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(3, 1, "ФИО, должность, основание")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(4, 1, "Полный адрес, телефон, реквизиты")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(5, 1, "ИНН")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(6, 1, "Полный адрес")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(7, 1, "ФИО директора")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(8, 1, "Телефон, email")->getColumnDimension()->setAutoSize(true);

    $PHPExcel->setActiveSheetIndex(0);
    $sheet = $PHPExcel->getActiveSheet();

    $sheet->setCellValueByColumnAndRow(0, 1, "Автор")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(1, 1, "Название учебника")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(2, 1, "Класс")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(3, 1, "Цена")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(4, 1, "Количество")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(5, 1, "Сумма")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(6, 1, "Организация")->getColumnDimension()->setAutoSize(true);
    $sheet->setCellValueByColumnAndRow(7, 1, "ИНН")->getColumnDimension()->setAutoSize(true);

    foreach ($infos as $school => $orders) {
        $schoolInfo = getSchoolInfo($school);

        //print($munName);
        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        foreach ($orders as $order) {
            $sheet->setCellValueByColumnAndRow(0, $j, $order["AUTHOR"])->getColumnDimension()->setAutoSize(true);
            $sheet->setCellValueByColumnAndRow(1, $j, $order["TITLE"])->getColumnDimension()->setAutoSize(true);
            $sheet->setCellValueByColumnAndRow(2, $j, $order["CLASS"])->getColumnDimension()->setAutoSize(true);
            $sheet->setCellValueByColumnAndRow(3, $j, $order["PRICE"])->getColumnDimension()->setAutoSize(true);
            $sheet->setCellValueByColumnAndRow(4, $j, $order["COUNT"])->getColumnDimension()->setAutoSize(true);
            $sheet->setCellValueByColumnAndRow(5, $j, $order["COUNT"] * $order["PRICE"])->getColumnDimension()->setAutoSize(true);
            $sheet->setCellValueByColumnAndRow(6, $j, $schoolInfo["FULL_NAME"])->getColumnDimension()->setAutoSize(true);
            $sheet->getStyle("H" . $j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $sheet->setCellValueByColumnAndRow(7, $j, $schoolInfo["INN"])->getColumnDimension()->setAutoSize(true);
            $j++;
        }

        $PHPExcel->setActiveSheetIndex(1);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValueByColumnAndRow(0, $p, $schoolInfo["RAJON"])->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(1, $p, 1)->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(2, $p, $schoolInfo["FULL_NAME"])->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(3, $p, $schoolInfo["DIR_FIO"] . ", директор, на основании " . $schoolInfo["DIR_DOC"])->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(4, $p, $schoolInfo["ADDRESS"] . ", Тел.: " . $schoolInfo["PHONE"]
            . ", ИНН/КПП: " . $schoolInfo["INN"] . "/" . $schoolInfo["KPP"] . ", " . $schoolInfo["BANK"] . ", БИК: " . $schoolInfo["BIK"]
            . ", ОКПО: " . $schoolInfo["OKPO"] . ", р/с: " . $schoolInfo["RASCH"] . "л/с: " . $schoolInfo["LS"])->getColumnDimension()->setAutoSize(true);
        //$sheet->setCellValueByColumnAndRow(5, $p, $schoolInfo["FULL_NAME"]);
        $sheet->getStyle("F" . $p)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
        $sheet->setCellValueByColumnAndRow(5, $p, $schoolInfo["INN"])->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(6, $p, $schoolInfo["ADDRESS"])->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(7, $p, $schoolInfo["DIR_FIO"])->getColumnDimension()->setAutoSize(true);
        $sheet->setCellValueByColumnAndRow(8, $p, $schoolInfo["PHONE"] . ", " . $schoolInfo["EMAIL"])->getColumnDimension()->setAutoSize(true);

        $PHPExcel->setActiveSheetIndex(0);

        $p++;
    }

    $tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

    $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
    $objWriter->save($tempFileName . ".XLSX");

    $result = array('file' => basename($tempFileName), 'error' => false);

    print(json_encode($result));
}