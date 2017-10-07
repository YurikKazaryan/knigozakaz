<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


function report_reestr_by_school($regionID, $periodID, $izdID) {
	global $USER;

	$arTemp = get_mun_list($USER->GetID());

	// Генерим справочник школа - муниципалитет и школа - название
	$arSchools = array();
	$arSchoolNames = array();
	$res = CIBlockElement::GetList(
		array('ID' => 'asc'),
		array('IBLOCK_ID' => 10, 'PROPERTY_OBLAST' => $regionID),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_MUN', 'NAME')
	);
	while ($arFields = $res->GetNext()) {
		$arSchools[$arFields['ID']] = $arFields['PROPERTY_MUN_VALUE'];
		$arSchoolNames[$arFields['ID']] = $arFields['~NAME'];
	}

	// Формируем структуру по муниципалитетам

	$arReport = array();

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
			'ALL_SUM' => 0
		);

		$prevID = $key;
	}


	// Получаем информацию по школам (сумма заказов в заданное издательство)
	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 11, 'PROPERTY_REGION_ID' => $regionID, 'PROPERTY_PERIOD' => $periodID, 'PROPERTY_IZD_ID' => $izdID, '!PROPERTY_STATUS' => array('oscart', 'osreport', 'osrepready')),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_SUM')
	);

	while ($arFields = $res->GetNext()) {
		$arReport[$regionID]['ALL_SUM'] += $arFields['PROPERTY_SUM_VALUE'];

		if ($arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['PARENT'] != $regionID) 
			$arReport[$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['PARENT']]['ALL_SUM'] += $arFields['PROPERTY_SUM_VALUE'];

		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['SCHOOL'][$arFields['PROPERTY_SCHOOL_ID_VALUE']]['SUM'] += $arFields['PROPERTY_SUM_VALUE'];
		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['SCHOOL'][$arFields['PROPERTY_SCHOOL_ID_VALUE']]['NAME'] =
			($arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['PARENT'] == $regionID ? '            ' : '                  ') . $arSchoolNames[$arFields['PROPERTY_SCHOOL_ID_VALUE']];
		$arReport[$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]]['ALL_SUM'] += $arFields['PROPERTY_SUM_VALUE'];
	}

	// Удаляем пустые муниципалитеты
	foreach ($arReport as $key => $value)
		if ($value['ALL_SUM'] == 0)
			unset($arReport[$key]);

	return (array(
		'REPORT' => $arReport
	));
}

?>