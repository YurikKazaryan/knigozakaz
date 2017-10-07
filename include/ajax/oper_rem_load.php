<?
/********************************************
* Загрузка комментария опреатора к заказу
*
* Параметры (передаются через POST)
*    ORDER_ID - ID заказа
* Возвращает массив:
*    author - автор комментария
*    date   - дата создания
*    text   - текст комментария
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметра
$order_id = ($_POST['ORDER_ID'] ? intval($_POST['ORDER_ID']) : 0);

if ($order_id && (is_user_in_group(9) || is_user_in_group(6) || is_user_in_group(7))) {
	if(CModule::IncludeModule('iblock')) {

		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 11, 'ID' => $order_id),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_OPER_REM')
		);

		if ($arFields = $res->GetNext()) {
			$arTemp = explode('@@@', $arFields['PROPERTY_OPER_REM_VALUE']['TEXT']);
			if (strlen(trim($arTemp[2])) > 0) {
				$result = array(
					'author' => $arTemp[1],
					'date' => date('d.m.Y H:i', $arTemp[0]),
					'order_id' => $order_id,
					'text' => $arTemp[2]
				);
				if (is_user_in_group(7)) {
					$result['readonly'] = true;
				}
			} else {
				if (is_user_in_group(9))
					$result = array(
						'author' => $USER->GetFullName(),
						'date' => date('d.m.Y H:i'),
						'order_id' => $order_id,
						'text' => ''
					);
				else
					$result = array(
						'author' => 'Ошибка',
						'date' => 'Ошибка',
						'order_id' => 0,
						'text' => 'Ошибка'
					);
			}
		}
	}
} else {
	$result = array('author' => 'Ошибка', 'date' => 'Ошибка', 'text' => 'Ошибка');
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>