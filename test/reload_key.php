<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?

/**********************************************************
* Загрузка каталога
* Параметры:
*    $fileName - имя файла *.CSV на сервере (кодировка WIN)
*    $mode - издательство:
*               'PROSV' - "Просвещение"
*               'DROFA' - Дрофа
*				'ASTREL' - Астрель
*               'AKADEM' - Академкнига
*               'ASXXI' - Ассоциация XXI век
*               'BINOM' - Бином
*               'VLADOS' - Владос
*               'VITA' - Вита-пресс
*               'RUSSLOVO' - Русское слово
*               'VENTANA' - Вентана-Граф
*               'MNEMOZINA' - Мнемозина
* Возвращает массив:
* RESULT: true - загрузка успешна, false - ошибка
* ERROR: описание ошибки
**********************************************************/
function reload_books($fileName, $mode) {
	$result = array('RESULT' => false, 'ERROR' => '');

	global $USER;

	// Проверяем существование файла
	if (file_exists($fileName)) {

		csv_test_file($fileName);

		$fp = fopen($fileName, 'r');
		$arBooks = array();
		switch ($mode) {

			// Ипортируем каталог от "Просвещения"
			case 'PROSV':
					// ************ НАСТРОЙКА ПРОСВЕЩЕНИЯ *******************
					$izdID = 5;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numCode1C = 1;			// Код 1С
					$numAuthor = 2;			// Авторы
					$numClass = 4;			// Класс
					$numFullName = 6;		// Поное наименование учебника
					$numRemCode = 6;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numUMK = 7;			// линия УМК
					$numSystem = 8;			// Система
					$numYear = 11;			// Год издания
					$numPrim = 10;			// Примечание
					$numPrice = 12;			// Цена
					$numURL = 15;			// адрес УРЛ
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'аудио') !== false) $rem = 39;				// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'фонох') !== false) $rem = 39;			// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'online') !== false) $rem = 2698;			// с онлайн-поддержкой
						elseif (strpos($arStr[$numRemCode], 'электронным пр') !== false) $rem = 38;	// с электронным приложением

						$url = quotes_clear($arStr[$numURL]);
						if ((strlen($url) > 0) && (strpos($url, 'http://') === false)) $url = 'http://' . $url;

						$name = quotes_clear($arStr[$numFullName]);

						$code_1c = quotes_clear($arStr[$numCode1C]);

						$key = $izdID . '^' . $fp_number . '^' . $code_1c;
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => (intval($arStr[$numYear])>0 ? intval($arStr[$numYear]) : ''),
								'ED_IZM' => strpos($arStr[$numRemCode], 'омплект') !== false ? 35 : 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => quotes_clear($arStr[$numUMK]),
								'URL' => $url,
								'REMARKS' => $rem,
								'SYSTEM' => quotes_clear($arStr[$numSystem]),
								'STANDART' => '',
								'PRIM' => quotes_clear($arStr[$numPrim]),
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "ДРОФА"
			case 'DROFA':
					// ************ НАСТРОЙКА ДРОФЫ *******************
					$izdID = 100;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numCode1C = 2;			// Код 1С
					$numAuthor = 15;		// Авторы
					$numClass = 17;			// Класс
					$numFullName = 16;		// Поное наименование учебника
					$numRemCode = 18;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numSystem = 22;		// Система
					$numStandart = 21;		// Стандарт

					$numPrim1 = 25;			// Примечание1
					$numPrim2 = 18;			// Автор и полное наименование издательства

					$numPrice = 26;			// Цена
					$numURL = 24;			// адрес УРЛ
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);		//********* Код ФП


						$rem = '';
						if (strpos($arStr[$numRemCode], 'CD') !== false) $rem = 38;		//********** Полное наименование - берем оттуда комменты

						$url = quotes_clear($arStr[$numURL]);		//********* УРЛ

						if ((strlen($url) > 0) && (strpos($url, 'http://') === false)) $url = 'http://' . $url;

						$name = quotes_clear($arStr[$numFullName]);		//********* Полное наименование

						$code_1c = quotes_clear($arStr[$numCode1C]);

						$key = $numStr;
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => $url,
								'REMARKS' => $rem,
								'SYSTEM' => quotes_clear($arStr[$numSystem]),
								'STANDART' => quotes_clear($arStr[$numStandart]),
								'PRIM' => $arStr[$numPrim1] . ' ' . $arStr[$numPrim2],
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "АСТРЕЛЬ"
			case 'ASTREL':
					// ************ НАСТРОЙКА ДРОФЫ *******************
					$izdID = 101;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numClass = 2;			// Класс
					$numFullName = 1;		// Поное наименование учебника
					$numPrice = 4;			// Цена
					$numRem = 5;			// Авторский договор
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);
						$prim = quotes_clear($arStr[$numRem]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$key = $izdID . '^' . $fp_number . '^' . md5($name);
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => $prim,
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Академкниги"
			case 'AKADEM':
					// ************ НАСТРОЙКА ДРОФЫ *******************
					$izdID = 105;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numAuthor = 2;			// Автор
					$numFullName = 3;		// Полное наименование учебника
					$numClass = 5;			// Класс
					$numPrice = 10;			// Цена
					// ******************************************************

					$numStrFile = -1;
					$newNumStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStrFile++;
						$newNumStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$key = $numStrFile;
						$new_key = $newNumStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Ассоциация XXI век"
			case 'ASXXI':
					// ************ НАСТРОЙКА ****************************
					$izdID = 106;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numFullName = 2;		// Полное наименование учебника
					$numClass = 3;			// Класс
					$numPrice = 7;			// Цена
					$numYear = 5;			// Год издания
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$key = $izdID . '^' . $fp_number . '^' . md5($name);
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => quotes_clear($arStr[$numYear]),
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Бином"
			case 'BINOM':
					// ************ НАСТРОЙКА ПРОСВЕЩЕНИЯ *******************
					$izdID = 107;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numClass = 3;			// Класс
					$numFullName = 2;		// Поное наименование учебника
					$numRemCode = 5;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numStandart = 4;		// Стандарт
					$numPrice = 9;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$name = quotes_clear($arStr[$numFullName]);

						$key = $izdID . '^' . $fp_number . '^' . md5($name);
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => strpos($arStr[$numRemCode], 'омплект') !== false ? 35 : 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => quotes_clear($arStr[$numStandart]),
								'PRIM' => quotes_clear($arStr[$numRemCode]),
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "ВЛАДОС"
			case 'VLADOS':
					// ************ НАСТРОЙКА *******************************
					$izdID = 103;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numClass = 3;			// Класс
					$numFullName = 2;		// Поное наименование учебника
					$numPrice = 8;			// Цена
					$numYear = 5;
					// ******************************************************
					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$key = $izdID . '^' . $fp_number . '^' . md5($name);
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => quotes_clear($arStr[$numYear]),
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Русское слово"
			case 'RUSSLOVO':
					// ************ НАСТРОЙКА *******************************
					$izdID = 104;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 3;			// Код ФП
					$numClass = 5;			// Класс
					$numFullName = 4;		// Поное наименование учебника
					$numPrice = 8;			// Цена
					$numYear = 6;
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$key = $izdID . '^' . $fp_number . '^' . md5($name);
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => quotes_clear($arStr[$numYear]),
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Вита-пресс"
			case 'VITA':
					// ************ НАСТРОЙКА *******************************
					$izdID = 102;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numAuthor = 1;
					$numClass = 3;			// Класс
					$numFullName = 2;		// Поное наименование учебника
					$numPrice = 14;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$rem = '';

						$temp = trim($arStr[$numFPCode]);
						if (substr($temp, 0, 5) == '99.99') {
							$fp_number = substr($temp, 15);
							$rem = 38;
						}

						$key = $izdID . '^' . $fp_number . '^' . md5($name);
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Вентана-граф"
			case 'VENTANA':
					// ************ НАСТРОЙКА *******************************
					$izdID = 108;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numCode1C = 2;			// Код 1С

					$numFullName = 3;		// Полное наименование учебника
					$numRemCode = 3;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numPrice = 4;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'аудио') !== false) $rem = 39;				// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'фонох') !== false) $rem = 39;			// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'online') !== false) $rem = 2698;			// с онлайн-поддержкой
						elseif (strpos($arStr[$numRemCode], 'CD-диск') !== false) $rem = 38;	// с электронным приложением

						$name = quotes_clear($arStr[$numFullName]);

						$code_1c = quotes_clear($arStr[$numCode1C]);

						$key = $izdID . '^' . $fp_number . '^' . $code_1c;
						$new_key = $numStr;

						if ($fp_number !== false) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => '',
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Мнемозина "
			case 'MNEMOZINA':
					// ************ НАСТРОЙКА *******************************
					$izdID = 109;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 4;			// Код ФП
					$numCode1C = 0;			// Код 1С

					$numFullName = 2;		// Полное наименование учебника
					$numAuthor = 1;			// Автор
					$numRemCode = 3;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numPrice = 7;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$price = floatval($arStr[$numPrice]);	// Цена

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'CD/DVD') !== false) $rem = 38;	// с электронным приложением

						$name = quotes_clear($arStr[$numFullName]);

						$code_1c = quotes_clear($arStr[$numCode1C]);

						$key = $izdID . '^' . $fp_number . '^' . $code_1c;
						$new_key = $numStr;

						if (($fp_number !== false) && ($price > 0)) {
							$arBooks[$key] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => '',
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $key,
								'NEW_KEY' => $new_key
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Если $mode передали неверный
			default:
				$result['ERROR'] = "ОШИБКА: Передан неверный параметр mode: $mode";
		}
		fclose($fp);

		// Если ошибки нет, начинаем загрузку в базу
		if ($result['RESULT']) {

			if (CModule::IncludeModule('iblock')) {

				$arFilter = array();
				foreach($arBooks as $arBook)
					$arFilter[] = $arBook['KEY'];

				$arTrans = array();
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 5, 'PROPERTY_KEY' => $arFilter), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_KEY'));
				while ($arFields = $res->GetNext()) {
					echo 'Конвертация KEY для BOOK_ID=' . $arFields['ID'] . '<br>';
					$arTrans[$arFields['PROPERTY_KEY_VALUE']] = array(
						'ID' => $arFields['ID'],
						'NEW_KEY' => ''
					);
				}

				foreach ($arBooks as $arBook)
					$arTrans[$arBook['KEY']]['NEW_KEY'] = $arBook['NEW_KEY'];

				foreach ($arTrans as $newKey) {
					CIBlockElement::SetPropertyValuesEx($newKey['ID'], 5, array('KEY' => $newKey['NEW_KEY']));
				}

			}

		}
	} else {
		$result['ERROR'] = "ОШИБКА: не найден файл $fileName";
	}
	return ($result);
}



//reload_books($_SERVER['DOCUMENT_ROOT'] . '/test/prosv.csv', 'PROSV');


?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>