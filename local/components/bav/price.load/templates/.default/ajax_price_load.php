<?
/********************************************************************
* Импорт каталога
*
* Параметры (передаются через POST)
*    FILE_ID - ID файла импорта
*    STEP - номер шага (1, 2, ...)
*    SIZE - размер шага
*    MODE - шаг обработки
*			1 - сброс флага WORK
*			2 - импорт каталога
*			3 - сборс активности для WORK == Y
* Шаг обработки - 50 записей
* Возвращает:
*	exit = 1 - конец обработки, 0 - еще есть записи
*   count - кол-во обработанных записей
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$result = array('exit' => 0, 'count' => 0, 'error' => 1);

$step = intval($_POST['STEP']);
$stepSize = intval($_POST['SIZE']);

if($step && $stepSize && CModule::IncludeModule('iblock')) {

	// Загружаем данные файла импорта
	$resFile = CIBlockElement::getList(
		false,
		array('IBLOCK_ID' => 35, 'ID' => $_POST['FILE_ID']),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_REGION', 'PROPERTY_IZD', 'PROPERTY_FILE', 'PROPERTY_DATA', 'PROPERTY_DATE', 'PROPERTY_FP_LOAD', 'PROPERTY_IZD_SUB', 'PROPERTY_NDS_MASK')
	);
	if ($arFieldsFile = $resFile->Fetch()) {

		$regionID = $arFieldsFile['PROPERTY_REGION_VALUE'];

		$izdID = $arFieldsFile['PROPERTY_IZD_VALUE'];
		$izdSub = $arFieldsFile['PROPERTY_IZD_SUB_VALUE'];

		$startDate = MakeTimeStamp($arFieldsFile['PROPERTY_DATE_VALUE']);

		$fpLoad = ($arFieldsFile['PROPERTY_FP_LOAD_VALUE'] == 'Y');

		$ndsMask = (is_array($arFieldsFile['PROPERTY_NDS_MASK_VALUE']) ? $arFieldsFile['PROPERTY_NDS_MASK_VALUE'] : false);

		$arPeriod = getWorkPeriod();
		$periodID = $arPeriod['ID'];

		switch ($_POST['MODE']) {

			// Проверяем уникальное поле на корректность
			case 0:

				// Загружаем файл
				$arFile = file($_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($arFieldsFile['PROPERTY_FILE_VALUE']), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				// Загружаем привязку полей
				$arData = array();
				foreach ($arFieldsFile['PROPERTY_DATA_VALUE'] as $str) {
					$arTemp = explode(';', $str);
					if (count($arTemp) > 2) {
						$key = $arTemp[0];
						unset($arTemp[0]);
						$arData[$key] = $arTemp;
					} else {
						$arData[$arTemp[0]] = $arTemp[1];
					}
				}

				$allStr = count($arFile);
				$result['allCount'] = $allStr;

				$arCodes = array();
				$arCodesError = array();

				for ($strNum = 0; ($strNum < $allStr); $strNum++) {

					if (strlen(trim($arFile[$strNum])) == 0) continue;

					// Разбиваем строку на поля
					$arStr = csvExplode(iconv('windows-1251', 'utf-8', $arFile[$strNum]));

					// Если строка с книгой (есть правильный код ФП, то обрабатываем)
					if (testFPNumber($arStr[$arData['FP_CODE']])) {
						$code1C = quotesClear($arStr[$arData['CODE_1C']]);
						if (in_array($code1C, $arCodes)) {
							if (!in_array($code1C, $arCodesError)) $arCodesError[] = $code1C;
						} else {
							$arCodes[] = $code1C;
						}
					}
				}

				if (count($arCodesError) > 0) {
					$result['step0error'] = 1;
					$result['step0list'] = '';
					foreach ($arCodesError as $value) $result['step0list'] .= $value . '<br>';
				}

				$result['count'] = $allStr;
				$result['exit'] = 1;

				break;

			// Устанавливаем свойство WORK в N
			case 1:

				$result['count'] = 0;

				// Выбираем записи для обработки

				$arFilter = array('IBLOCK_ID' => 5, 'SECTION_ID' => $izdID, 'PROPERTY_REGION' => $regionID);
				if ($izdSub) $arFilter['PROPERTY_SUBSECTION'] = $izdSub;

				$res = CIBlockElement::GetList(
					false,
					$arFilter,
					false,
					array('iNumPage' => $step, 'nPageSize' => $stepSize),
					array('IBLOCK_ID', 'ID')
				);
				while ($arFields = $res->Fetch()) {
					// Устанавливаем признак
					CIBlockElement::SetPropertyValueCode($arFields['ID'], 'WORK', 'N');
					$result['count']++;
				}
				if ($result['count'] < $stepSize) $result['exit'] = 1;
				break;

			// Обрабатываем импорт
			case 2:

				// Загружаем файл
				$arFile = file($_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($arFieldsFile['PROPERTY_FILE_VALUE']), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

//test_out($arFile);

				// Загружаем привязку полей
				$arData = array();
				foreach ($arFieldsFile['PROPERTY_DATA_VALUE'] as $str) {
					$arTemp = explode(';', $str);
					if (count($arTemp) > 2) {
						$key = $arTemp[0];
						unset($arTemp[0]);
						$arData[$key] = $arTemp;
					} else {
						$arData[$arTemp[0]] = $arTemp[1];
					}
				}

//test_out($arData);

//test_out("stepSize: $stepSize");

				$allStr = count($arFile);
				$result['allCount'] = $allStr;

				for ($strNum = (($step - 1) * $stepSize), $workCnt = 0; (($workCnt < $stepSize) && ($strNum < $allStr)); $strNum++, $workCnt++) {

//test_out("Stroka $strNum     workCnt $workCnt");

					if (strlen(trim($arFile[$strNum])) == 0) continue;

					// Разбиваем строку на поля
					$arStr = csvExplode(iconv('windows-1251', 'utf-8', $arFile[$strNum]));

					$arStr[$arData['PRICE']] = str_replace(' ', '', $arStr[$arData['PRICE']]);

//test_out('before IF: FP_CODE: ' . $arStr[$arData['FP_CODE']]);

					// Если строка с книгой (есть правильный код ФП, то обрабатываем)
					if (testFPNumber($arStr[$arData['FP_CODE']])) {

//test_out('enter THEN');

						// Массив свойств, которые не обновляются из этого списка
						$arExFields = array('CODE_1C', 'PRICE');

						// Составляем массив свойств
						$arProp = array();
						foreach ($arData as $dataCode => $dataValue) {
							if (in_array($dataCode, $arExFields)) continue;
							if (is_array($dataValue)) {
								$str = '';
								foreach ($dataValue as $value) $str .= ' ' . quotesClear($arStr[$value]);
								$arProp[$dataCode] = trim($str);
							} else {
								if ($dataCode == 'FP_CODE') {
									$arProp[$dataCode] = ($fpLoad ? preg_replace( '/[^[:print:]]/', '',quotesClear($arStr[$dataValue])) : '');
									if ($arProp[$dataCode] == "99.99.99.99.99.99") $arProp[$dataCode] = "";
								}

								elseif ($dataCode == 'NDS') {
									$nds18 = false;
									$tempStr = strtoupper(quotesClear($arStr[$dataValue]));
									foreach ($ndsMask as $mask) {
										if (strpos($tempStr, $mask) !== false) {
											$nds18 = true;
											break;
										}
									}
									$arProp[$dataCode] = ($nds18 ? 18 : 10);
								} else
									$arProp[$dataCode] = quotesClear($arStr[$dataValue]);
							}
						}
						$arProp['WORK'] = 'Y';
						$arProp['KEY'] = $strNum;
						$arProp['SUBSECTION'] = $izdSub;

//test_out($arProp);

						// Название книги для поиска
						$bookName = quotesClear($arStr[$arData['AUTHOR']]) . ' ' . quotesClear($arStr[$arData['TITLE']]);

						// Ищем книгу по уникальному коду
						$res = CIBlockElement::GetList(
							false,
							array('IBLOCK_ID' => 5, 'SECTION_ID' => $izdID, 'PROPERTY_REGION' => $regionID, 'PROPERTY_CODE_1C' => $arStr[$arData['CODE_1C']]),
							false, false,
							array('IBLOCK_ID', 'ID')
						);

						if ($arFields = $res->Fetch()) {	// Если нашли - обновляем

							$bookID = $arFields['ID'];

							// Обновляем запись в каталоге
							$el = new CIBlockElement;
							$el->Update($arFields['ID'], array(
								'ACTIVE' => 'Y',
								'NAME' => $bookName
							));
							CIBlockElement::SetPropertyValuesEx($arFields['ID'], 5, $arProp);

						} else {	// Если не нашли - добавляем

							$arProp['CODE_1C'] = quotesClear($arStr[$arData['CODE_1C']]);
							$arProp['REGION'] = $regionID;

							$el = new CIBlockElement;

							$bookID = $el->Add(array(
								'MODIFIED_BY' => $USER->GetID(),
								'IBLOCK_SECTION_ID' => $izdID,
								'IBLOCK_ID' => 5,
								'NAME' => $bookName,
								'ACTIVE' => 'Y',
								'PROPERTY_VALUES'=> $arProp
							));

						}

						// Обновляем цену

						// Проверяем наличие цены с заданной датой
						$res = CIBlockElement::GetList(
							false,
							array(
								'IBLOCK_ID' => 34,
								'PROPERTY_REGION' => $regionID,
								'PROPERTY_IZD' => $izdID,
								'PROPERTY_PERIOD' => $periodID,
								'PROPERTY_START' => date('Y-m-d', $startDate),
								'PROPERTY_BOOK' => $bookID
							),
							false, false,
							array('IBLOCK_ID', 'ID')
						);
						// Если нашли - меняем цену, если нет - добавляем запись
						if ($arFields = $res->Fetch()) {
							CIBlockElement::SetPropertyValueCode($arFields['ID'], 'PRICE', $arStr[$arData['PRICE']]);
						} else {
							$el = new CIBlockElement;
							$el->Add(array(
								'MODIFIED_BY' => $USER->GetID(),
								'IBLOCK_SECTION_ID' => false,
								'IBLOCK_ID' => 34,
								'NAME' => $bookID,
								'ACTIVE' => 'Y',
								'PROPERTY_VALUES'=> array(
									'REGION' => $regionID,
									'IZD' => $izdID,
									'PERIOD' => $periodID,
									'START' => date('d.m.Y', $startDate),
									'BOOK' => $bookID,
									'PRICE' => $arStr[$arData['PRICE']]
								)
							));
						}

					}

				}

				$result['count'] = $workCnt;
				if ($strNum == $allStr) $result['exit'] = 1;

				break;

			// Для книг с WORK == N сбрасываем активность
			case 3:

				$result['count'] = 0;

				// Выбираем записи для обработки

				$arFilter = array('IBLOCK_ID' => 5, 'SECTION_ID' => $izdID, 'PROPERTY_REGION' => $regionID, 'PROPERTY_WORK' => 'N');
				if ($izdSub) $arFilter['PROPERTY_SUBSECTION'] = $izdSub;

				$res = CIBlockElement::GetList(
					false,
					$arFilter,
					false, false,
					array('IBLOCK_ID', 'ID')
				);
				while ($arFields = $res->Fetch()) {
					// Сбрасываем активность
					$el = new CIBlockElement;
					$el->Update($arFields['ID'], array(
						'MODIFIED_BY' => $USER->GetID(),
						'ACTIVE' => 'N'
					));
					$result['count']++;
				}
				$result['exit'] = 1;
				break;
		}

		$result['error'] = 0;
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>