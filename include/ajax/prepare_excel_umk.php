<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 27.09.17
 * Time: 0:24
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

require($_SERVER["DOCUMENT_ROOT"] . "/include/PHPExcel/PHPExcel.php");


$data = $_POST["data"];

$scheme_name = $_SERVER["DOCUMENT_ROOT"] . "/inventory/reports/scheme/umk.xlsx";

$reportFile = PHPExcel_IOFactory::load($scheme_name);
$reportFile->setActiveSheetIndex(0);

$text = "Учебник: " . $_POST["book"] . " (Изд. " . get_izd_name($_POST["izd"]) . ") используют следующие школы";

$reportFile->getActiveSheet()->setCellValueByColumnAndRow(0,1, $text);
$startRow = 3;

foreach ($data as $key => $row) {
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, $row["RAION"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(1, $startRow, $row["FULL_NAME"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(2, $startRow, $row["ADDRESS"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(3, $startRow, $row["DIR_FIO"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, $row["OTV_FIO"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(5, $startRow, $row["PHONE"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(6, $startRow, $row["EMAIL"]);

    $startRow ++;
}

$objWriter = new PHPExcel_Writer_Excel2007($reportFile);
$uid = uniqid();
$baseName = $_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/" . $uid;

$objWriter->save($baseName . ".XLSX");

echo json_encode(basename($baseName));