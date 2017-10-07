<?
/***************************************
* Выгрузка одного заказа в формате Excel
*
* Параметры (передаются через GET)
*    ORDER_ID  - ID заказа
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php');	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/report.xls';

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/report_svod.php');	// Генератор отчета

global $USER;

$result = array('file' => '', 'error' => true);

// Обработка параметра
$level1 = strtoupper(trim($_POST['LEVEL1']));
$level2 = intval(trim($_POST['LEVEL2']));
$level3 = intval(trim($_POST['LEVEL3']));
$level4 = intval(trim($_POST['LEVEL4']));
$mode = intval(trim($_POST['MODE']));
$period = intval(trim($_POST['PERIOD']));
$self = (intval(trim($_POST['SELF'])) == 1);

if (!in_array($mode, array(0, 1, 2))) $mode = 0;

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/report' . ($self ? '_self' : '') . '.xls';

if ($USER->IsAuthorized() && in_array($level1, array('MUN', 'IZD')) && CModule::IncludeModule('iblock') && file_exists($templateFile)) {

	$arPeriod = getPeriodList();

	$arReport = report_svod($mode, $level1, $level2, $level3, $level4, $period, $self);	// Формируем отчет

	$arIzd = get_izd_list();
	$arMun = get_mun_list($USER->GetID());
	if ($level3) $arSchool = get_school_list();

// test_out(print_r($arIzd,1));
// test_out(print_r($arMun,1));
// test_out(print_r($arReport,1));

	// Генерируем временное имя файла
	$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'rep');

	// Загружаем шаблон таблицы
	$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

	// Выгружаем отчет в таблицу
	$objPHPExcel->getActiveSheet()
		->setCellValue('A2', ($self ? 'Сводный отчёт по самостоятельным закупкам' : 'Сводный отчёт'))
		->setCellValue('A4', 'Период: ' . $arPeriod[$period]['NAME'])
		->setCellValue('A3', ($mode==0 ? '(Включены только заказы)' : ($mode==1 ? '(Включены только отчёты)' : '(Включены заказы и отчёты)')))
		->setCellValue('C4', date('d.m.Y h:i'));

	$numStr = 0;
	foreach ($arReport as $key1 => $arLevel1) {
		$numStr++;
		$objPHPExcel->getActiveSheet()
			->setCellValueByColumnAndRow(0, $numStr+11, ($level1 == 'MUN' ? $arMun[$key1] : $arIzd[$key1]))
			->setCellValueByColumnAndRow(2, $numStr+11, $arLevel1['COUNT'])
			->setCellValueByColumnAndRow(3, $numStr+11, $arLevel1['SUM']);

		if ($self) $objPHPExcel->getActiveSheet()
			->setCellValueByColumnAndRow(4, $numStr+11, $arLevel1['SUM_KATALOG'])
			->setCellValueByColumnAndRow(5, $numStr+11, $arLevel1['SUM'] - $arLevel1['SUM_KATALOG']);

		if ($level2) {
			foreach ($arLevel1 as $key2 => $arLevel2) {
				$numStr++;
				$objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow(0, $numStr+11, ($level1 == 'MUN' ? $arIzd[$key2] : $arMun[$key2]))
					->setCellValueByColumnAndRow(2, $numStr+11, $arLevel2['COUNT'])
					->setCellValueByColumnAndRow(3, $numStr+11, $arLevel2['SUM']);

				if ($self) $objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow(4, $numStr+11, $arLevel2['SUM_KATALOG'])
					->setCellValueByColumnAndRow(5, $numStr+11, $arLevel2['SUM'] - $arLevel2['SUM_KATALOG']);

				if ($level3) {
					foreach ($arLevel2 as $key3 => $arLevel3) {
						$numStr++;
						$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(0, $numStr+11, $arSchool[$key3])
							->setCellValueByColumnAndRow(2, $numStr+11, $arLevel3['COUNT'])
							->setCellValueByColumnAndRow(3, $numStr+11, $arLevel3['SUM']);

						if ($self) $objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(4, $numStr+11, $arLevel3['SUM_KATALOG'])
							->setCellValueByColumnAndRow(5, $numStr+11, $arLevel3['SUM'] - $arLevel3['SUM_KATALOG']);

						if ($level4) {
							foreach ($arLevel3 as $key4 => $arLevel4) {
								$numStr++;
								$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(0, $numStr+11, $arLevel4['NAME'])
									->setCellValueByColumnAndRow(1, $numStr+11, $arLevel4['FP_CODE'])
									->setCellValueByColumnAndRow(2, $numStr+11, $arLevel4['COUNT'])
									->setCellValueByColumnAndRow(3, $numStr+11, $arLevel4['SUM']);

								if ($self) $objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(4, $numStr+11, $arLevel4['SUM_KATALOG'])
									->setCellValueByColumnAndRow(5, $numStr+11, $arLevel4['SUM'] - $arLevel4['SUM_KATALOG']);
							}
						}
					}
				} else {
					if ($level4) {
						foreach ($arLevel2 as $key4 => $arLevel4) {
							$numStr++;
							$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(0, $numStr+11, $arLevel4['NAME'])
								->setCellValueByColumnAndRow(1, $numStr+11, $arLevel4['FP_CODE'])
								->setCellValueByColumnAndRow(2, $numStr+11, $arLevel4['COUNT'])
								->setCellValueByColumnAndRow(3, $numStr+11, $arLevel4['SUM']);

							if ($self) $objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(4, $numStr+11, $arLevel4['SUM_KATALOG'])
								->setCellValueByColumnAndRow(5, $numStr+11, $arLevel4['SUM'] - $arLevel4['SUM_KATALOG']);
						}
					}
				}
			}
		} else { // Если отключена группировка по второму уровню
			if ($level3) {
				foreach ($arLevel1 as $key3 => $arLevel3) {
					$numStr++;
					$objPHPExcel->getActiveSheet()
						->setCellValueByColumnAndRow(0, $numStr+11, $arSchool[$key3])
						->setCellValueByColumnAndRow(2, $numStr+11, $arLevel3['COUNT'])
						->setCellValueByColumnAndRow(3, $numStr+11, $arLevel3['SUM']);

					if ($self) $objPHPExcel->getActiveSheet()
						->setCellValueByColumnAndRow(4, $numStr+11, $arLevel3['SUM_KATALOG'])
						->setCellValueByColumnAndRow(5, $numStr+11, $arLevel3['SUM'] - $arLevel3['SUM_KATALOG']);
				}
				if ($level4) {
					foreach ($arLevel3 as $key4 => $arLevel4) {
						$numStr++;
						$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(0, $numStr+11, $arLevel4['NAME'])
							->setCellValueByColumnAndRow(1, $numStr+11, $arLevel4['FP_CODE'])
							->setCellValueByColumnAndRow(2, $numStr+11, $arLevel4['COUNT'])
							->setCellValueByColumnAndRow(3, $numStr+11, $arLevel4['SUM']);

						if ($self) $objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(4, $numStr+11, $arLevel4['SUM_KATALOG'])
							->setCellValueByColumnAndRow(5, $numStr+11, $arLevel4['SUM'] - $arLevel4['SUM_KATALOG']);
					}
				}
			} else {
				if ($level4) {
					foreach ($arLevel1 as $key4 => $arLevel4) {
						$numStr++;
						$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(0, $numStr+11, $arLevel4['NAME'])
							->setCellValueByColumnAndRow(1, $numStr+11, $arLevel4['FP_CODE'])
							->setCellValueByColumnAndRow(2, $numStr+11, $arLevel4['COUNT'])
							->setCellValueByColumnAndRow(3, $numStr+11, $arLevel4['SUM']);

						if ($self) $objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(4, $numStr+11, $arLevel4['SUM_KATALOG'])
							->setCellValueByColumnAndRow(5, $numStr+11, $arLevel4['SUM'] - $arLevel4['SUM_KATALOG']);
					}
				}
			}
		}
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