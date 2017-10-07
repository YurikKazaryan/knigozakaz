<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Пересчет НДС в спсике заказов исходя из инфоблока "Заказы"

if (1 || $USER->GetID() != 1) LocalRedirect('/');

CModule::IncludeModule('iblock');

// Список ID заказов для пересчета 
$arList = array(194085, 211240, 211267, 205553, 200162, 211965, 203504, 190251, 198577, 204829, 206540);

$arResult = array();

$res = CIBlockElement::GetList(
	array('PROPERTY_ORDER_NUM' => 'asc'),
	array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $arList),
	false, false,
	array('IBLOCK_ID', 'ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_NDS', 'PROPERTY_ORDER_NUM')
);
while ($arFields = $res->Fetch()) {
	if (!isset($arResult[$arFields['PROPERTY_ORDER_NUM_VALUE']])) $arResult[$arFields['PROPERTY_ORDER_NUM_VALUE']] = array('NDS_10' => 0, 'NDS_18' => 0, 'SUM' => 0, 'OLD_NDS_10' => 0, 'OLD_NDS_18' => 0, 'OLD_SUM' => 0);

	if ($arFields['PROPERTY_NDS_VALUE'] == 18)
		$arResult[$arFields['PROPERTY_ORDER_NUM_VALUE']]['NDS_18'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
	else
		$arResult[$arFields['PROPERTY_ORDER_NUM_VALUE']]['NDS_10'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

	$arResult[$arFields['PROPERTY_ORDER_NUM_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
}

$res = CIBlockElement::GetList(
	array('ID' => 'asc'),
	array('IBLOCK_ID' => 11, 'ID' => $arList),
	false, false,
	array('IBLOCK_ID', 'ID', 'PROPERTY_SUM', 'PROPERTY_SUM_10', 'PROPERTY_SUM_18')
);
while ($arFields = $res->Fetch()) {
	$arResult[$arFields['ID']]['OLD_SUM'] = $arFields['PROPERTY_SUM_VALUE'];
	$arResult[$arFields['ID']]['OLD_NDS_10'] = $arFields['PROPERTY_SUM_10_VALUE'];
	$arResult[$arFields['ID']]['OLD_NDS_18'] = $arFields['PROPERTY_SUM_18_VALUE'];
}

test_print($arResult);

//foreach ($arResult as $key => $value) {
//	CIBlockElement::SetPropertyValueCode($key, 'SUM_10', $value['NDS_10']);
//	CIBlockElement::SetPropertyValueCode($key, 'SUM_18', $value['NDS_18']);
//}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>