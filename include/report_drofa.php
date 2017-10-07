<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

/*
* Формирование данных для отчета по Дрофе
*/

function report_drofa($munID, $period) {
	$izdID = 100;

	if (CModule::IncludeModule('iblock')) {

		// Выбираем школы муниципалитета
		$arMun = get_mun_id_for_filter($munID);

		$arSchools = array();
		$arFilter = array();
		$res = CIBlockElement::GetList(
			array('PROPERTY_MUN' => 'asc'),
			array('IBLOCK_ID' => 10, 'PROPERTY_MUN' => $arMun),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_INN', 'NAME', 'PROPERTY_MUN')
		);

		while ($arFields = $res->GetNext()) {
			$arSchools[$arFields['ID']] = array('NAME' => $arFields['NAME'] . ' ' . get_izd_name($arFields['PROPERTY_MUN_VALUE']), 'INN' => $arFields['PROPERTY_INN_VALUE'], 'MUN' => $arFields['PROPERTY_MUN_VALUE']);
			$arFilter[$arFields['ID']] = $arFields['ID'];
		}

// test_out("All schools in dep: " . count($arSchools));

		// Выбираем школы, которые сделали заказы для издательства
		$artemp = array();
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 11, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_SCHOOL_ID' => $arFilter, '!PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $period),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_STATUS')
		);
		while ($arFields = $res->GetNext())
			$arTemp[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];

		// Убираем школы, которые не работают с издательством
		foreach ($arSchools as $key => $arSchool)
			if (!in_array($key, $arTemp)) {
				unset($arSchools[$key]);
				unset($arFilter[$key]);
			}

// test_out("School having orders: " . count($arSchools));

		// Получаем спиок заказанных книг
		$arReport = array();
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_SCHOOL_ID' => $arFilter, '!PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $period),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_CODE_1C', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_SCHOOL_ID')
		);
		while ($arFields = $res->GetNext()) {

// test_out(print_r($arFields,1));

			if (!isset($arReport[$arFields['PROPERTY_CODE_1C_VALUE']])) {
				$arReport[$arFields['PROPERTY_CODE_1C_VALUE']] = array();
				foreach ($arSchools as $key => $value)
					$arReport[$arFields['PROPERTY_CODE_1C_VALUE']][$key] = 0;
			}

			$arReport[$arFields['PROPERTY_CODE_1C_VALUE']][$arFields['PROPERTY_SCHOOL_ID_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
		}
	}

//	test_out($arReport);
//	test_out($arSchools);
	return array($arReport, $arSchools);
}

?>