<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

function report_binom($munID, $periodID, $startDate = false) {
	$izdID = 107;

	if (CModule::IncludeModule('iblock')) {

		// Выбираем школы муниципалитета

		$arMun = get_mun_id_for_filter($munID);

		$arSchools = array();
		$arFilter = array();
		$res = CIBlockElement::GetList(
			array('PROPERTY_MUN' => 'asc'),
			array('IBLOCK_ID' => 10, 'PROPERTY_MUN' => $arMun),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_INN', 'NAME', 'PROPERTY_MUN', 'PROPERTY_DIR_FIO')
		);
		while ($arFields = $res->GetNext()) {
			$arSchools[$arFields['ID']] = array(
				'NAME' => html_entity_decode($arFields['NAME']) . ' ' . get_izd_name($arFields['PROPERTY_MUN_VALUE']),
				'SHORT_NAME' => html_entity_decode($arFields['NAME']),
				'INN' => $arFields['PROPERTY_INN_VALUE'],
				'MUN' => $arFields['PROPERTY_MUN_VALUE'],
				'DIR' => $arFields['PROPERTY_DIR_FIO_VALUE']
			);
			$arFilter[$arFields['ID']] = $arFields['ID'];
		}

// test_out("All schools in dep: " . count($arSchools));

		// Выбираем школы, которые сделали заказы для Бинома
		$arTemp = array();

		$arOrderFilter = array('IBLOCK_ID' => 11, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_SCHOOL_ID' => $arFilter, '!PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $periodID);
		if ($startDate) $arOrderFilter['>=DATE_ACTIVE_FROM'] = ConvertTimeStamp($startDate);

		$res = CIBlockElement::GetList(
			false,
			$arOrderFilter,
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_STATUS', 'PROPERTY_PERIOD')
		);
		while ($arFields = $res->GetNext())
			$arTemp[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];

		// Убираем школы, которые не работают с биномом
		foreach ($arSchools as $key => $arSchool)
			if (!in_array($key, $arTemp)) {
				unset($arSchools[$key]);
				unset($arFilter[$key]);
			}

// test_out("School having orders: " . count($arSchools));

		if (count($arSchools) == 0)
			$arSchools = false;
		else {
			// Получаем спиок заказанных книг и считаем общий итог
			$arReport = array();
			$arCount = array();
			$res = CIBlockElement::GetList(
				false,
				array('IBLOCK_ID' => 9, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_SCHOOL_ID' => $arFilter, '!PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $periodID),
				false, false,
				array('IBLOCK_ID', 'ID', 'PROPERTY_KEY', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_SCHOOL_ID', 'PROPERTY_ORDER_NUM', 'PROPERTY_PERIOD')
			);

			while ($arFields = $res->GetNext()) {

				if (!isset($arReport[$arFields['PROPERTY_SCHOOL_ID_VALUE']])) {
					$arReport[$arFields['PROPERTY_SCHOOL_ID_VALUE']] = array();
					$arSchools[$arFields['PROPERTY_SCHOOL_ID_VALUE']]['ORDER_NUM'] = $arFields['PROPERTY_ORDER_NUM_VALUE'];
				}
				$arReport[$arFields['PROPERTY_SCHOOL_ID_VALUE']][$arFields['PROPERTY_KEY_VALUE']] = $arFields['PROPERTY_COUNT_VALUE'];

				if (!isset($arCount[$arFields['PROPERTY_KEY_VALUE']]))
					$arCount[$arFields['PROPERTY_KEY_VALUE']] = $arFields['PROPERTY_COUNT_VALUE'];
				else
					$arCount[$arFields['PROPERTY_KEY_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
			}
		}
	}

//	test_out($arReport);
//	test_out($arSchools);
	return array($arReport, $arSchools, $arCount);
}

?>