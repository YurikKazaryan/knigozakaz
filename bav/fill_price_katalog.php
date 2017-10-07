<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
if ($USER->GetID() != 1) LocalRedirect('/');

// Заполнение полей PRICE_KATALOG для отчетных записей

$arBooks = array();

$res = CIBlockElement::GetList(
	false,
	array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => array('osrep','osrepready'), 'PROPERTY_PERIOD' => 63890),
	false, false,
	array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_PRICE', 'PROPERTY_PRICE_KATALOG')
);

while ($arFields = $res->GetNext()) {
	if (!$arFields['PROPERTY_PRICE_KATALOG_VALUE']) {
		if (is_array($arBook[$arFields['NAME']])) {
		} else {
			$arBook[$arFields['NAME']] = array('PRICE' => floatval($arFields['PROPERTY_PRICE_VALUE']));
		}
	}
}



?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>