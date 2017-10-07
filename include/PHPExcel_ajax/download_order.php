<?
/***************************************
* Выгрузка одного заказа в формате Excel
*
* Параметры (передаются через GET)
*    ORDER_ID  - ID заказа
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

$templateFile = $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel_ajax/' . 'template_order.xls';

// Обработка параметра
$orderID = intval(trim($_GET['ORDER_ID']));

if ($USER->IsAuthorized() && $orderID && CModule::IncludeModule('iblock') && file_exists($templateFile)) {

	// Загружаем данные заказа
	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 11, 'ID' => $orderID),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_ORDER_NUM'));
	if ($arFields = $res->GetNext()) {
		$izdID = $arFields['PROPERTY_IZD_ID_VALUE'];
		$schoolID = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
		$orderNum = $schoolID . '-' . $arFields['PROPERTY_ORDER_NUM_VALUE'];
	}

	$arSchool = get_school_info($schoolID);

	// если текущий пользователь - админ школы, муниципалитета или области - формируем файл
	if (is_admin($schoolID) || in_array($USER->GetID(), $arSchool['ADMIN'])) {

		// Формируем массив со списком книг
		$arBooks = array();
		$sumAll = 0;
		$countAll = 0;

		$arBookID = array();
	
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $orderID),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_PRICE', 'PROPERTY_COUNT', 'PROPERTY_BOOK', 'PROPERTY_IZD_ID')
		);
		while ($arFields = $res->GetNext()) {
			$arBookID[] = $arFields['PROPERTY_BOOK_VALUE'];
			$arBooks[$arFields['PROPERTY_BOOK_VALUE']] = array(
				'PRICE' => $arFields['PROPERTY_PRICE_VALUE'],
				'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
				'SUM' => $arFields['PROPERTY_PRICE_VALUE'] * $arFields['PROPERTY_COUNT_VALUE'],
				'IZD_NAME' => get_izd_name($arFields['PROPERTY_IZD_ID_VALUE'])
			);
			$sumAll += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
			$countAll += $arFields['PROPERTY_COUNT_VALUE'];
		}

//test_out($arBookID);

		$arBookInfo = getBookInfo($arBookID);

//test_out($arBookInfo);

		foreach ($arBooks as $key => $arBook) {
			$arBooks[$key]['NAME'] = $arBookInfo[$key]['~PROPERTY_TITLE_VALUE'];
			$arBooks[$key]['AUTHOR'] = $arBookInfo[$key]['~PROPERTY_AUTHOR_VALUE'];
			$arBooks[$key]['FP_CODE'] = $arBookInfo[$key]['PROPERTY_FP_CODE_VALUE'];
			$arBooks[$key]['PRIM'] = $arBookInfo[$key]['PROPERTY_PRIM_VALUE'];
		}

		uasort($arBooks, 'cmp');

//test_out($arBooks);

		// Загружаем таблицу-шаблон
		$objPHPExcel = PHPExcel_IOFactory::load($templateFile);

		// Заполняем статичные поля
		$objPHPExcel->getActiveSheet()
			->setCellValue('A2', 'Заказ № ' . $orderNum)
			->setCellValue('A4', html_entity_decode(html_entity_decode($arSchool['FULL_NAME'])))
			->setCellValue('A6', 'Издательство ' . get_izd_name($izdID))
			->setCellValue('D13', $countAll)
			->setCellValue('F13', $sumAll);

		// Добавляем строки при необходимости
		$countBooks = count($arBooks);
		$addCount = $countBooks > 4 ? $countBooks - 4 : 0;	// Считаем, сколько строк надо добавить
		if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(11, $addCount);

		// Заполняем табличную часть
		$strNum = 0;
		foreach ($arBooks as $arBook) {

			$book_name = ($arBook['AUTHOR'] ? $arBook['AUTHOR'] . ' ' : '') . $arBook['NAME'];

			$objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow(0, $strNum+9, $strNum+1)
				->setCellValueByColumnAndRow(1, $strNum+9, $arBook['FP_CODE'])
				->setCellValueByColumnAndRow(2, $strNum+9, $book_name)
				->setCellValueByColumnAndRow(3, $strNum+9, $arBook['COUNT'])
				->setCellValueByColumnAndRow(4, $strNum+9, $arBook['PRICE'])
				->setCellValueByColumnAndRow(5, $strNum+9, $arBook['SUM']);
			$strNum++;
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