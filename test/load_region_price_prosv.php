<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("test");?>
<?

	if (1 && $USER->IsAdmin()) {

		// Проверяем существование файла
		$fileName = $_SERVER['DOCUMENT_ROOT'].'/test/prosv_kemerovo.csv';
		if (file_exists($fileName)) {

			csv_test_file($fileName);

			$fp = fopen($fileName, 'r');
			$arBooks = array();

			$numStr = 0;
			while (!feof($fp)) {
				$arStr = csv_explode(iconv('windows-1251', 'utf-8', fgets($fp)));
				$numStr++;

				$fp_number = test_fp_number($arStr[0]);			//***** Номер ФП
				$code_1c = quotes_clear($arStr[2]);
				$price = floatval($arStr[11]);

				if ($fp_number !== false) {
					$arBook[$code_1c] = array(
						'FP_NUMBER' => $fp_number,
						'CODE_1C' => $code_1c,
						'PRICE' => $price
					);
				}
			}
		}

		// Загружаем из базы учебники ПРосвещения
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 5, 'SECTION_ID' => 5),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_FP_CODE', 'PROPERTY_CODE_1C', 'PROPERTY_PRICE')
		);

		while ($arFields = $res->GetNext()) {
			$key = $arFields['PROPERTY_CODE_1C_VALUE'];
			if ($arBook[$key]) {
				CIBlockElement::SetPropertyValuesEx($arFields['ID'], 5, array('PRICE_144' => $arBook[$key]['PRICE']));
			}
		}

		echo 'OK<br>';
//		test_print($arBook);
	}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>