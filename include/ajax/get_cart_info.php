<?
/********************************************
* Получение информации о корзине пользователя или отчете
*
* Параметры (передаются через POST)
*    USER  - ID пользователя
*    MODE  - режим (CART или REPORT)
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметра
$user_id = ($_POST['USER'] ? intval($_POST['USER']) : 0);

if ($user_id && ($_POST['MODE'] == 'CART' || $_POST['MODE'] == 'REPORT')) {
	if(CModule::IncludeModule('iblock')) {

		$school_id = get_schoolID($user_id);

		// Выбираем заказы пользователя со статусом "КОРЗИНА" или "ДЛЯ ОТЧЕТА"
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => ($_POST['MODE'] == 'CART' ? 'oscart' : 'osreport'), 'PROPERTY_SCHOOL_ID' => $school_id),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS', 'PROPERTY_SCHOOL_ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE')
		);
		$result = array('sum' => 0, 'count' => 0);
		while ($arFields = $res->GetNext()) {
			$result['sum'] += $arFields['PROPERTY_PRICE_VALUE'] * $arFields['PROPERTY_COUNT_VALUE'];
			$result['count'] += $arFields['PROPERTY_COUNT_VALUE'];
		}

		// Форматируем вывод суммы
		$result['sum'] = sprintf('%1.2f руб.', $result['sum']);
	}
} else {
	$result = array('sum' => 'Ошибка');
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>