<?
/********************************************
* Сохранение комментария опреатора к заказу
*
* Параметры (передаются через POST)
*    ORDER_ID - ID заказа
*    TEXT - Текст комментария
* Если TEXT пустой - комментарий удаляется
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметров
$order_id = ($_POST['ORDER_ID'] ? intval($_POST['ORDER_ID']) : 0);
$rem_text = trim($_POST['TEXT']);

// Записывать комменты может только оператор
if ($order_id && is_user_in_group(9)) {
	if(CModule::IncludeModule('iblock')) {
		// Если текст не пустой - формируем запись
		if (strlen($rem_text) > 0)	$rem_text = time() . '@@@' . $USER->GetFullName() . '@@@' . $rem_text;

		// Записываем в базу
		CIBlockElement::SetPropertyValuesEx($order_id, 11, array('OPER_REM' => $rem_text));

		$result = array('delete' => strlen($rem_text) > 0 ? 'N' : 'Y', 'order_id' => $order_id);
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>