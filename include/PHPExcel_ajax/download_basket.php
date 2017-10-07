<?
/***************************************
* Выгрузка корзины в формате Excel
*
* Параметры (передаются через GET)
*    ID  - ID школы
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'template_order.xls';

// Обработка параметра
$schoolID = intval(trim($_GET['ID']));

if ($USER->IsAuthorized() && $schoolID && CModule::IncludeModule('iblock') && file_exists($templateFile)) {

	$arSchool = get_school_info($schoolID);

	// если текущий пользователь - админ школы, муниципалитета или области - формируем файл
	if (is_admin($schoolID) || in_array($USER->GetID(), $arSchool['ADMIN'])) {

		// Формируем массив со списком книг, разбитый по издательствам
		$arBooks = array();
		$sumAll = 0;
		$countAll = 0;

		$strCount = 0;

		$arBookID = array();
	
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_SCHOOL_ID' => $schoolID, 'PROPERTY_STATUS' => 'oscart'),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_PRICE', 'PROPERTY_COUNT', 'PROPERTY_BOOK', 'PROPERTY_IZD_ID')
		);

		while ($arFields = $res->GetNext()) {

			$arBookID[] = $arFields['PROPERTY_BOOK_VALUE'];

			if (!is_array($arBooks[$arFields['PROPERTY_IZD_ID_VALUE']])) {
				$arBooks[$arFields['PROPERTY_IZD_ID_VALUE']] = array(
					'BOOKS' => array(),
					'IZD_NAME' => get_izd_name($arFields['PROPERTY_IZD_ID_VALUE']),
					'SUM' => 0,
					'COUNT' => 0
				);
			}

			$sum = $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

			$arBooks[$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_VALUE']] = array(
				'PRICE' => $arFields['PROPERTY_PRICE_VALUE'],
				'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
				'SUM' => $arFields['PROPERTY_PRICE_VALUE'] * $arFields['PROPERTY_COUNT_VALUE']
			);

			$strCount++;

			$sumAll += $sum;
			$arBooks[$arFields['PROPERTY_IZD_ID_VALUE']]['SUM'] += $sum;

			$countAll += $arFields['PROPERTY_COUNT_VALUE'];
			$arBooks[$arFields['PROPERTY_IZD_ID_VALUE']]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
		}

		$arBookInfo = getBookInfo($arBookID);

//		uasort($arBooks, 'cmp');

//test_out($arBookInfo);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

		// Заполняем статичные поля
		$objPHPExcel->getActiveSheet()
			->setCellValue('A2', 'Учебная литература в корзине школы')
			->setCellValue('A4', $arSchool['FULL_NAME'])
			->setCellValue('A6', ' ')
			->setCellValue('C13', 'ИТОГО В КОРЗИНЕ:')
			->setCellValue('D13', $countAll)
			->setCellValue('F13', $sumAll);

		// Добавляем строки при необходимости
		$strCount += (count($arBooks) > 1 ? count($arBooks)*2 + count($arBooks)-1 : 1);
		$addCount = $strCount > 4 ? $strCount - 4 : 0;	// Считаем, сколько строк надо добавить
		if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(11, $addCount);

		// Заполняем табличную часть
		$strNum = 0;
		$bookNum = 0;
		$sheet = $objPHPExcel->getActiveSheet();

		foreach ($arBooks as $arIzd) {

			$sheet
				->setCellValueByColumnAndRow(0, $strNum+9, '***')
				->setCellValueByColumnAndRow(1, $strNum+9, '********')
				->setCellValueByColumnAndRow(2, $strNum+9, 'ИЗДАТЕЛЬСТВО ' . $arIzd['IZD_NAME'])
				->setCellValueByColumnAndRow(3, $strNum+9, '********')
				->setCellValueByColumnAndRow(4, $strNum+9, '********')
				->setCellValueByColumnAndRow(5, $strNum+9, '********');

			$strNum++;

			foreach ($arIzd['BOOKS'] as $bookID => $arBook) {
				$objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow(0, $strNum+9, $bookNum+1)
					->setCellValueByColumnAndRow(1, $strNum+9, $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'])
					->setCellValueByColumnAndRow(2, $strNum+9, $arBookInfo[$bookID]['~NAME'])
					->setCellValueByColumnAndRow(3, $strNum+9, $arBook['COUNT'])
					->setCellValueByColumnAndRow(4, $strNum+9, $arBook['PRICE'])
					->setCellValueByColumnAndRow(5, $strNum+9, $arBook['SUM']);
				$strNum++;
				$bookNum++;
			}

			if (count($arBooks) > 1) {
				$sheet
					->setCellValueByColumnAndRow(0, $strNum+9, '***')
					->setCellValueByColumnAndRow(1, $strNum+9, '********')
					->setCellValueByColumnAndRow(2, $strNum+9, 'ИТОГО ПО ИЗДАТЕЛЬСТВУ ' . $arIzd['IZD_NAME'])
					->setCellValueByColumnAndRow(3, $strNum+9, $arIzd['COUNT'])
					->setCellValueByColumnAndRow(4, $strNum+9, '********')
					->setCellValueByColumnAndRow(5, $strNum+9, $arIzd['SUM']);
				$strNum += 2;
			}
		}

		// Готовим браузер клиента к загрузке файла
		header('Content-Type: application/vnd.ms-excel');
		header ("Accept-Ranges: bytes");
		header ("Content-Disposition: attachment; filename=" . "order_" . $orderNum . ".xls");

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');

	}
}

// Дополнительная функция для сортировки
function cmp($a, $b) {
    if ($a['FP_CODE'] == $b['FP_CODE']) {
        return 0;
    }
    return ($a['FP_CODE'] < $b['FP_CODE']) ? -1 : 1;
}

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>