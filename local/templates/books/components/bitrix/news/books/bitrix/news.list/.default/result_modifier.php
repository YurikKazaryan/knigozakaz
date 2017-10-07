<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	switch ($arParams['PARENT_SECTION']) {
		case 0: $arResult['IZD_NAME'] = 'Все издательства'; break;
		case 180: $arResult['IZD_NAME'] = get_izd_name($arParams['PARENT_SECTION']); break;
		default: $arResult['IZD_NAME'] = 'Издательство '.get_izd_name($arParams['PARENT_SECTION']);
	}

	// Режим отчета/заказа
	$arResult['REPORT_MODE'] = get_report_mode();

	$arOptions = getOptions();

	// Показ цен
	$arResult['SHOW_PRICE'] = $USER->IsAuthorized();

	//Массив ID для запроса цен
	$arBookID = array();

	foreach ($arResult['ITEMS'] as $key => $arItem) {

		$arBookID[] = $arItem['ID'];

		// Форматируем цену
//		$arResult['ITEMS'][$key]['PROPERTIES']['PRICE']['VALUE'] = sprintf('%0.2f руб', $arItem['PROPERTIES']['PRICE']['VALUE']);

		// Издательство
		$arResult['ITEMS'][$key]['IZD_NAME'] = get_izd_name($arItem['IBLOCK_SECTION_ID']);

		// Показ цены
		$arResult['ITEMS'][$key]['SHOW_PRICE'] =
			$USER->IsAuthorized() &&
			($arOptions['SHOW_CAT_PRICE']['VALUE'] || (!$arOptions['SHOW_CAT_PRICE']['VALUE'] && in_array($arItem['IBLOCK_SECTION_ID'], $arOptions['SHOW_CAT_PRICE_EX']['VALUE'])));
	}

	// Запрашиваем цену
	$arPrice = getPrice($arBookID);

	if ($arPrice !== false && !is_array($arPrice)) $arPrice = array($arBookID[0] => $arPrice);

	// Форматируем цены и прописываем в каталог
	foreach ($arResult['ITEMS'] as $key => $arItem)
		$arResult['ITEMS'][$key]['PRICE'] = sprintf('%0.2f руб.', $arPrice[$arItem['ID']]);

	// Проверяем, является ли пользователь админом школы
	if ($arResult['SCHOOL_ADMIN'] = is_user_in_group(8)) {

		// Запрашиваем список текущего заказа или отчета школы
		$arBooksCart = array();
		$arBooksReport = array();
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_STATUS' => array('oscart', 'osreport'), 'PROPERTY_SCHOOL_ID' => get_schoolID($USER->GetID())),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS', 'PROPERTY_SCHOOL_ID', 'PROPERTY_COUNT', 'PROPERTY_BOOK', 'PROPERTY_PRICE')
		);

		while ($arFields = $res->GetNext()) {
			if ($arFields['PROPERTY_STATUS_VALUE'] == 'oscart')
				$arBooksCart[$arFields['PROPERTY_BOOK_VALUE']] = array('COUNT' => $arFields['PROPERTY_COUNT_VALUE'], 'PRICE' => $arFields['PROPERTY_PRICE_VALUE']);
			else
				$arBooksReport[$arFields['PROPERTY_BOOK_VALUE']] = array('COUNT' => $arFields['PROPERTY_COUNT_VALUE'], 'PRICE' => $arFields['PROPERTY_PRICE_VALUE']);
		}

		foreach ($arResult['ITEMS'] as $key => $arItem) {
			if ($arBooksCart[$arItem['ID']]) {
				$arResult['ITEMS'][$key]['DELETE'] = true;
				$arResult['ITEMS'][$key]['DELETE_COUNT'] = $arBooksCart[$arItem['ID']]['COUNT'] . ' ед.';
				$arResult['ITEMS'][$key]['DELETE_SUM'] = sprintf('%1.2f руб', $arBooksCart[$arItem['ID']]['COUNT'] * $arBooksCart[$arItem['ID']]['PRICE']);
			}
			if ($arBooksReport[$arItem['ID']]) {
				$arResult['ITEMS'][$key]['REPORT_DELETE'] = true;
				$arResult['ITEMS'][$key]['REPORT_DELETE_COUNT'] = $arBooksReport[$arItem['ID']]['COUNT'] . ' ед.';
				$arResult['ITEMS'][$key]['REPORT_DELETE_SUM'] = sprintf('%1.2f руб', $arBooksReport[$arItem['ID']]['COUNT'] * $arBooksReport[$arItem['ID']]['PRICE']);
			}
		}
	}

?>