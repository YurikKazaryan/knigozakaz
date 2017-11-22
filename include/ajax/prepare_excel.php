<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 27.09.17
 * Time: 0:24
 */

require($_SERVER["DOCUMENT_ROOT"] . "/include/PHPExcel/PHPExcel.php");

$data = $_POST["data"];

$scheme_name = $_SERVER["DOCUMENT_ROOT"] . "/inventory/reports/scheme/mscheme.xlsx";

$reportFile = PHPExcel_IOFactory::load($scheme_name);
$reportFile->setActiveSheetIndex(0);

$startRow = 4;

foreach ($data as $key => $row) {
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, $row["PROPERTY_MUNICIPALITY"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(1, $startRow, $row["PROPERTY_SCHOOL"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(2, $startRow, $row["PROPERTY_IZD"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(3, $startRow, $row["PROPERTY_BOOK_NAME"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, $row["PROPERTY_BOOK_AUTHOR"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(5, $startRow, $row["PROPERTY_SUBJECT"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(6, $startRow, $row["PROPERTY_CLASS"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(7, $startRow, $row["PROPERTY_BOOK_PRICE"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(8, $startRow, $row["PROPERTY_BOOK_COUNT"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(9, $startRow, $row["PROPERTY_PUPIL_COUNT"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(10, $startRow, $row["PROPERTY_BOOK_WRITEOFF"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(11, $startRow, $row["PROPERTY_KOEF"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(12, $startRow, $row["PROPERTY_BOOK_NEED"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(13, $startRow, $row["PROPERTY_COST"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(14, $startRow, $row["PROPERTY_COST_10"]);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(15, $startRow, $row["PROPERTY_USE_ORDERS"]);

    $startRow ++;
}

$objWriter = new PHPExcel_Writer_Excel2007($reportFile);
$uid = uniqid();
$baseName = $_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/" . $uid;

$objWriter->save($baseName . ".XLSX");

echo json_encode(basename($baseName));