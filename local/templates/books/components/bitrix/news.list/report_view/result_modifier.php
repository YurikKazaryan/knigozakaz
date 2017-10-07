<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	$arResult['BASKET'] = array();
	$arResult['BASKET_SUM'] = 0;

	if (isset($_GET['back'])) $arResult['back_url'] = $_GET['back'];

	foreach ($arResult['ITEMS'] as $key => $arItem) {

		if (!is_array($arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']])) {
			$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']] = array('NAME' => get_izd_name($arItem['PROPERTIES']['IZD_ID']['VALUE']), 'SUM' => 0, 'COUNT' => 0, 'BOOKS' => array());
		}

		if (!isset($arResult['SCHOOL_NAME'])) $arResult['SCHOOL_NAME'] = get_school_name_by_id($arItem['PROPERTIES']['SCHOOL_ID']['VALUE']);

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