<?
/********************************************
* Изменение даты поставки в заказе
*
* Параметры (передаются через POST)
*    ORDER_ID  - ID заказа
*    DPOST  - дата в формате ДД.ММ.ГГГГ
* Возвращает массив:
*    name - Новая дата в формате ДД.ММ.ГГГГ
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

// Обработка параметров
$order_id = ($_POST['ORDER_ID'] ? intval($_POST['ORDER_ID']) : 0);

$temp = trim($_POST['DPOST']);

if (strlen($temp) > 0) {
	// Определяем разделитель (точка, / или -)
	$delim = (strpos($temp, '.') === false ? (strpos($temp, '/') === false ? (strpos($temp, '-') === false ? '' : '-') : '/') : '.');

	$arDate = explode($delim, $temp);
	$dpost = mktime(0, 0, 0, $arDate[1], $arDate[0], $arDate[2]);
}

if (strlen($temp) == 0 || ($delim && $dpost !== false)) {

	if(CModule::IncludeModule('iblock')) {
		if (strlen($temp) == 0) {
			CIBlockElement::SetPropertyValuesEx($order_id, 11, array('DATAPOSTAVKI' => ''));
			$result = array('name' => '<span class="error">НЕ УКАЗАНА!</span>');
		} else {
			CIBlockElement::SetPropertyValuesEx($order_id, 11, array('DATAPOSTAVKI' => ConvertTimeStamp($dpost, 'SHORT')));
			$result = array('name' => date('d.m.Y', $dpost));
		}
	}

} else {
	$result = array('name' => 'Ошибка');
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>