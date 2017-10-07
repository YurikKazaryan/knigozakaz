<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
if ($USER->GetID() != 1) LocalRedirect('/');

CModule::IncludeModule('iblock');

$arSchool = array();

$res = CIBlockElement::GetList(
	false,
	array('IBLOCK_ID' => IB_ORDERS_LIST, 'PROPERTY_IZD_ID' => 107),
	false, false,
	array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID')
);
while ($arFields = $res->Fetch())
	$arSchool[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];

echo 'Всего школ с заказами для Бинома: ' . count($arSchool) . '<br>';

// Список статусов
$arTemp = getSchoolTypeSpr();
$arStatus = array();
foreach ($arTemp as $stat)
	$arStatus[] = $stat['VALUE'];

$arResult = array();

test_print($arStatus);

// Проверяем на заполнение статуса
$res = CIBlockElement::GetList(
	array('name' => 'asc'),
	array('IBLOCK_ID' => 10, 'ID' => $arSchool),
	false, false,
	array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS', 'NAME')
);
while ($arFields = $res->GetNext()) {
	if (!in_array($arFields['PROPERTY_STATUS_VALUE'], $arStatus))
		$arResult[] = array(
			'ID' => $arFields['ID'],
			'NAME' => $arFields['~NAME']
		);
}

echo 'Из них не заполнен статус: ' . count($arResult) . '<br>';

foreach ($arResult as $arSch)
	echo $arSch['ID'] . ' - ' . $arSch['NAME'] . '<br>';

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>