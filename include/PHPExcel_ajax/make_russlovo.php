<?
/***************************************
* Формирование сводной спецификации для Вентана-Граф
*
* Параметры (передаются через POST)
*    MUN_ID - ID муниципалитета
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require($_SERVER["DOCUMENT_ROOT"]."/include/report_ventana.php");
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

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'ventana_svod1_' . $regionID . '_' . $period . '.xlsx';

if ($USER->IsAuthorized() && $munID && CModule::IncludeModule('iblock')) {

	if (file_exists($templateFile)) {

		$arPeriod = getPeriodList();

		// Генерируем временное имя файла
		$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

		switch ($period) {

			case 63889: // 2014-2015 учебный год
				// Формируем отчет
				list($arReport, $arSchools) = report_ventana($munID, $period, $startDate);

				// Загружаем таблицу-шаблон
				$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

				if ($startDate)
					$subTitle = 'Заказы, созданные в период с ' . date('d.m.Y', $startDate) . ' по ' . date('d.m.Y');
				else
					$subTitle = 'Заказы за весь отчётный период по ' . date('d.m.Y');

				$objPHPExcel->getActiveSheet()->setCellValue('A4', $subTitle);

				$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Период: ' . $arPeriod[$period]['NAME']);

				$maxRow= $objPHPExcel->getActiveSheet()->getHighestRow();

				// Вставляем столбцы, если надо
				if (count($arSchools) > 74) {
					$objPHPExcel->getActiveSheet()->insertNewColumnBefore('EX', (count($arSchools)-74)*2);
				}

				$arMunColors = array(1 => 'FFFF00', 2 => 'FABF8F', 3 => 'FDE9D9', 4 => 'F2F2F2');

				for ($i = 0; $i <= $maxRow && count($arReport) > 0; $i++) {

					$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
					if (isset($arReport[$code])) {
						$schoolPos=1;
						$oldMun = 0;
						$munColor = 1;
						foreach($arReport[$code] as $schoolID => $schoolCount) {
							// Если школа еще не записана в заголовок - пишем
							if (isset($arSchools[$schoolID])) {

								if ($arSchools[$schoolID]['MUN'] != $oldMun) {
									$munColor = ($munColor < count($arMunColors) ? $munColor+1 : 1);
									$oldMun = $arSchools[$schoolID]['MUN'];
								}

								$sheet = $objPHPExcel->getActiveSheet();

								setBorderStyle($objPHPExcel, getLetterAddress(7 + $schoolPos).'5');
								setBorderStyle($objPHPExcel, getLetterAddress(7 + $schoolPos).'6');
								setBorderStyle($objPHPExcel, getLetterAddress(7 + $schoolPos + 1).'6');

								$sheet->getColumnDimension(getLetterAddress(7 + $schoolPos))->setWidth(28);
								$sheet->getColumnDimension(getLetterAddress(7 + $schoolPos+1))->setWidth(20);

								$sheet
									->setCellValueByColumnAndRow(6 + $schoolPos, 4, 'ИНН '.$arSchools[$schoolID]['INN'])
									->setCellValueByColumnAndRow(6 + $schoolPos, 5, html_entity_decode($arSchools[$schoolID]['NAME']))
									->setCellValueByColumnAndRow(6 + $schoolPos, 6, 'Заказ , экз./ комплект.')
									->setCellValueByColumnAndRow(6 + $schoolPos + 1, 6, 'Сумма, руб.');

								$sheet->getStyle(getLetterAddress(7 + $schoolPos).'6')->getAlignment()
									->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
									->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

								$sheet->getStyle(getLetterAddress(7 + $schoolPos).'5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($arMunColors[$munColor]);
								$sheet->getStyle(getLetterAddress(7 + $schoolPos).'6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($arMunColors[$munColor]);
								$sheet->getStyle(getLetterAddress(7 + $schoolPos+1).'6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($arMunColors[$munColor]);

								$sheet->getStyle(getLetterAddress(7 + $schoolPos).'6')->getFont()->setBold(true)->setSize(13);

								$sheet->getStyle(getLetterAddress(7 + $schoolPos + 1).'6')->getAlignment()
									->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
									->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

								$sheet->getStyle(getLetterAddress(7 + $schoolPos + 1).'6')->getFont()->setBold(true)->setSize(13);

								for ($jj=7; $jj<=$maxRow; $jj++) {
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6 + $schoolPos + 1, $jj, '='.getLetterAddress(7 + $schoolPos).$jj.'*E'.$jj);
								}


								unset($arSchools[$schoolID]);
							}

							// Пишем количество и формулу суммы
							$count = intval($schoolCount);
							if ($count > 0) {
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6 + $schoolPos, $i, $count);
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6 + $schoolPos + 1, $i, '='.getLetterAddress(7 + $schoolPos).$i.'*E'.$i);
							}
							$schoolPos += 2;
						}
						unset($arReport[$code]);
					}
				}
				break;

			default:



				// Формируем отчет
				list($arReport, $arSchools) = report_prosv($munID, $period, 108, $startDate);

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
					$objPHPExcel->getActiveSheet()->insertNewColumnBefore('FL', (count($arSchools)-75) * 2);
				}

				for ($i = 0; $i <= $maxRow && count($arReport) > 0; $i++) {

					$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();

					if (isset($arReport[$code])) {
						$schoolPos=0;
						foreach($arReport[$code] as $schoolID => $schoolCount) {
							// Если школа еще не записана в заголовок - пишем
							if (isset($arSchools[$schoolID])) {

								$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(13 + $schoolPos, 5, html_entity_decode($arSchools[$schoolID]['NAME']) . ",\nИНН " . $arSchools[$schoolID]['INN']);

								unset($arSchools[$schoolID]);
							}

							// Пишем количество и формулу суммы
							$count = intval($schoolCount);
							if ($count > 0) {
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13 + $schoolPos, $i, $count);
							}
							$schoolPos += 2;
						}
						unset($arReport[$code]);
					}
				}




				break;
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