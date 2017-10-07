<?
/********************************************
* Удаление учебника из корзины или заказка пользователя
*
* Параметры (передаются через POST)
*    USER  - ID пользователя
*    BOOK  - ID учебника
*    MODE  - режим, CART - корзина, REPORT - отчет
* Возвращает массив:
*    count - количество НАИМЕНОВАНИЙ в корзине
*    sum   - общая сумма учебников в корзине
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметра
$user_id = ($_POST['USER'] ? intval($_POST['USER']) : 0);
$book_id = ($_POST['BOOK'] ? intval($_POST['BOOK']) : 0);

// Получаем данные рабочего периода
$arPeriod = getWorkPeriod();

if (is_array($arPeriod) && $user_id && $book_id && ($_POST['MODE'] == 'CART' || $_POST['MODE'] == 'REPORT')) {
	if(CModule::IncludeModule('iblock')) {

		$school_id = get_schoolID($user_id);

		$status = ($_POST['MODE'] == 'CART' ? 'oscart' : 'osreport');

		// Ищем этот учебник в корзине пользователя
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => $status, 'PROPERTY_SCHOOL_ID' => $school_id, 'PROPERTY_BOOK' => $book_id),
			false, false,
			array('IBLOCK_ID', 'ID')
		);

		// Если есть - удаляем
		if ($arFields = $res->GetNext()) CIBlockElement::Delete($arFields['ID']);

		// Считаем заказы пользователя со статусом "КОРЗИНА" или "ОТЧЕТ"
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => $status, 'PROPERTY_SCHOOL_ID' => $school_id),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE')
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
	if ($arPeriod === false) $result['auth'] = 1;
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>