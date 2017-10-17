<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?

/**********************************************************
* Перезапись ключей в заказах из каталога
**********************************************************/

$arIzdID = array(107);

global $USER;
if (0 && $USER->IsAdmin() && CModule::IncludeModule('iblock')) {

	$arKey = array();
	$arFilter = array();
	$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 5, 'SECTION_ID' => $arIzdID), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_KEY'));
	while ($arFields = $res->GetNext()) {
		$arFilter[] = $arFields['ID'];
		$arKey[$arFields['ID']] = $arFields['PROPERTY_KEY_VALUE'];
	}

	// Меняем в 9 ИБ
	$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_BOOK_ID' => $arFilter), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_BOOK_ID'));
	while ($arFields = $res->GetNext()) {
		CIBlockElement::SetPropertyValuesEx($arFields['ID'], 9, array('KEY' => $arKey[$arFields['PROPERTY_BOOK_ID_VALUE']]));
	}

}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>