<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("test");?>
<?

	if (0 && $USER->IsAdmin()) {

		// Проверяем существование файла
		$fileName = $_SERVER['DOCUMENT_ROOT'].'/test/astrel_kemerovo.csv';
		if (file_exists($fileName)) {

			csv_test_file($fileName);

			$fp = fopen($fileName, 'r');
			$arBooks = array();

			while (!feof($fp)) {
				$arStr = csv_explode(iconv('windows-1251', 'utf-8', fgets($fp)));

				$fp_number = test_fp_number($arStr[1]);			//***** Номер ФП
				$code_1c = quotes_clear($arStr[2]);
				$price = floatval($arStr[25]);

				if ($fp_number !== false) {
					$arBook[$code_1c] = array(
						'FP_NUMBER' => $fp_number,
						'CODE_1C' => $code_1c,
						'PRICE' => $price
					);
				}
			}
		}

		echo 'Загружено книг из прайса: ' . count($arBook) . '<br>';

		// Загружаем из базы учебники Вентаны
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 5, 'SECTION_ID' => 101),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_FP_CODE', 'PROPERTY_CODE_1C', 'PROPERTY_PRICE')
		);

		$i = 0;
		$j = 0;
		while ($arFields = $res->GetNext()) {
			$j++;
			$key = $arFields['PROPERTY_CODE_1C_VALUE'];
			if ($arBook[$key]) {
				$i++;
				CIBlockElement::SetPropertyValuesEx($arFields['ID'], 5, array('PRICE_144' => $arBook[$key]['PRICE']));
			} else {
				echo 'Нет совпадения по коду 1С для ID = ' . $arFields['ID'] . '<br>';
			}
		}

		echo 'Учебников в базе: ' . $j . '<br>';
		echo 'Совпало по коду 1С с базой: ' . $i . '<br>';
		echo 'OK<br>';

	}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>