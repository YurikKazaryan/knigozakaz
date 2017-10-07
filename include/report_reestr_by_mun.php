<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


function report_reestr_by_mun($regionID, $periodID) {
	global $USER;

	$arReport = array();

	$arIzd = get_izd_list(); // Список издательств

	$arIzdID = array();
	$arIzdSchool = array();
	foreach ($arIzd as $key => $value) {
		$arIzdID[$key] = 0;
		$arIzdSchool[$key] = array();
	}

	// Формируем структуру по муниципалитетам и издательствам

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
			'ALL_COUNT' => 0,
			'SCHOOLS' => array(),
			'IZD' => $arIzdID,
			'IZD_SCH' => $arIzdSchool
		);

		$prevID = $key;
	}

	// Генерим справочник школа - муниципалитет
	$arSchools = array();
	$res = CIBlockElement::GetList(
		array('ID' => 'asc'),
		array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_OBLAST' => $regionID),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_MUN')
	);
	while ($arFields = $res->GetNext())
		$arSchools[$arFields['ID']] = $arFields['PROPERTY_MUN_VALUE'];

	// Считаем школы с заказами по муниципалитетам и издательствам

	$res = CIBLockElement::GetList(
		false,
		array('IBLOCK_ID' => IB_ORDERS_LIST, 'PROPERTY_REGION_ID' => $regionID, 'PROPERTY_PERIOD' => $periodID, '!PROPERTY_STATUS' => array('oscart', 'osreport', 'osrepready')),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_SCHOOL_ID')
	);

	while ($arFields = $res->GetNext()) {

		$schoolID = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
		$munID = $arSchools[$schoolID];
		$izdID = $arFields['PROPERTY_IZD_ID_VALUE'];

		// Если этой школы еще не было в этом издательстве этого муниципалитета - считаем и добавляем ID школы
		if (!in_array($schoolID, $arReport[$munID]['IZD_SCH'][$izdID])) {
			$arReport[$munID]['IZD_SCH'][$izdID][] = $schoolID;
			$arReport[$munID]['IZD'][$izdID]++;

			// Добавляем в итог по родителю, если это не второй уровень
			if ($arReport[$munID]['PARENT'] != $regionID) $arReport[$arReport[$munID]['PARENT']]['IZD'][$izdID]++;

			// Добавляем в итог по региону
			$arReport[$regionID]['IZD'][$izdID]++;

			// Если этой школы еще не было в этом муниципалитете - считаем и добавляем ID школы
			if (!in_array($schoolID, $arReport[$munID]['SCHOOLS'])) {
				$arReport[$munID]['SCHOOLS'][] = $schoolID;
				$arReport[$munID]['ALL_COUNT']++;

				// Добавляем в итого по региону
				$arReport[$regionID]['ALL_COUNT']++;

				// Если это не 2 уровень, то добавляем к итого по родителю
				if ($arReport[$munID]['PARENT'] != $regionID)
					$arReport[$arReport[$munID]['PARENT']]['ALL_COUNT']++;
			}
		}

	}

	// Удаляем нулевые муниципалитеты
	foreach ($arReport as $key => $value)
		if ($value['ALL_COUNT'] == 0) unset($arReport[$key]);

	// Удаляем нулевые издательства
	foreach ($arReport[$regionID]['IZD'] as $key => $value)
		if ($value == 0) unset($arIzd[$key]);

	return (array(
		'REPORT' => $arReport,
		'IZD_LIST' => $arIzd
	));
}

?>