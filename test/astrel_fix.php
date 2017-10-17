<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корректирвоака кодов 1С Астрель");?>
<?

	// КОпирование кодов 1С для Астрель из каталога в заказы

	if (0 && $USER->IsAdmin()) {

		// Формируем список кодов 1С астрели из каталога
		$arBooks = array();
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 5, 'SECTION_ID' => 101),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_CODE_1C')
		);
		while ($arFields = $res->GetNext())
			$arBooks[$arFields['ID']] = $arFields['PROPERTY_CODE_1C_VALUE'];

		// Выбираем учебники из заказов астрели за текущий период БЕЗ кода 1С
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_PERIOD' => 63890, 'PROPERTY_IZD_ID' => 101, 'PROPERTY_REGION_ID' => 56, 'PROPERTY_CODE_1C' => false),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_CODE_1C', 'PROPERTY_PERIOD', 'PROPERTY_IZD_ID', 'PROPERTY_REGION_ID', 'PROPERTY_BOOK_ID')
		);
		while ($arFields = $res->Getnext()) {
			CIBlockElement::SetPropertyValuesEx($arFields['ID'], 9, array('CODE_1C' => $arBooks[$arFields['PROPERTY_BOOK_ID_VALUE']]));
		}

	}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>