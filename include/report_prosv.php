<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

/*
*Формирование массива для сводного отчета по Просвещению
*/

function report_prosv($munID, $period, $izdID = 5, $startDate = false) {

	if (CModule::IncludeModule('iblock')) {

		// Выбираем школы муниципалитета
		$arMun = get_mun_id_for_filter($munID);

		$arSchools = array();
		$arFilter = array();
		$res = CIBlockElement::GetList(
			array('PROPERTY_MUN' => 'asc'),
			array('IBLOCK_ID' => 10, 'PROPERTY_MUN' => $arMun),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_MUN')
		);

		while ($arFields = $res->GetNext()) {
			$arSchools[$arFields['ID']] = get_school_info($arFields['ID']);
			$arFilter[$arFields['ID']] = $arFields['ID'];
		}

// test_out("All schools in dep: " . count($arSchools));

		// Выбираем школы, которые сделали заказы для издательства
		$arTemp = array();
		$arOrderFilter = array('IBLOCK_ID' => 11, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_SCHOOL_ID' => $arFilter, '!PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $period);

		if ($startDate) $arOrderFilter['>=DATE_ACTIVE_FROM'] = ConvertTimeStamp($startDate);

// test_out($arOrderFilter);

		$arOrderList = array();

		$res = CIBlockElement::GetList(
			false,
			$arOrderFilter,
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_STATUS', 'PROPERTY_IZD_ID', 'DATE_ACTIVE_FROM')
		);
		while ($arFields = $res->GetNext()) {
			$arOrderList[] = $arFields['ID'];
			$arTemp[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
		}

		// Убираем школы, которые не работают с издательством
		foreach ($arSchools as $key => $arSchool)
			if (!in_array($key, $arTemp)) {
				unset($arSchools[$key]);
				unset($arFilter[$key]);
			}

		// Получаем список заказанных книг
		$arReport = array();

		if (count($arFilter) > 0) {
			$res = CIBlockElement::GetList(
				false,
				array(
					'IBLOCK_ID' => 9,
					'PROPERTY_IZD_ID' => $izdID,
					'PROPERTY_SCHOOL_ID' => $arFilter,
					'!PROPERTY_STATUS' => 'osrepready',
					'PROPERTY_PERIOD' => $period,
					'PROPERTY_ORDER_NUM' => $arOrderList
				),
				false, false,
				array('IBLOCK_ID', 'ID', 'PROPERTY_CODE_1C', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_SCHOOL_ID', 'PROPERTY_IZD_ID', 'PROPERTY_ORDER_NUM', 'PROPERTY_FP_CODE')
			);
			while ($arFields = $res->GetNext()) {

	// test_out(print_r($arFields,1));

				if ($izdID == 107)
					$keyValue = $arFields['PROPERTY_FP_CODE_VALUE'];
				else
					$keyValue = $arFields['PROPERTY_CODE_1C_VALUE'];

				if (!isset($arReport[$keyValue])) {
					$arReport[$keyValue] = array();
					foreach ($arSchools as $key => $value)
						$arReport[$keyValue][$key] = 0;
				}

				$arReport[$keyValue][$arFields['PROPERTY_SCHOOL_ID_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
			}
		}
	}

//	test_out($arReport);
global $USER;
//test_out($arSchools);
	return array($arReport, $arSchools);
}

?>