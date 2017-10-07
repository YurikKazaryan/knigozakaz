<?
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

$templateFile = getSvodTemplate(IZD_PROSV, $period, $regionID);

if ($USER->IsAuthorized() && $munID && CModule::IncludeModule('iblock') && file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		// Формируем отчет
		$arTemp = report_prosv_new($munID, $period, IZD_PROSV, $startDate);

		$arReport = $arTemp['REPORT'];
		$arSchoolsMain = $arTemp['SCHOOLS'];
		$arSections = $arTemp['SECTIONS'];

//if ($USER->GetID() == 1) test_out($arTemp);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

if ($USER->GetID() == 1 && $objPHPExcel === false) test_out($templateFile);

		if ($startDate)
			$subTitle = 'Заказы, созданные в период с ' . date('d.m.Y', $startDate) . ' по ' . date('d.m.Y');
		else
			$subTitle = 'Заказы за весь отчётный период по ' . date('d.m.Y');

		$arMunColors = array(1 => 'D0D0D0', 2 => 'F2F2F2');

		$arStartCol = array(14, 15, 12);	// Стартовые столбцы школ на листах

		// Цикл по подразделам каталога
		foreach ($arSections as $arSec) {
			$objPHPExcel->setActiveSheetIndex($arSec['PAGE']);	// Переключились на нужную страницу свода

			$sheet = $objPHPExcel->getActiveSheet();

			$sheet->setCellValue('A3', $subTitle);	// Заполнили подзаголовок с датой

			$maxRow = $sheet->getHighestRow(); // Количество строк на листе свода

			$arTemp = reset($arReport[$arSec['SUB_ID']]);
			$colCount = count($arTemp);

			$arSchools = $arSchoolsMain;

			// Вставляем столбцы, если надо
			if ($colCount > 10) $sheet->insertNewColumnBefore('R', $colCount-10);

			for ($i = 0; $i <= $maxRow && count($arReport[$arSec['SUB_ID']]) > 0; $i++) {

				$code = $sheet->getCellByColumnAndRow(2, $i)->getValue();

				if (isset($arReport[$arSec['SUB_ID']][$code])) {
					$schoolPos=1;
					$oldMun = 0;
					$munColor = 1;

					foreach($arReport[$arSec['SUB_ID']][$code] as $schoolID => $schoolCount) {

						// Если школа еще не записана в заголовок - пишем
						if (isset($arSchools[$schoolID])) {

							if ($arSchools[$schoolID]['MUN'] != $oldMun) {
								$munColor = ($munColor < count($arMunColors) ? $munColor+1 : 1);
								$oldMun = $arSchools[$schoolID]['MUN'];
							}

//							setBorderStyle($objPHPExcel, getLetterAddress(15 + $schoolPos).'4');
//							setBorderStyle($objPHPExcel, getLetterAddress(15 + $schoolPos).'5');

//							$sheet->getColumnDimension(getLetterAddress(15 + $schoolPos))->setWidth(28);

							$sheet
								->setCellValueByColumnAndRow($arStartCol[$arSec['PAGE']] + $schoolPos, 5, 'ИНН '.$arSchools[$schoolID]['INN'])
								->setCellValueByColumnAndRow($arStartCol[$arSec['PAGE']] + $schoolPos, 6, $arSchools[$schoolID]['NAME']);

							$sheet->getStyle(getLetterAddress($arStartCol[$arSec['PAGE']] + $schoolPos + 1).'6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($arMunColors[$munColor]);
							$sheet->getStyle(getLetterAddress($arStartCol[$arSec['PAGE']] + $schoolPos + 1).'5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($arMunColors[$munColor]);

							unset($arSchools[$schoolID]);
						}

						// Пишем количество и формулу суммы
						$count = intval($schoolCount);
						if ($count > 0) {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($arStartCol[$arSec['PAGE']] + $schoolPos, $i, $count)
								->getStyle(getLetterAddress($arStartCol[$arSec['PAGE']] + $schoolPos + 1).$i)->getAlignment()
								->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
								->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						}
						$schoolPos += 1;
					}
					unset($arReport[$code]);

				}
			}
		}

		$objPHPExcel->setActiveSheetIndex(0);

		// Записываем таблицу во временный файл
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($tempFileName);

		$result = array('file' => basename($tempFileName), 'error' => false);
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>