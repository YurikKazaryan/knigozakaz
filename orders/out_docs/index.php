<?
/**************************************************
* Формирование пакета документов по номеру договора
*
* Параметры (передаются через GET)
*    order_id - идентификатор договора
*********************************************/
// Подключаем API Битрикса и свою библиотеку
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

if (intval($_GET['order_id']) > 0) {

	$schoolByOrder = getSchoolByOrder($_GET['order_id']);

	$arOrderInfo = getOrderInfo($_GET['order_id']);

	$orderNum = $arOrderInfo['SCHOOL_ID'] . '-' . $arOrderInfo['ORDER_NUM'];

	//print_r($arOrderInfo);
	// Проверяем права (исключаем подстановку параметров)
	if ((CSite::InGroup(array(8)) && get_schoolID($USER->GetID()) == $schoolByOrder) ||	is_admin($schoolByOrder)) {

		// Формируем приложение

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

		$fNameZip = iconv('utf-8', 'cp1251', $fName);

		// Создаем архив
		$zip = new ZipArchive();
		$zip_name = $fName . '_Zakaz_'. $orderNum . '.zip';
		$zip_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';

		//echo $zip_path;

		if ($zip->open($zip_path . $zip_name, ZIPARCHIVE::CREATE) !== true) {
			die();
		} else {

			if ($dogovor = get_dogovor($_GET['order_id'])) $zip->addFromString($fNameZip . '_' . $orderNum . iconv('utf-8', 'cp1251', '_договор') . '.rtf', $dogovor);		// Формируем договор

			if ($spec = getSpecification($_GET['order_id'])) $zip->addFromString($fNameZip . '_' . $orderNum . iconv('utf-8', 'cp1251', '_спец') . '.xlsx', $spec);			// Формируем спецификацию

			if ($pril = getSpecification($_GET['order_id'], true)) $zip->addFromString($fNameZip . '_' . $orderNum . iconv('utf-8', 'cp1251', '_приложение') . '.xlsx', $pril);	// Формируем приложение

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
}

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>