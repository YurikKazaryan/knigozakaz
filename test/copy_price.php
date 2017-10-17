<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>

<?

if (0 && $USER->IsAdmin()) {

	// Получаем список учебников кроме фильтра
	$arIzd = array(105, 112, 5, 121, 101, 114, 108, 116, 100, 119);

	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 5, '!SECTION_ID' => $arIzd),
		false, false,
		array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'PROPERTY_PRICE')
	);

	while ($arFields = $res->GetNext())
		CIBlockElement::SetPropertyValuesEx($arFields['ID'], 5, array('PRICE_144' => $arFields['PROPERTY_PRICE_VALUE']));

}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>