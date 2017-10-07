<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	// Составляем строку адреса для показа в списке и фильтр для проверки наличия учебников в корзинах
	$arFilter = array();
	foreach ($arResult['ITEMS'] as $key => $arItem) {
		$adr = '';
		$adr = get_obl_name($arItem['PROPERTIES']['OBLAST']['VALUE']);
		$adr .= (strlen($adr) > 0 && $arItem['PROPERTIES']['RAJON']['VALUE'] ? ', ' : '') . $arItem['PROPERTIES']['RAJON']['VALUE'];
		$adr .= (strlen($adr) > 0 && $arItem['PROPERTIES']['PUNKT']['VALUE'] ? ', ' : '') . $arItem['PROPERTIES']['PUNKT']['VALUE'];
		$adr .= (strlen($adr) > 0 && $arItem['PROPERTIES']['RAJON_GORODA']['VALUE'] ? ', ' : '') . $arItem['PROPERTIES']['RAJON_GORODA']['VALUE'];
		$adr .= (strlen($adr) > 0 && $arItem['PROPERTIES']['ULICA']['VALUE'] ? ', ' : '') . $arItem['PROPERTIES']['ULICA']['VALUE'];
		$adr .= (strlen($adr) > 0 && $arItem['PROPERTIES']['DOM']['VALUE'] ? ', ' : '') . ($arItem['PROPERTIES']['DOM']['VALUE'] ? 'д.' : '') . $arItem['PROPERTIES']['DOM']['VALUE'];
		$arResult['ITEMS'][$key]['ADR_STRING'] = $adr;

		$arFilter[] = $arItem['ID'];
	}

	// Если фильтр не пустой, проверяем наличие корзин и ставим флажки
	$arBasket = array();
	if (count($arFilter) > 0) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_SCHOOL_ID' => $arFilter, 'PROPERTY_STATUS' => 'oscart'), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_STATUS'));
		while ($arFields = $res->GetNext()) $arBasket[$arFields['PROPERTY_SCHOOL_ID_VALUE']] = true;
	}

	// Проставляем признаки наличия корзины в список школ
	foreach ($arResult['ITEMS'] as $key => $arItem) {
		$arResult['ITEMS'][$key]['BASKET'] = $arBasket[$arItem['ID']];
	}

	// Получаем список муниципалитетов для текущего пользователя
	$arResult['MUN_LIST'] = get_mun_list($USER->GetID());
?>