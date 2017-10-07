<?
/******************************************
* Формирование полного общего отчёта по АИС
* Параметры (передаются через POST)
*    PAGE_NUM - номер запрашиваемой страницы
*    MODE - режим
*        1 - запрос количества строк в отчёте
*        2 - обрабокта страницы отчета
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

global $USER;

$result = array('file' => '', 'error' => true);

// Обработка параметра
$pageSize = 500;	//******************** Размер страницы запроса! ************************
$pageNum = intval($_POST['PAGE_NUM']);
$mode = intval($_POST['MODE']);

if (!in_array($mode, array(1, 2))) $mode = 0;

if (CSite::InGroup(array(1,6,7,9)) && $mode && CModule::IncludeModule('iblock')) {

	$arWorkPeriod = getWorkPeriod();

	$arFilter = array(
		'IBLOCK_ID' => IB_ORDERS,
		'PROPERTY_REGION' => getRegionFilter(),
		'PROPERTY_PERIOD' => $arWorkPeriod['ID'],
		'!PROPERTY_STATUS' => array('osreport', 'osrepready', 'oscart'),
		'PROPERTY_SCHOOL_ID' => get_schoolID_list($USER->GetID())
	);

	switch ($mode) {

		case 1:		// Считаем кол-во строк в отчёте
			$cnt = CIBlockElement::GetList(false, $arFilter, array(), false, array('IBLOCK_ID', 'ID'));
			$result['count'] = $cnt;
			$result['page_count'] = intval($cnt / $pageSize) + ($cnt - intval($cnt / $pageSize) > 0 ? 1 : 0);
			$result['error'] = false;
			COption::SetOptionInt('iblock', 'USER_'.$USER->GetID().'_REPORT_FULL_PAGE_COUNT', $result['page_count']);
			break;

		case 2:
			$arIzd = getIzdList();
			$arMun = getMunList($USER->GetID()); foreach ($arMun as $key => $name) $arMun[$key] = trim(str_replace('.', '', $name));
			$arSchool = array();
			$arBook = array();
			$arReport = array();
			$arOrders = array();
			$res = CIBlockElement::GetList(
					array('PROPERTY_SCHOOL_ID' => 'desc', 'PROPERTY_ORDER_NUM' => 'desc'),
					$arFilter,
					false, array('iNumPage' => $pageNum, 'nPageSize' => $pageSize),
					array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_STATUS', 'TIMESTAMP_X', 'PROPERTY_SCHOOL_ID', 'PROPERTY_BOOK', 'PROPERTY_ORDER_NUM')
				);
			while ($arFields = $res->Fetch()) {

				if (!in_array($arFields['PROPERTY_SCHOOL_ID_VALUE'], $arSchool)) $arSchool[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
				if (!in_array($arFields['PROPERTY_BOOK_VALUE'], $arBook)) $arBook[] = $arFields['PROPERTY_BOOK_VALUE'];
				if (!in_array($arFields['PROPERTY_ORDER_NUM_VALUE'], $arOrders)) $arOrders[] = $arFields['PROPERTY_ORDER_NUM_VALUE'];

				$arReport[] = array(
					'IZD_ID' => $arFields['PROPERTY_IZD_ID_VALUE'],
					'IZD_NAME' => $arIzd[$arFields['PROPERTY_IZD_ID_VALUE']],
					'MUN_NAME' => '',
					'SCHOOL_ID' => $arFields['PROPERTY_SCHOOL_ID_VALUE'],
					'SCHOOL_NAME' => '',
					'BOOK_ID' => $arFields['PROPERTY_BOOK_VALUE'],
					'BOOK_NAME' => '',
					'FP_CODE' => '',
					'1C_CODE' => '',
					'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
					'PRICE' => $arFields['PROPERTY_PRICE_VALUE'],
					'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
					'ORDER_NUM' => '',
					'ORDER_ID' => $arFields['PROPERTY_ORDER_NUM_VALUE'],
					'STATUS' => getStatusName($arFields['PROPERTY_STATUS_VALUE']),
					'CHANGE' => $arFields['TIMESTAMP_X'],
					'REMARKS' => ''
				);
			}

			// Получаем внешние номера заказов
			$res = CIBlockElement::GetList(
				false,
				array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $arOrders),
				false, false,
				array('IBLOCK_ID', 'ID', 'NAME')
			);
			$arOrdersNum = array();
			while ($arFields = $res->Fetch())
				$arOrdersNum[$arFields['ID']] = $arFields['NAME'];


			// Запрашиваем информацию о школах и учебниках, вошедших в выборку и прописываем их в отчёт
			$arBookInfo = getBookInfo($arBook, true);
			$arSchoolInfo = getSchoolInfoList($arSchool);
			foreach ($arReport as $key => $arStr) {
				$arReport[$key]['BOOK_NAME'] = $arBookInfo[$arStr['BOOK_ID']]['~NAME'];
				$arReport[$key]['FP_CODE'] = $arBookInfo[$arStr['BOOK_ID']]['PROPERTY_FP_CODE_VALUE'];
				$arReport[$key]['1C_CODE'] = $arBookInfo[$arStr['BOOK_ID']]['PROPERTY_CODE_1C_VALUE'];
				$arReport[$key]['SCHOOL_NAME'] = $arSchoolInfo[$arStr['SCHOOL_ID']]['NAME'];
				$arReport[$key]['MUN_NAME'] = $arMun[$arSchoolInfo[$arStr['SCHOOL_ID']]['MUN']];
				$arReport[$key]['ORDER_NUM'] = $arOrdersNum[$arStr['ORDER_ID']];
			}



			// Записываем в файл

			$allPageCount = COption::GetOptionString('iblock', 'USER_'.$USER->GetID().'_REPORT_FULL_PAGE_COUNT', '');

			if ($pageNum == 1) {			// Первый запуск

				// Создаём временный файл
				$tempFileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/user_'.$USER->GetID().'_report_full.csv';
				COption::SetOptionString('iblock', 'USER_'.$USER->GetID().'_REPORT_FULL_TEMPLATE', $tempFileName);

				$fp = fopen($tempFileName, 'w');

				// Пишем заголовки столбцов
				fwrite($fp, iconv('utf-8', 'windows-1251', 'Издательство;Муниципалитет;Школа;Наименование;Код ФП;Код 1С;Кол-во;Цена за единицу;Сумма;Номер заказа;Статус заказа;Когда изменён;Примечание'."\n"));

			} else {						// Дозаполняем таблицу

				// открываем временный файл на запись
				$tempFileName = COption::GetOptionString('iblock', 'USER_'.$USER->GetID().'_REPORT_FULL_TEMPLATE', '');
				$fp = fopen($tempFileName, 'a');

			}

			// Заполняем таблицу
			foreach ($arReport as $arStr) {
				fwrite($fp, iconv('utf-8', 'windows-1251',
					'"' . $arStr['IZD_NAME'] . '";"' .
					$arStr['MUN_NAME'] . '";"' .
					$arStr['SCHOOL_NAME'] . '";"' .
					$arStr['BOOK_NAME'] . '";"' .
					$arStr['FP_CODE'] . '";" ' .
					$arStr['1C_CODE'] . '";' .
					$arStr['COUNT'] . ';' .
					$arStr['PRICE'] . ';' .
					$arStr['SUM'] . ';"' .
					$arStr['ORDER_NUM'] . '";"' .
					$arStr['STATUS'] . '";"' .
					$arStr['CHANGE'] . '";"' .
					$arStr['REMARKS'] . '"' .
					"\n")
				);
			}

			//Закрываем файл
			fclose($fp);

			if ($allPageCount == $pageNum) {

				// Создаем архив
				$zip = new ZipArchive();
				$zip_name = 'report_full_' . $USER->GetID() . '.zip';
				$zip_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';
				if ($zip->open($zip_path . $zip_name, ZIPARCHIVE::CREATE) !== true) $result['error'] = "File create error: $zip_path$zip_name";
//				if ($zip->addFile($tempFileName)) unlink($tempFileName);
				$zip->addFile($tempFileName, 'report_full.csv');
				$zip->close();

				$result['file'] = $zip_name;



			}

			$result['error'] = false;
//			test_out($arReport);
			break;
	}


//	$result = array('file' => basename($tempFileName), 'error' => false);
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>