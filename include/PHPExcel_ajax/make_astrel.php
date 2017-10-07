<?
/***************************************
* Формирование сводной спецификации для Астрели
*
* Параметры (передаются через POST)
*    MUN_ID - ID муниципалитета
*    PERIOD - ID отчетного периода
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require($_SERVER["DOCUMENT_ROOT"]."/include/report_prosv.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

/*******************************************
* Дополнительные функции
*******************************************/
function setBorderStyle($objPHPExcel, $address) {
	$borderStyle = $objPHPExcel->getActiveSheet()->getStyle($address)->getBorders();
	$borderStyle->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$borderStyle->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$borderStyle->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$borderStyle->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
}
//******************************************

global $USER;

//Определяем область
$regionID = getRegionFilter();

// Обработка параметра
$munID = intval(trim($_POST['MUN_ID']));
$period = intval(trim($_POST['PERIOD']));

$startDate = trim($_POST['START_DATE']);
$startDate = ($startDate == '' ? false : strtotime($startDate));

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'astrel_svod_' . $regionID . '_' . $period . '.xlsx';

if ($USER->IsAuthorized() && $munID && CModule::IncludeModule('iblock')) {

	if (file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		// Формируем отчет
		list($arReport, $arSchools) = report_prosv($munID, $period, 101, $startDate);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

		// Заполняем второй лист
		$objPHPExcel->setActiveSheetIndex(1);

		$i = 1;
		foreach($arSchools as $arSchool) {
			$objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow(1, $i+1, html_entity_decode($arSchool['FULL_NAME']))
				->setCellValueByColumnAndRow(1, $i+2, 'ЮрЛицо')
				->setCellValueByColumnAndRow(1, $i+3, 'Покупатель')
				->setCellValueByColumnAndRow(1, $i+4, ' ')
				->setCellValueByColumnAndRow(1, $i+5, $arSchool['INN'])
				->setCellValueByColumnAndRow(1, $i+6, $arSchool['KPP'])
				->setCellValueByColumnAndRow(1, $i+7, $arSchool['OKPO'])
				->setCellValueByColumnAndRow(1, $i+8, ' ')
				->setCellValueByColumnAndRow(1, $i+9, $arSchool['ADDRESS'])
				->setCellValueByColumnAndRow(1, $i+10, $arSchool['ADDRESS'])
				->setCellValueByColumnAndRow(1, $i+11, $arSchool['ADDRESS'])
				->setCellValueByColumnAndRow(1, $i+12, $arSchool['PHONE'])
				->setCellValueByColumnAndRow(1, $i+13, '')
				->setCellValueByColumnAndRow(1, $i+14, $arSchool['EMAIL'])
				->setCellValueByColumnAndRow(1, $i+15, '')
				->setCellValueByColumnAndRow(1, $i+16, $arSchool['DIR_FIO'])
				->setCellValueByColumnAndRow(1, $i+17, $arSchool['BIK'])
				->setCellValueByColumnAndRow(1, $i+18, $arSchool['RASCH'])
				->setCellValueByColumnAndRow(1, $i+19, $arSchool['LS'])
				->setCellValueByColumnAndRow(1, $i+20, html_entity_decode($arSchool['BANK']));

			$i += 23;
		}

		$objPHPExcel->setActiveSheetIndex(0);

		$maxRow= $objPHPExcel->getActiveSheet()->getHighestRow();

		if ($startDate)
			$subTitle = 'Заказы, созданные в период с ' . date('d.m.Y', $startDate) . ' по ' . date('d.m.Y');
		else
			$subTitle = 'Заказы за весь отчётный период по ' . date('d.m.Y');

		$objPHPExcel->getActiveSheet()->setCellValue('A4', $subTitle);

		// Вставляем столбцы, если надо
		if (count($arSchools) > 75) {
			$objPHPExcel->getActiveSheet()->insertNewColumnBefore('GA', (count($arSchools)-75) * 2);
		}

		$arMunColors = array(1 => 'FFFF00', 2 => 'FFFF00', 3 => 'FFFF00', 4 => 'FFFF00');

		for ($i = 0; $i <= $maxRow && count($arReport) > 0; $i++) {

			$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
			if (isset($arReport[$code])) {
				$schoolPos=0;
				$oldMun = 0;
				$munColor = 1;
				foreach($arReport[$code] as $schoolID => $schoolCount) {
					// Если школа еще не записана в заголовок - пишем
					if (isset($arSchools[$schoolID])) {

						if ($arSchools[$schoolID]['MUN'] != $oldMun) {
							$munColor = ($munColor < count($arMunColors) ? $munColor+1 : 1);
							$oldMun = $arSchools[$schoolID]['MUN'];
						}

						$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(28 + $schoolPos*2, 5, html_entity_decode($arSchools[$schoolID]['NAME']) . ",\nИНН " . $arSchools[$schoolID]['INN']);

						unset($arSchools[$schoolID]);
					}

					// Пишем количество и формулу суммы
					$count = intval($schoolCount);
					if ($count > 0) {
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(28 + $schoolPos*2, $i, $count);
					}
					$schoolPos += 1;
				}
				unset($arReport[$code]);
			}
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