<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	$arResult['BASKET'] = array();
	$arResult['BASKET_SUM'] = 0;
	$arResult['IS_OPER'] = is_user_in_group(8);

	foreach ($arResult['ITEMS'] as $key => $arItem) {

		if (!is_array($arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']])) {
			$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']] = array('NAME' => get_izd_name($arItem['PROPERTIES']['IZD_ID']['VALUE']), 'SUM' => 0, 'COUNT' => 0, 'BOOKS' => array());
		}

		$arResult['BASKET_SUM'] += $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE'];
		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['SUM'] += $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE'];
		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['COUNT'] += $arItem['PROPERTIES']['COUNT']['VALUE'];

		$arItem['SUM'] = sprintf('%.2f руб.', $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE']);
		$arItem['PRICE'] = sprintf('%.2f', $arItem['PROPERTIES']['PRICE']['VALUE']);

		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['BOOKS'][] = $arItem;

	}

	foreach($arResult['BASKET'] as $key => $value)
		$arResult['BASKET'][$key]['SUM'] = sprintf('%.2f руб.', $value['SUM']);

	$arResult['BASKET_SUM'] = sprintf('%.2f руб.', $arResult['BASKET_SUM']);

?>