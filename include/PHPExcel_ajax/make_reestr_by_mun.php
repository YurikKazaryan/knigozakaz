<?
/***************************************
* Формирование рееестра заказов с разбивкой по муниципалитетам
*
* Параметры (передаются через POST)
*    PERIOD - ID периода для отчета
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта

require_once($_SERVER["DOCUMENT_ROOT"]."/include/report_reestr_by_mun.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

//Определяем область
$regionID = getRegionFilter();

// Обработка параметра
$period = intval(trim($_POST['PERIOD']));

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'reestr_by_mun.xlsx';

if ($USER->IsAuthorized() && $period && CModule::IncludeModule('iblock')) {

	if (file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		// Формируем массив с отчетом
		$arResult = report_reestr_by_mun($regionID, $period);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);
		$activeSheet = $objPHPExcel->getActiveSheet();

		$activeSheet->
			setCellValueByColumnAndRow(1, 3, get_izd_name($regionID))->
			setCellValueByColumnAndRow(1, 5, date('d.m.Y H:i:s'));

		// Вставляем в таблицу столбцы, если надо
		if (count($arResult['IZD_LIST']) > 3) {
			$activeSheet->insertNewColumnBefore('D', (count($arResult['IZD_LIST'])-3));
		}
		
		// Вставляем в таблицу строки, если надо
		if (count($arResult['REPORT']) > 4) {
			$activeSheet->insertNewRowBefore('11', (count($arResult['REPORT'])-4));
		}

		// Заполняем названия издательств
		$col = 2;
		foreach ($arResult['IZD_LIST'] as $value)
			$activeSheet->setCellValueByColumnAndRow($col++, 8, $value);

		// Выводим отчет в таблицу
		$row = 9;
		foreach ($arResult['REPORT'] as $arMun) {
			$activeSheet->setCellValueByColumnAndRow(0, $row, $arMun['NAME'])->setCellValueByColumnAndRow(1, $row, $arMun['ALL_COUNT']);
			$col = 2;
			foreach ($arMun['IZD'] as $key => $value) {
				if (isset($arResult['IZD_LIST'][$key])) {
					if ($value) $activeSheet->setCellValueByColumnAndRow($col, $row, $value);
					$col++;
				}
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