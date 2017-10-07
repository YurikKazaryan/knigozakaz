<?
/***************************************
* Формирование сводной спецификации для Бином (2017)
*
* Параметры (передаются через POST)
*    MUN_ID - ID муниципалитета
*    PERIOD - ID отчетного периода
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require($_SERVER["DOCUMENT_ROOT"]."/include/report_prosv_new.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

//Определяем область
$regionID = getRegionFilter();

// Обработка параметра
$munID = intval(trim($_POST['MUN_ID']));
$period = intval(trim($_POST['PERIOD']));

$izd = IZD_BINOM;

$startDate = trim($_POST['START_DATE']);
$startDate = ($startDate == '' ? false : strtotime($startDate));

$templateFile = getSvodTemplate($izd, $period, $regionID);

if ($USER->IsAuthorized() && $munID && CModule::IncludeModule('iblock') && file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		// Формируем отчет
		$arTemp = report_prosv_new($munID, $period, $izd, $startDate, false);

		$arReport = $arTemp['REPORT'];
//		$arSchoolsMain = $arTemp['SCHOOLS'];
//		$arSections = $arTemp['SECTIONS'];
		$arBookPrice = $arTemp['BOOK_PRICE'];

		// Схлопываем отчет до книг

		foreach ($arReport as $code1C => $arSchool) {
			$arReport[$code1C]['COUNT'] = 0;
			foreach ($arSchool as $bookCount) $arReport[$code1C]['COUNT'] += $bookCount;
		}

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

		$objPHPExcel->setActiveSheetIndex(0);

		$maxRow= $objPHPExcel->getActiveSheet()->getHighestRow();

		if ($startDate)
			$subTitle = 'Заказы, созданные в период с ' . date('d.m.Y', $startDate) . ' по ' . date('d.m.Y');
		else
			$subTitle = 'Заказы за весь отчётный период по ' . date('d.m.Y');

		$subTitleCell = 'B1';
		$colCode = 1;
		$colPrice = 7;
		$colCount = 6;

		$objPHPExcel->getActiveSheet()->setCellValue($subTitleCell, $subTitle);

		$arPriceError = array();

		for ($i = 0; $i <= $maxRow && count($arReport) > 0; $i++) {

			$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($colCode, $i)->getValue();

			if ($arBookPrice[$code] && $arBookPrice[$code] != $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($colPrice, $i)->getValue()) {
				$arPriceError[] = $code;
			}

			if (isset($arReport[$code])) {

				// Пишем количество
				$count = intval($arReport[$code]['COUNT']);
				if ($count > 0)
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
						$colCount,
						$i,
						$count
					);

				unset($arReport[$code]);
			}
		}

		if (count($arReport) > 0 || count($arPriceError) > 0) {
			$errSheet = new PHPExcel_Worksheet($objPHPExcel, 'ERROR_LIST');
			$objPHPExcel->addSheet($errSheet);

			$errLine = 1;

			if (count($arReport) > 0) {
				$objPHPExcel->getSheetByName('ERROR_LIST')
					->setCellValue('A'.$errLine++, 'Позиции, присутствующие в каталоге, но не найденные в файле свода:')
					->setCellValue('A'.$errLine++, 'Код 1С учебника');
				foreach ($arReport as $code1C => $arRep)
					$objPHPExcel->getSheetByName('ERROR_LIST')
						->setCellValue('A'.$errLine++, $code1C);
				$errLine++;
			}
			if (count($arPriceError) > 0){
				$objPHPExcel->getSheetByName('ERROR_LIST')
					->setCellValue('A'.$errLine++, 'Позиции, в которых цена свода не совпадает с ценой в базе')
					->setCellValue('A'.$errLine++, 'Код 1С учебника');
				foreach ($arPriceError as $code1C)
					$objPHPExcel->getSheetByName('ERROR_LIST')
						->setCellValue('A'.$errLine++, $code1C);
			}
		}

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