<?
/***************************************
* Формирование рееестра 3
*
* Параметры (передаются через POST)
*    PERIOD - ID периода для отчета
*    MODE - режим (1 (кол-во), 2 (сумма), 3 (кол-во И сумма))
***************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта

require_once($_SERVER["DOCUMENT_ROOT"]."/include/report_reestr_3.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

//Определяем область
$regionID = getRegionFilter();

//test_out($_POST);

// Обработка параметра
$period = intval(trim($_POST['PERIOD']));
$mode = intval($_POST['MODE']);
if ($mode != 1 && $mode != 2) $mode == 3;

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'reestr_3.xlsx';

if ($USER->IsAuthorized() && $period && CModule::IncludeModule('iblock')) {

	if (file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		// Формируем массив с отчетом
		$arResult = report_reestr_3($regionID, $period, $mode);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);
		$activeSheet = $objPHPExcel->getActiveSheet();

		$activeSheet->
			setCellValueByColumnAndRow(0, 3, 'Регион: ' . get_izd_name($regionID))->
			setCellValueByColumnAndRow(0, 5, 'Отчетный период: ' . $arPeriod[$period]['NAME'])->
			setCellValueByColumnAndRow(0, 7, 'Дата формирования: ' . date('d.m.Y H:i'))->
			setCellValueByColumnAndRow(4, 9, 'Количество экземпляров книг, заказанных черех АИС заказа учебников (' .
				($mode == 1 ? 'шт.' : ($mode == 2 ? 'руб.' : 'шт., руб.')) .
				')');

		// Вставляем в таблицу столбцы, если надо
		if (count($arResult['IZD_LIST']) > 3) {
			$activeSheet->insertNewColumnBefore('F', (count($arResult['IZD_LIST'])-3));
		}

		// Вставляем в таблицу строки, если надо
		$cnt = count($arResult['REPORT']) - 1;
		foreach ($arResult['REPORT'] as $value)
			if (isset($value['SCHOOLS']))
				$cnt += count($value['SCHOOLS']);
		if ($cnt > 4) {
			$activeSheet->insertNewRowBefore('13', ($cnt-4));
		}

		// Заполняем названия издательств
		$col = 4;
		foreach ($arResult['IZD_LIST'] as $value)
			$activeSheet->setCellValueByColumnAndRow($col++, 10, $value);
		// Выводим отчет в таблицу
		$row = 11;
		foreach ($arResult['REPORT'] as $arMun) {
			$activeSheet->setCellValueByColumnAndRow(0, $row, $arMun['NAME']);
			foreach ($arMun['SCHOOLS'] as $arSch) {
				$activeSheet
					->setCellValueByColumnAndRow(1, $row, $arSch['NAME'])
					->setCellValueByColumnAndRow(2, $row, $arSch['ADDRESS'])
					->setCellValueByColumnAndRow(3, $row, $arSch['DIR']);
				$col = 4;
				foreach ($arSch['IZD'] as $key => $value) {
					if (isset($arResult['IZD_LIST'][$key])) {
						if ($value) $activeSheet->setCellValueByColumnAndRow($col, $row, ($mode == 1 ? $value : ($mode == 2 ? sprintf('%.2f', $arSch['IZD_SUM'][$key]) : $value . chr(10) . sprintf('%.2f', $arSch['IZD_SUM'][$key]))));
						$col++;
					}
				}
				$row++;
			}
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