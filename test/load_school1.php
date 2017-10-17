<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	/************************************
	* Загрузка списка школ по CSV-файлу
	************************************/
	if (0 && $USER->IsAdmin()) {

		$arFile = file($_SERVER['DOCUMENT_ROOT'] . '/test/kemerovo_schools.csv');
		$arError = array();

		$arSchool = array();

		foreach ($arFile as $str) {
			$arStr = csv_explode(iconv('windows-1251', 'utf-8', $str));
			$arSchool[] = array(
				'OBLAST' => 144,
				'MUN' => intval($arStr[0]),
				'NAME' => quotes_clear($arStr[1]),
				'FULL_NAME' => quotes_clear($arStr[2])
			);
		}

		// Пишем в базу
		foreach ($arSchool as $arItem) {
//			$el = new CIBlockElement;
			$arNew = Array(
				'MODIFIED_BY' => $USER->GetID(),
				'IBLOCK_SECTION_ID' => false,
				'IBLOCK_ID' => 10,
				'NAME' => $arItem['NAME'],
				'ACTIVE' => 'Y',
				'PROPERTY_VALUES'=> array(
					'OBLAST' => $arItem['OBLAST'],
					'MUN' => $arItem['MUN'],
					'FULL_NAME' => $arItem['FULL_NAME']
				)
			);
//test_print($arNew);
//			$newID = $el->Add($arNew);
			if (!$newID) echo 'ОШИБКА: ' . $el->LAST_ERROR . '<br>';
		}


	}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>