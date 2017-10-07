<?
/********************************************
* Создание файла отчета по книгам
* Параметр: PERIOD - ID отчетного периода
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

$result = array('error' => true);

$periodID = intval($_POST['PERIOD']);

if(CModule::IncludeModule('iblock') && $periodID) {

	$arPeriod = getPeriodList();

	$res = CIBlockElement::GetList(
		array('PROPERTY_IZD_ID' => 'asc', 'PROPERTY_BOOK.PROPERTY_FP_CODE' => 'asc'),
		array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_PERIOD' => $periodID, 'PROPERTY_REGION_ID' => getRegionFilter()),
		array('PROPERTY_BOOK', 'PROPERTY_IZD_ID'), false,
		array('ID', 'IBLOCK_ID', 'PROPERTY_PERIOD')
	);

	$result = array();
	$arBookFilter = array();

	while ($arFields = $res->GetNext()) {

		if (!in_array($arFields['PROPERTY_BOOK_VALUE'], $arBookFilter)) $arBookFilter[] = $arFields['PROPERTY_BOOK_VALUE'];

		$result[] = array(
			'IZD_ID' => $arFields['PROPERTY_IZD_ID_VALUE'],
			'BOOK_ID' => $arFields['PROPERTY_BOOK_VALUE'],
			'CNT' => $arFields['CNT']
		);
	}

	// Получаем названия издательств
	$arIzd = array();
	$res = CIBlockSection::GetList(
		false,
		array('IBLOCK_ID' => IB_BOOKS),
		false,
		array('IBLOCK_ID', 'ID', 'NAME')
	);
	while ($arFields = $res->GetNext()) $arIzd[$arFields['ID']] = $arFields['~NAME'];

	$arBooks = getBookInfo($arBookFilter, true);

	// Заполняем названия и код ФП
	foreach ($result as $key => $arItem) {
		$result[$key]['IZD_NAME'] = $arIzd[$arItem['IZD_ID']];
		$result[$key]['BOOK_NAME'] = $arBooks[$arItem['BOOK_ID']]['~NAME'];
		$result[$key]['FP_CODE'] = $arBooks[$arItem['BOOK_ID']]['PROPERTY_FP_CODE_VALUE'];
	}

	// Генерируем временное имя файла
	$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

	// Загружаем шаблон таблицы
	$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/report_umk.xls';
	$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

	// Выгружаем отчет в таблицу
	$objPHPExcel->getActiveSheet()->setCellValue('C3', date('d.m.Y H:i'))->setCellValue('A2', $arPeriod[$periodID]['NAME']);

	$oldID = 0;
	$numStr = 0;
	foreach ($result as $arItem) {
		if ($arItem['IZD_ID'] != $oldID) {
			if (!$oldID) $numStr++; else $numStr += 2;
			$oldID = $arItem['IZD_ID'];
			$objPHPExcel->getActiveSheet()
				->mergeCells('A'.($numStr+5).':C'.($numStr+5))
				->setCellValueByColumnAndRow(0, $numStr+5, 'Издательство '.$arItem['IZD_NAME'])
				->getStyle('A'.($numStr+5))->applyFromArray(array('font' => array('bold' => true, 'size' => 14)))
				->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		$numStr++;
		$objPHPExcel->getActiveSheet()
			->setCellValueByColumnAndRow(0, $numStr+5, $arItem['BOOK_NAME'])
			->setCellValueByColumnAndRow(1, $numStr+5, $arItem['FP_CODE'])
			->setCellValueByColumnAndRow(2, $numStr+5, $arItem['CNT']);
	}

	// Записываем таблицу во временный файл
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save($tempFileName);

	$result = array('file' => basename($tempFileName), 'error' => false);

}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>