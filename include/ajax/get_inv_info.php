<?
/********************************************************************
* Возвращает информацию об инвентаризации
*
* Параметры (передаются через POST)
*    INV_ID - ID учебника
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметров
$invID = trim($_POST['INV_ID']);

$result = array('error' => 1);

if($invID && CModule::IncludeModule('iblock')) {

	$arTemp = getWorkPeriod();

	$arFilter = array('IBLOCK_ID' => 25, 'ID' => $invID);

	// Запрашиваем информацию об учебнике
	$res = CIBlockElement::GetList(
		false,
		$arFilter,
		false, false,
		array('IBLOCK_ID', 'ID', 'NAME',
			'PROPERTY_YEAR_PURCHASE',
			'PROPERTY_COUNT',
			'PROPERTY_REM',
			'PROPERTY_USE_IN_CLASS'
			//'PROPERTY_USE_NEXT',
			//'PROPERTY_USE_' . $arTemp['ID']
		)
	);

	if ($arFields = $res->GetNext()) {
		$result['year_purchase'] = $arFields['PROPERTY_YEAR_PURCHASE_VALUE'];
		$result['count'] = $arFields['PROPERTY_COUNT_VALUE'];
		$result['rem'] = $arFields['PROPERTY_REM_VALUE']['TEXT'];
		$result['use_in_class'] = $arFields['PROPERTY_USE_IN_CLASS_VALUE'];
		//$result['use_next'] = ($arFields['PROPERTY_USE_NEXT_VALUE'] == 'Y' ? 'Y' : 'N');
		//$result['use_curr'] = ($arFields['PROPERTY_USE_' . $arTemp['ID'] . '_VALUE'] == 'Y' ? 'Y' : 'N');
		$result['error'] = 0;
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>