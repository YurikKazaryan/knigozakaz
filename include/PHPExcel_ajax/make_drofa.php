<?
/***************************************
* Формирование сводной спецификации для Дрофы
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

switch ($_POST['IZD']) {
	case 'russlovo': $izd = IZD_RUSSLOVO; break;
	case 'astrel': $izd = IZD_ASTREL; break;
	case 'ventana': $izd = IZD_VENTANA; break;
	default: $izd = IZD_DROFA;
}

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
		$arSchoolsMain = $arTemp['SCHOOLS'];
		$arSections = $arTemp['SECTIONS'];
		$arBookPrice = $arTemp['BOOK_PRICE'];

		$arBookID = array();
		foreach ($arBookPrice as $key => $value) $arBookID[] = $key;
		$arBookCode = getCodeByID($arBookID);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

		// Заполняем второй лист
		$objPHPExcel->setActiveSheetIndex(1);

		$i = 1;
		foreach($arSchoolsMain as $arSchool) {

			switch ($_POST['IZD']) {
				case 'russlovo':
					$objPHPExcel->getActiveSheet()
						->setCellValueByColumnAndRow(1, $i+1, $arSchool['FULL_NAME'])
						->setCellValueByColumnAndRow(1, $i+2, $arSchool['NAME'])
						->setCellValueByColumnAndRow(1, $i+3, $arSchool['INN'])
						->setCellValueByColumnAndRow(1, $i+4, $arSchool['KPP'])
						->setCellValueByColumnAndRow(1, $i+5, $arSchool['OKPO'])
						->setCellValueByColumnAndRow(1, $i+6, $arSchool['ADDRESS'])
						->setCellValueByColumnAndRow(1, $i+7, $arSchool['ADDRESS'])
						->setCellValueByColumnAndRow(1, $i+8, $arSchool['ADDRESS'])
						->setCellValueByColumnAndRow(1, $i+9, $arSchool['PHONE'])
						->setCellValueByColumnAndRow(1, $i+10, $arSchool['EMAIL'])
						->setCellValueByColumnAndRow(1, $i+11, ' ')
						->setCellValueByColumnAndRow(1, $i+12, $arSchool['DIR_FIO'])
						->setCellValueByColumnAndRow(1, $i+13, $arSchool['BIK'])
						->setCellValueByColumnAndRow(1, $i+14, $arSchool['RASCH'])
						->setCellValueByColumnAndRow(1, $i+15, $arSchool['BANK'])
						->setCellValueByColumnAndRow(1, $i+16, $arSchool['LS'])
						->setCellValueByColumnAndRow(1, $i+17, ' ')
						->setCellValueByColumnAndRow(1, $i+18, $arSchool['OTV_FIO'] . ' ' . $arSchool['OTV_PHONE'])
						->setCellValueByColumnAndRow(1, $i+19, substr($arSchool['PUNKT_FZ'],7));
					$i += 20;
					break;
				case 'ventana':
					$i--;
					$objPHPExcel->getActiveSheet()
						->setCellValueByColumnAndRow(1, $i+1, $arSchool['FULL_NAME'])
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
						->setCellValueByColumnAndRow(1, $i+20, $arSchool['BANK']);
					$i += 24;
					break;
				default:
					$objPHPExcel->getActiveSheet()
						->setCellValueByColumnAndRow(1, $i+1, $arSchool['FULL_NAME'])
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
						->setCellValueByColumnAndRow(1, $i+20, $arSchool['BANK']);
					$i += 23;
			}
		}

		$objPHPExcel->setActiveSheetIndex(0);

		$maxRow= $objPHPExcel->getActiveSheet()->getHighestRow();

		if ($startDate)
			$subTitle = 'Заказы, созданные в период с ' . date('d.m.Y', $startDate) . ' по ' . date('d.m.Y');
		else
			$subTitle = 'Заказы за весь отчётный период по ' . date('d.m.Y');

		switch ($_POST['IZD']) {
			case 'russlovo':
				$subTitleCell = 'A2';
				$colReady = 50;
				$colInsertBefore = 'DF';
				$colSchool = 11;
				$colCode = 0;
				$colPrice = 7;
				break;
			case 'astrel':
				$subTitleCell = 'B4';
				$colReady = 75;
				$colInsertBefore = 'FY';
				$colSchool = 28;
				$colCode = 2;
				$colPrice = 25;
				break;
			case 'ventana':
				$subTitleCell = 'A5';
				$colReady = 75;
				$colInsertBefore = 'FQ';
				$colSchool = 20;
				$colCode = 2;
				$colPrice = 17;
				break;
			default:
				$subTitleCell = 'A5';
				$colReady = 75;
				$colInsertBefore = 'GA';
				$colSchool = 31;
				$colCode = 2;
				$colPrice = 17;
		}

		$objPHPExcel->getActiveSheet()->setCellValue($subTitleCell, $subTitle);

		// Вставляем столбцы, если надо
		if (count($arSchools) > $colReady)
			$objPHPExcel->getActiveSheet()->insertNewColumnBefore($colInsertBefore, (count($arSchools)-75) * 2);

		$arMunColors = array(1 => 'D0D0D0', 2 => 'F2F2F2');

		$arSchools = $arSchoolsMain;

		$arPriceError = array();

		for ($i = 0; $i <= $maxRow && count($arReport) > 0; $i++) {

			$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($colCode, $i)->getValue();

			if ($code && $arBookPrice[$arBookCode[$code]] && $arBookPrice[$arBookCode[$code]] != $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($colPrice, $i)->getValue()) {
				$arPriceError[] = $code;
			}

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

						if ($_POST['IZD'] != 'russlovo') {
							$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(
									$colSchool + $schoolPos*2,
									5,
									$arSchools[$schoolID]['NAME'] . ",\nИНН " . $arSchools[$schoolID]['INN']
							);
						}

						unset($arSchools[$schoolID]);
					}

					// Пишем количество
					$count = intval($schoolCount);
					if ($count > 0)
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
							$colSchool + $schoolPos*2,
							$i,
							$count
						);

					$schoolPos += 1;
				}
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