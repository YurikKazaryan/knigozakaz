<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/*
* $mode - режим:
*           1 - Количество
*           2 - Сумма
*           3 - Количество и Сумма
*/
function report_reestr_3($regionID, $periodID, $mode = 1) {
	global $USER;

	$arReport = array();

	$arIzd = get_izd_list(); // Список издательств

	$arIzdID = array();
	foreach ($arIzd as $key => $value)
		$arIzdID[$key] = 0;

	$allIzdCount = $arIzdID;
	$allIzdSum = $arIzdID;

	// Получаем список школ по муниципалитетам, вытаскиваем нужную инфу по школе, составляем справочник школа - муниципалитет
	$arSchoolList = array();
	$arSchools = array();
	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 10, 'PROPERTY_OBLAST' => $regionID),
		false, false,
		array(
			'IBLOCK_ID', 'ID',
			'NAME',
			'PROPERTY_MUN',
			'PROPERTY_INDEX', 'PROPERTY_OBLAST', 'PROPERTY_RAJON', 'PROPERTY_PUNKT', 'PROPERTY_ULICA', 'PROPERTY_DOM',
			'PROPERTY_DIR_FIO', 'PROPERTY_PHONE', 'PROPERTY_EMAIL', 'PROPERTY_INN'
		)
	);

	while ($arFields = $res->GetNext()) {

		$arSchools[$arFields['ID']] = $arFields['PROPERTY_MUN_VALUE'];

		// Составляем строку адреса
		$address = $arFields['PROPERTY_INDEX_VALUE'];
		$address .= (strlen($address) > 0 ? ', ' : '') . get_obl_name($arFields['PROPERTY_OBLAST_VALUE']);
		$address .= (strlen($address) > 0 && $arFields['PROPERTY_RAJON_VALUE'] ? ', ' : '') . $arFields['PROPERTY_RAJON_VALUE'];
		$address .= (strlen($address) > 0 && $arFields['PROPERTY_PUNKT_VALUE'] ? ', ' : '') . $arFields['PROPERTY_PUNKT_VALUE'];
		$address .= (strlen($address) > 0 && $arFields['PROPERTY_ULICA_VALUE'] ? ', ' : '') . $arFields['PROPERTY_ULICA_VALUE'];
		$address .= (strlen($address) > 0 && $arFields['PROPERTY_DOM_VALUE'] ? ', ' : '') . ($arFields['PROPERTY_DOM_VALUE'] ? 'д.' : '') . $arFields['PROPERTY_DOM_VALUE'];

		$arSchoolList[$arFields['PROPERTY_MUN_VALUE']][$arFields['ID']] = array(
			'NAME' => $arFields['~NAME'] . " (ИНН " . ($arFields['PROPERTY_INN_VALUE'] ? $arFields['PROPERTY_INN_VALUE'] : 'не указан') . ')',
			'ADDRESS' => $address,
			'DIR' => ($arFields['PROPERTY_DIR_FIO_VALUE'] ? $arFields['PROPERTY_DIR_FIO_VALUE'] : 'Директор НЕ УКАЗАН') .
						', телефон: ' . ($arFields['PROPERTY_PHONE_VALUE'] ? $arFields['PROPERTY_PHONE_VALUE'] : 'НЕ УКАЗАН') .
						', e-mail: ' . ($arFields['PROPERTY_EMAIL_VALUE'] ? $arFields['PROPERTY_EMAIL_VALUE'] : 'НЕ УКАЗАН'),
			'ALL_BOOKS' => 0,
			'ALL_SUM' => 0,
			'IZD' => $arIzdID,
			'IZD_SUM' => $arIzdID
		);
	}

	// Формируем структуру по муниципалитетам, школам и издательствам

	$arTemp = get_mun_list($USER->GetID());

	$prevID = 0;
	$parentID = 0;
	$oldLevel = 1;

	foreach ($arTemp as $key => $value) {

		if (strpos($value, '......') !== false) {
			if ($oldLevel == 2) {
				$oldLevel = 3;
				$parentID = $prevID;
			}
		}

		elseif (strpos($value, '...') !== false) {
			if ($oldLevel == 1) {
				$oldLevel = 2;
				$parentID = $prevID;
			} elseif ($oldLevel == 3) {
				$oldLevel = 2;
				$parentID = $regionID;
			}
		}

		$arReport[$key] = array(
			'NAME' => str_replace('...', '      ', $value),
			'PARENT' => $parentID,
		);

		if (isset($arSchoolList[$key])) $arReport[$key]['SCHOOLS'] = $arSchoolList[$key];

		$prevID = $key;
	}

	//Считаем учебники и суммы по заказам
	$arBooks = array();
	$arBookSum = array();
	
	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 9, 'PROPERTY_REGION_ID' => $regionID, 'PROPERTY_PERIOD' => $periodID),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_NUM', 'PROPERTY_COUNT', 'PROPERTY_PRICE')
	);

	while ($arFields = $res->GetNext()) {
		$arBooks[$arFields['PROPERTY_ORDER_NUM_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
		$arBookSum[$arFields['PROPERTY_ORDER_NUM_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
	}

	// Берем заказы по издательствам
	$res = CIBLockElement::GetList(
		false,
		array('IBLOCK_ID' => 11, 'PROPERTY_REGION_ID' => $regionID, 'PROPERTY_PERIOD' => $periodID, '!PROPERTY_STATUS' => array('oscart', 'osreport', 'osrepready')),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_SCHOOL_ID')
	);

	while ($arFields = $res->GetNext()) {
		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['SCHOOLS'][$arFields['PROPERTY_SCHOOL_ID_VALUE']]['ALL_BOOKS'] += $arBooks[$arFields['ID']];
		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['SCHOOLS'][$arFields['PROPERTY_SCHOOL_ID_VALUE']]['ALL_SUM'] += $arBookSum[$arFields['ID']];

		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['SCHOOLS'][$arFields['PROPERTY_SCHOOL_ID_VALUE']]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']] += $arBooks[$arFields['ID']];
		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['SCHOOLS'][$arFields['PROPERTY_SCHOOL_ID_VALUE']]['IZD_SUM'][$arFields['PROPERTY_IZD_ID_VALUE']] += $arBookSum[$arFields['ID']];

		$allIzdCount[$arFields['PROPERTY_IZD_ID_VALUE']] += $arBooks[$arFields['ID']];
		$allIzdSum[$arFields['PROPERTY_IZD_ID_VALUE']] += $arBookSum[$arFields['ID']];
	}


	// Удаляем нулевые школы
	foreach ($arReport as $keyMun => $arMun)
		foreach ($arMun['SCHOOLS'] as $key => $value)
			if ($value['ALL_BOOKS'] == 0)
				unset($arReport[$keyMun]['SCHOOLS'][$key]);

	// Удаляем нулевые муниципалитеты
	foreach ($arReport as $keyMun => $arMun)
		if (is_array($arMun['SCHOOLS']) && count($arMun['SCHOOLS']) == 0)
			unset($arReport[$keyMun]);


	// Удаляем нулевые издательства
	foreach ($allIzdCount as $key => $value)
		if ($value == 0)
			unset($arIzd[$key]);


	return (array(
		'REPORT' => $arReport,
		'IZD_LIST' => $arIzd
	));
}

?>