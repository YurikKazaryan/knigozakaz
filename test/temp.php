<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?

global $USER;

if (false && $USER->IsAdmin() && CModule::IncludeModule('iblock')) {

	$arID = array(9422, 9423, 9424, 9425, 9426, 9427, 9428, 9429, 9430, 9431);
	$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 5, 'ID' => $arID), false, false, array('IBLOCK_ID', 'ID', 'NAME'));
	$arBooks = array();
	while ($arFields = $res->GetNext())
		$arBooks[$arFields['ID']] = $arFields['NAME'];

	$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_BOOK_ID' => $arID), false, false, array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_BOOK_ID'));

	while ($arFields = $res->GetNext()) {
		echo $arFields['PROPERTY_BOOK_ID_VALUE'] . ' === ' . $arFields['NAME'] . ' === ' . $arBooks[$arFields['PROPERTY_BOOK_ID_VALUE']] . '<br>';

//		$el = new CIBlockElement;
//		$el->Update($arFields[ID], array('NAME' => $arBooks[$arFields['PROPERTY_BOOK_ID_VALUE']]));
	}


}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>