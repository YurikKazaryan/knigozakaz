<?
/***************************************
* Формирование рееестра заказов с разбивкой по школам
*
* Параметры (передаются через POST)
*    PERIOD - ID периода для отчета
*    IZD - издательство
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта

require_once($_SERVER["DOCUMENT_ROOT"]."/include/report_reestr_by_school.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

//Определяем область
$regionID = getRegionFilter();

// Обработка параметра
$period = intval(trim($_POST['PERIOD']));
$izdID = intval(trim($_POST['IZD']));

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'reestr_by_school.xlsx';

if ($USER->IsAuthorized() && $period && $izdID && CModule::IncludeModule('iblock')) {

	if (file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		// Формируем массив с отчетом
		$arResult = report_reestr_by_school($regionID, $period, $izdID);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);
		$activeSheet = $objPHPExcel->getActiveSheet();

		$activeSheet->
			setCellValueByColumnAndRow(0, 2, get_izd_name($izdID))->
			setCellValueByColumnAndRow(0, 4, get_izd_name($regionID))->
			setCellValueByColumnAndRow(0, 5, 'Отчётный период: ' . $arPeriod[$period]['NAME'])->
			setCellValueByColumnAndRow(0, 6, 'Дата формирования: ' . date('d.m.Y H:i'));

		// Вставляем в таблицу строки, если надо

		$cnt = count($arResult['REPORT']) * 2;
		foreach ($arResult['REPORT'] as $value)
			if (isset($value['SCHOOL'])) $cnt += count($value['SCHOOL']);

		if ($cnt > 6) $activeSheet->insertNewRowBefore('11', ($cnt-6));

		// Выводим отчет в таблицу
		$row = 9;
		foreach ($arResult['REPORT'] as $arStr) {
			$activeSheet->setCellValueByColumnAndRow(0, $row, $arStr['NAME'])->setCellValueByColumnAndRow(1, $row++, $arStr['ALL_SUM']);
			foreach ($arStr['SCHOOL'] as $value)
				$activeSheet->setCellValueByColumnAndRow(0, $row, $value['NAME'])->setCellValueByColumnAndRow(1, $row++, $value['SUM']);
			$row++;
		}

		// Записываем таблицу во временный файл
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($tempFileName);

		$result = array('file' => basename($tempFileName), 'error' => false);
	} else {
		$result = array('file' => '', 'error' => true, 'err_message' => 'Не найден файл шаблона для выбранного отчетного периода!');
	}
}

// Отдаем результат
echo json_encode($result);


// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>