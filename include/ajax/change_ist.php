<?
/********************************************
* Изменение источника финансорования в заказе
*
* Параметры (передаются через POST)
*    ORDER_ID  - ID заказа
*    IST  - код источника
* Возвращает массив:
*    name - Наименование нового источника
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

// Обработка параметра
$order_id = ($_POST['ORDER_ID'] ? intval($_POST['ORDER_ID']) : 0);
$ist = trim($_POST['IST']);

$arIst = get_istochnik_spr();
$arIst['none'] = array('SHORT' => '<span class="error">НЕ УКАЗАН!</span>');

if ($order_id && $arIst[$ist]) {

	if(CModule::IncludeModule('iblock')) {
		if ($ist == 'none') {
			CIBlockElement::SetPropertyValuesEx($order_id, 11, array('ISTOCHNIK' => ''));
		} else {
			CIBlockElement::SetPropertyValuesEx($order_id, 11, array('ISTOCHNIK' => $ist));
		}
		$result = array('name' => $arIst[$ist]['SHORT']);
	}
} else {
	$result = array('name' => 'Ошибка');
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>