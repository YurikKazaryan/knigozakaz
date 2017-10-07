<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корректировка отчетов");?>
<?
/*********************************************
* Корректировка пустых цен каталога в отчетах
*********************************************/
if (1 && $USER->IsAdmin()) {

	//******* ПАРАМЕТРЫ КОРРЕКТИРОВКИ ******************
	$periodID = 63890;  // Рабочий период
	$regionID = 56;		// Регион
	//**************************************************

	// Получаем список учебников из отчета с пустыми ценами каталога
	// формируем фильтр ID учебников для коррекции и список id для корректировки
	$arBoookID = array();
	$arCorrect = array();
	$res = CIBlockElement::GetList(
		false,
		array(
			'IBLOCK_ID' => 9,
			'PROPERTY_PERIOD' => $periodID,
			'PROPERTY_REGION_ID' => $regionID,
			'PROPERTY_STATUS' => array('osreport', 'osrepready'),
			'PROPERTY_PRICE_KATALOG' => false
		),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_PRICE_KATALOG', 'PROPERTY_BOOK_ID')
	);

	while ($arFields = $res->GetNext()) {
		if (!in_array($arFields['PROPERTY_BOOK_ID_VALUE'], $arBookID)) $arBookID[] = $arFields['PROPERTY_BOOK_ID_VALUE'];
		$arCorrect[] = array('ID' => $arFields['ID'], 'BOOK_ID' => $arFields['PROPERTY_BOOK_ID_VALUE']);
	}

	// Загружаем цены пропущенных книг
	if (count($arCorrect) > 0) {
		$arBookPrice = array();
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 5, 'ID' => $arBookID),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_PRICE_'.$regionID)
		);
		while ($arFields = $res->GetNext())
			$arBookPrice[$arFields['ID']] = $arFields['PROPERTY_PRICE_'.$regionID.'_VALUE'];
	}

	echo 'Всего строк для коррекции: ' . count($arCorrect) . '<br>';
	echo 'Всего учебников для коррекции: ' . count($arBookPrice) . '<br>';

	// Корректируем
	foreach ($arCorrect as $arItem)
		CIBlockElement::SetPropertyValueCode($arItem['ID'], 'PRICE_KATALOG', $arBookPrice[$arItem['BOOK_ID']]);

}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>