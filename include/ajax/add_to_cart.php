<?
/********************************************
* Добавление учебника в корзину или отчет пользователя
*
* Параметры (передаются через POST)
*    USER  - ID пользователя
*    BOOK  - ID учебника
*    COUNT - количество
*    MODE  - CART - корзина, REPORT - отчет
* Возвращает массив:
*    count - количество НАИМЕНОВАНИЙ в корзине
*    sum   - общая сумма учебников в корзине
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

// Обработка параметра
$user_id = ($_POST['USER'] ? intval($_POST['USER']) : 0);
$book_id = ($_POST['BOOK'] ? intval($_POST['BOOK']) : 0);
$count = ($_POST['COUNT'] ? intval($_POST['COUNT']) : 0);

// Получаем данные рабочего периода
$arPeriod = getWorkPeriod();

if (is_array($arPeriod) && $user_id && $book_id && $count && ($_POST['MODE'] == 'CART' || $_POST['MODE'] == 'REPORT')) {
	if(CModule::IncludeModule('iblock')) {

		$result = array();

		$school_id = getSchoolID($user_id);

		$status = $_POST['MODE'] == 'CART' ? 'oscart' : 'osreport';

		// Ищем этот учебник в корзине пользователя
		$cnt = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => $status, 'PROPERTY_SCHOOL_ID' => $school_id, 'PROPERTY_BOOK' => $book_id),
			array(), false,
			array('IBLOCK_ID', 'ID')
		);

		// Если нет - добавляем
		if ($cnt == 0) {

			// Записываем в корзину или отчет
			if ($arFields = getBookInfo($book_id)) {

					$price = getPrice($book_id);

					$arCart = array(
					'MODIFIED_BY' => $user_id,
					'IBLOCK_SECTION_ID' => false,
					'IBLOCK_ID' => 9,
					'NAME' => $arFields['~NAME'],
					'ACTIVE' => Y,
					'PROPERTY_VALUES' => array(
						'ORDER_NUM' => '',
						'STATUS' => $status,
						'SCHOOL_ID' => $school_id,
						'BOOK' => $book_id,
						'IZD_ID' => $arFields['IBLOCK_SECTION_ID'],
						'COUNT' => $count,
						'PRICE' => ($status == 'osreport' ? 0 : $price),
						'NDS' => ($arFields['PROPERTY_NDS_VALUE'] == 18 ? 18 : 10),
						'PRICE_KATALOG' => $price,
						'PERIOD' => $arPeriod['ID'],
						'REGION' => getUserRegion($USER->GetID())//$arFields['PROPERTY_REGION_VALUE']
					)
				);

				$el = new CIBlockElement;
				$new_id = $el->Add($arCart);
				$result['count_book'] = $count . ' ед.';
			}
		}

		// Считаем заказы школы со статусом "КОРЗИНА" или "ОТЧЕТ"
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => $status, 'PROPERTY_SCHOOL_ID' => $school_id),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS', 'PROPERTY_SCHOOL_ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE')
		);
		$result['sum'] = 0;
		$result['count'] = 0;
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