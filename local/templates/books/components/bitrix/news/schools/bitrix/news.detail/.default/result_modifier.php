<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

	$arResult['OBLAST_NAME'] = getRegionName($arResult['PROPERTIES']['OBLAST']['VALUE']);
	$arResult['MUN_NAME'] = get_obl_name($arResult['PROPERTIES']['MUN']['VALUE']);

	// Определяем режим доступа (админ школы - редактирование реквизитов,
	// админ системы и муниципалитета - редактирование привязки к муниципалитету
	// Все остальные - нет доступа
	$arResult['ACCESS_MODE'] = 0;
	if (is_user_in_group(6)) {			// Админ системы
//		$arResult['ACCESS_MODE'] = 3;
		$arResult['ACCESS_MODE'] = 1;
	} elseif (is_user_in_group(9)) {	// Оператор
		$arResult['ACCESS_MODE'] = 1;
	} else {
		$arUserMuns = get_munID_list($USER->GetID());
		if (is_user_in_group(7) && in_array($arResult['PROPERTIES']['MUN']['VALUE'], $arUserMuns)) { // Админ муниципалитета
//			$arResult['ACCESS_MODE'] = 2;
			$arResult['ACCESS_MODE'] = 1;
		} elseif (is_user_in_group(8) && in_array($USER->GetID(), $arResult['PROPERTIES']['ADMIN']['VALUE'])) { // админ школы
			$arResult['ACCESS_MODE'] = 1;
		}
	}

	if ($arResult['ACCESS_MODE']) $arResult['MUN_LIST'] = get_mun_list($USER->GetID());

	$arResult['ADMINS'] = array();
	foreach ($arResult['PROPERTIES']['ADMIN']['VALUE'] as $value) {
		$arResult['ADMINS'][] = get_user_info($value);
	}

	$arResult['STATUS_SPR'] = getSchoolStatusSpr();
	$arResult['TYPE_SPR'] = getSchoolTypeSpr();

	$arResult['CAN_ADD_ADMIN'] = ($arResult['ID'] == 11555) || !is_array($arResult['PROPERTIES']['ADMIN']['VALUE']) && is_admin($arResult['ID']);

//if ($arResult['ID'] = 11555)
//	echo '<pre>'.print_r($arResult['PROPERTIES'],1).'</pre>';

?>