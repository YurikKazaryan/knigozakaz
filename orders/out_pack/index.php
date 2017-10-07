<?
/**************************************************
* Формирование пакета документов по списку договоров
*
* Параметры (передаются через GET)
*    mode = 1 (скачать), или 2 (отправить)
*    orders - идентификаторы заказов через запятую
*********************************************/
// Подключаем API Битрикса и свою библиотеку
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

if ($_GET['mode'] == 1) {

	$zip = false;

	$arOrders = explode(',', $_GET['orders']);

	foreach ($arOrders as $order_id) {
		if (is_admin(get_school_by_order($order_id))) {

			$arOrderInfo = getOrderInfo($order_id);
			$orderNum = $arOrderInfo['SCHOOL_ID'] . '-' . $arOrderInfo['ORDER_NUM'];

			$schoolByOrder = getSchoolByOrder($order_id);

			// Формируем имя файла (название школы)
			$arSchool = getSchoolInfo($schoolByOrder);
			$fName = '';
			for ($i = 0; $i < strlen($arSchool['NAME']); $i++) {
				$c = substr($arSchool['NAME'], $i, 1);
				if (($c >= 'А' && $c <= 'Я') || ($c >= 'а' && $c <= 'я') || ($c >= '0' && $c <= '9'))
					$fName .= $c;
				elseif ($c == '№')
					$fName .= 'N';
				else
					$fName .= '_';
			}
			$fName = str_replace('__', '_', $fName);

			if (strlen($fName) > 20) {
				$pos = 20;
				while ((substr($fName, $pos, 1) != '_') && ($pos < strlen($fName))) $pos++;
				$fName = substr($fName, 0, $pos);
			}

			$fName = iconv('utf-8', 'cp866', $fName);

			$dogovor = get_dogovor($order_id);
			$spec = getSpecification($order_id);

			if ($zip === false) {
				// Создаем архив
				$zip = new ZipArchive();
				$zip_name = 'orders_pack_' . date('d-m-Y-H-i-s') . '.zip';
				$zip_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';
				if ($zip->open($zip_path . $zip_name, ZIPARCHIVE::CREATE) !== true) die("File create error: $zip_path$zip_name");
			}

			if (strlen($dogovor) > 0) $zip->addFromString($fName . '_' . $orderNum . iconv('utf-8', 'cp866', '_договор') . '.rtf', $dogovor);
			if (strlen($spec) > 0) $zip->addFromString($fName . '_' . $orderNum . iconv('utf-8', 'cp866', '_спец') . '.xlsx', $spec);
		}
	}

	if ($zip !== false) {
		$zip->close();
		if (file_exists($zip_path . $zip_name)) {
			// Отдаем архив
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="' . $zip_name . '"');
			readfile($zip_path . $zip_name);
			unlink($zip_path . $zip_name);
		}
	}
}

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>