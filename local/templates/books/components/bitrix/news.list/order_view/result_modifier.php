<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	$arPeriodList = getPeriodList();

	if (isset($_SESSION['ORDERS_LIST_PAGE']))
		$arResult['BACK_URL'] = '?PAGEN_1=' . $_SESSION['ORDERS_LIST_PAGE'] . ($_GET['order_id'] ? '#order' . $_GET['order_id'] : '');

	$arResult['BASKET'] = array();
	$arResult['BASKET_SUM'] = 0;

	// Составляем список ID учебников заказа
	$arBookID = array();
	foreach ($arResult['ITEMS'] as $arItem)
		$arBookID[] = $arItem['PROPERTIES']['BOOK']['VALUE'];

	$arBooks = getBookInfo($arBookID, true);

	foreach ($arResult['ITEMS'] as $key => $arItem) {

		if (!is_array($arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']])) {
			$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']] = array('NAME' => get_izd_name($arItem['PROPERTIES']['IZD_ID']['VALUE']), 'SUM' => 0, 'COUNT' => 0, 'BOOKS' => array());
		}

		// Получаем название школы (один раз)
		if (!isset($arResult['SCHOOL_NAME'])) $arResult['SCHOOL_NAME'] = get_school_name_by_id($arItem['PROPERTIES']['SCHOOL_ID']['VALUE']);

		// Название муниципалитета (один раз)
		if (!isset($arResult['MUN_NAME'])) {
			$arTemp = get_school_info($arItem['PROPERTIES']['SCHOOL_ID']['VALUE']);
			$arResult['MUN_NAME'] = get_izd_name($arTemp['MUN']);
		}

		// Прописываем данные книги из каталога
		$arItem['AUTHOR'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['~PROPERTY_AUTHOR_VALUE'];
		$arItem['TITLE'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['~PROPERTY_TITLE_VALUE'];
		$arItem['CLASS'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['PROPERTY_CLASS_VALUE'];
		$arItem['FP_CODE'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['PROPERTY_FP_CODE_VALUE'];
		$arItem['CODE_1C'] = $arBooks[$arItem['PROPERTIES']['BOOK']['VALUE']]['PROPERTY_CODE_1C_VALUE'];

		$arResult['BASKET_SUM'] += $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE'];
		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['SUM'] += $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE'];
		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['COUNT'] += $arItem['PROPERTIES']['COUNT']['VALUE'];

		$arItem['SUM'] = sprintf('%.2f руб.', $arItem['PROPERTIES']['COUNT']['VALUE'] * $arItem['PROPERTIES']['PRICE']['VALUE']);
		$arItem['PRICE'] = sprintf('%.2f руб.', $arItem['PROPERTIES']['PRICE']['VALUE']);

		$arResult['BASKET'][$arItem['PROPERTIES']['IZD_ID']['VALUE']]['BOOKS'][] = $arItem;

		if (!$arResult['READONLY']) $arResult['READONLY'] = $arPeriodList[$arItem['PROPERTIES']['PERIOD']['VALUE']]['ARCHIVE'];

	}

	foreach($arResult['BASKET'] as $key => $value)
		$arResult['BASKET'][$key]['SUM'] = sprintf('%.2f руб.', $value['SUM']);

	$arResult['BASKET_SUM'] = sprintf('%.2f руб.', $arResult['BASKET_SUM']);

?>