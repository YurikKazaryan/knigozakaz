<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	$arResult['BASKET'] = array();
	$arResult['BASKET_SUM'] = 0;
	$arResult['IS_OPER'] = is_user_in_group(8);

	$arResult['ORDER_DISABLED'] = (is_user_in_group(8) && !testPunktFZ(get_schoolID($USER->GetID())) ? 'disabled' : '');

	$schoolID = getSchoolID($USER->GetID());

	$arResult['SCHOOL_ID'] = $schoolID;

	// Составляем список ID учебников корзины
	$arBookID = array();
	foreach ($arResult['ITEMS'] as $arItem)
		$arBookID[] = $arItem['PROPERTIES']['BOOK']['VALUE'];

	$arBooks = getBookInfo($arBookID, true);

	foreach ($arResult['ITEMS'] as $key => $arItem) {

		// Прописываем данные книги из каталога
		$arItem['AUTHOR'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['~PROPERTY_AUTHOR_VALUE'];
		$arItem['TITLE'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['~PROPERTY_TITLE_VALUE'];
		$arItem['CLASS'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['PROPERTY_CLASS_VALUE'];
		$arItem['FP_CODE'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['PROPERTY_FP_CODE_VALUE'];

		if (!is_array($arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']])) {
			$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']] = array(
				'NAME' => get_izd_name($arItem['PROPERTIES']['IZD_ID']['VALUE']),
				'SUM' => 0,
				'COUNT' => 0,
				'CAN_ORDER' => canMakeOrder($schoolID, $arItem['PROPERTIES']['IZD_ID']['VALUE']),
				'BOOKS' => array()
			);
		}

		$arResult['BASKET_SUM'] += $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE'];
		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['SUM'] += $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE'];
		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['COUNT'] += $arItem['PROPERTIES']['COUNT']['VALUE'];

		$arItem['SUM'] = sprintf('%.2f руб.', $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE']);
		$arItem['PRICE'] = sprintf('%.2f руб.', $arItem['PROPERTIES']['PRICE']['VALUE']);

		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['BOOKS'][] = $arItem;

	}

	foreach($arResult['BASKET'] as $key => $value)
		$arResult['BASKET'][$key]['SUM'] = sprintf('%.2f руб.', $value['SUM']);

	$arResult['BASKET_SUM'] = sprintf('%.2f руб.', $arResult['BASKET_SUM']);
?>