<?
/********************************************************************
* Возвращает тело для SELECT - выбор подкатегории для издательства
*
* Параметры (передаются через POST)
*    IZD - ID издательства, для которого грузим подкатегории
* Возвращает:
*	error = 1 - ошибка
*   empty = 1 - если подкатегорий нет
*   body - список option
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$result = array('error' => 1, 'empty' => 1, 'body' => '');

$izdID = intval($_POST['IZD']);

if($izdID && CModule::IncludeModule('iblock')) {
	
	$res = CIBlockElement::GetList(
		array('sort' => 'asc', 'name' => 'asc'),
		array('IBLOCK_ID' => 36, 'PROPERTY_IZD' => $izdID),
		false, false,
		array('IBLOCK_ID', 'ID', 'NAME')
	);
	while ($arFields = $res->GetNext()) {
		$result['body'] .= '<option value="' . $arFields['ID'] . '">' . $arFields['~NAME'] . '</option>';
	}

	if (strlen($result['body'])) {
		$result['body'] =
			'<label>Подраздел</label>' .
			'<select class="form-control" name="IZD_SUBSECTION">' .
			'<option value="">- Не выбрано -</option>' .
			$result['body'] .
			'</select>';
		$result['empty'] = 0;
	} else {
		$result['empty'] = 1;
	}
	$result['error'] = 0;
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>