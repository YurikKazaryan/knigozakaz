<?
/*****************************************************
* Формирование спсика "пустых" школ
* Параметром передается PERIOD - ID отчетного периода
*****************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

$result = array('file' => '', 'error' => true);

$periodID = intval($_POST['PERIOD']);

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'empty_list.xls';

if ($USER->IsAuthorized() && CModule::IncludeModule('iblock') && file_exists($templateFile) && $periodID) {

	$arPeriod = getPeriodList();

	// Генерируем временное имя файла
	$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

	// Формируем отчет

	// составляем список ID школ. у которых есть заказы или отчеты
	$arOrders = array();
	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 11, 'PROPERTY_PERIOD' => $periodID),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_PERIOD')
	);
	while ($arFields = $res->GetNext())
		if (!in_array($arFields['PROPERTY_SCHOOL_ID_VALUE'], $arOrders)) $arOrders[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];

	// Выбираем школы, которых нет в сформированном списке
	$arSchools = array();
	$res = CIBlockElement::GetList(
		array('PROPERTY_MUN' => 'asc'),
		array('IBLOCK_ID' => 10),
		false, false,
		array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_FULL_NAME', 'PROPERTY_DIR_FIO', 'PROPERTY_PHONE', 'PROPERTY_EMAIL', 'PROPERTY_MUN')
	);
	while ($arFields = $res->GetNext())
		if (!in_array($arFields['ID'], $arOrders))
			$arSchools[] = array(
				'ID' => $arFields['ID'],
				'MUN' => get_izd_name($arFields['PROPERTY_MUN_VALUE']),
				'NAME' => $arFields['~NAME'] . ($arFields['~PROPERTY_FULL_NAME_VALUE'] ? ' - ' . $arFields['~PROPERTY_FULL_NAME_VALUE'] : ''),
				'DIR' => $arFields['PROPERTY_DIR_FIO_VALUE'],
				'PHONE' => $arFields['~PROPERTY_PHONE_VALUE'],
				'EMAIL' => $arFields['~PROPERTY_EMAIL_VALUE']
			);

	// Загружаем таблицу-шаблон
	$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

	// Вставляем строки, если надо
	$count = count($arSchools);
	$addCount = $count > 4 ? $count - 4 : 0;
	if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(10, $addCount);

	$objPHPExcel->getActiveSheet()
		->setCellValue('A3', $arPeriod[$periodID]['NAME'])
		->setCellValue('A4', 'Отчёт сформирован: ' . date('d.m.Y H:i'));

	$numStr = 0;
	foreach ($arSchools as $arSchool) {
		$objPHPExcel->getActiveSheet()->getRowDimension(8+$numStr)->setRowHeight(-1);

		$objPHPExcel->getActiveSheet()
			->setCellValueByColumnAndRow(0, 8+$numStr, $numStr+1)
			->setCellValueByColumnAndRow(1, 8+$numStr, $arSchool['MUN'])
			->setCellValueByColumnAndRow(2, 8+$numStr, $arSchool['NAME'])
			->setCellValueByColumnAndRow(3, 8+$numStr, $arSchool['DIR'])
			->setCellValueByColumnAndRow(4, 8+$numStr, $arSchool['PHONE'])
			->setCellValueByColumnAndRow(5, 8+$numStr, $arSchool['EMAIL'])
			->setCellValueByColumnAndRow(6, 8+$numStr, $arSchool['ID']);
		$numStr++;
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