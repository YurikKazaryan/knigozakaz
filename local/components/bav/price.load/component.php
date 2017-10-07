<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('iblock');

if (($arParams['CACHE_TYPE'] == 'N') || $this->StartResultCache($arParams['CACHE_TIME'])) {

	$arResult['REG_LIST'] = getRegionInfo();
	$arResult['IZD_LIST'] = getIzdList();

	if ($_POST['BTN'] == 'LOAD' && isset($arResult['REG_LIST'][intval($_POST['REGION'])]) && isset($arResult['IZD_LIST'][intval($_POST['IZD'])]) && !$_FILES['PRICE_FILE']['error']) {

		// Загружаем файл в издательство для обработки
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 35, 'PROPERTY_REGION' => intval($_POST['REGION']), 'PROPERTY_IZD' => intval($_POST['IZD']), 'PROPERTY_TYPE' => 'tfs_price'),
			false, false,
			array('IBLOCK_ID', 'ID')
		);
		if ($arFields = $res->Fetch()) {
			CIBlockElement::SetPropertyValueCode($arFields['ID'], 'FILE', $_FILES['PRICE_FILE']);
			CIBlockElement::SetPropertyValueCode($arFields['ID'], 'DATA', '');
			CIBlockElement::SetPropertyValueCode($arFields['ID'], 'DATE', $_POST['START_DATE']);
			CIBlockElement::SetPropertyValueCode($arFields['ID'], 'IZD_SUB', $_POST['IZD_SUBSECTION']);
			$fileID = $arFields['ID'];
		} else {
			$el = new CIBlockElement;
			$fileID = $el->Add(array(
				'MODIFIED_BY' => $USER->GetID(),
				'IBLOCK_SECTION_ID' => false,
				'IBLOCK_ID' => 35,
				'NAME' => $_POST['REGION'] . ' - ' . $_POST['IZD'] . ' - TFS_PRICE',
				'ACTIVE' => 'Y',
				'PROPERTY_VALUES'=> array(
					'REGION' => $_POST['REGION'],
					'IZD' => $_POST['IZD'],
					'DATE' => $_POST['START_DATE'],
					'IZD_SUB' => $_POST['IZD_SUBSECTION'],
					'TYPE' => 'tfs_price',
					'FILE' => $_FILES['PRICE_FILE']
				)
			));
		}

		$fileName = $_SERVER['DOCUMENT_ROOT'] . getTempFileByID($fileID);
		csvTestFile($fileName);
		$arFile = file($fileName, FILE_IGNORE_NEW_LINES);

		// Ищем первую знАчимую строку и рзбираем ее на поля
		$strNum = false;
		$arResult['VALUE_LIST'] = false;
		foreach ($arFile as $key => $str) {
			$str = iconv('windows-1251', 'utf-8', $str);
			$arStr = explode(';', $str); // разбиваем строку на поля
			foreach ($arStr as $value) {	// Ищем поле с корректным ФП
				if (testFPNumber($value)) {
					$strNum = $key;
					$arResult['VALUE_LIST'] = $arStr;
					break;
				}
			}
			if ($strNum !== false) break;
		}

		if ($strNum !== false) {	// Если все нормально - готовим списки полей для выставления соответствия

			// Список вычисляемых полей (не выводить в соответствии)
			$arExProp = array('KEY', 'REGION', 'WORK', 'SUBSECTION');

			// Запрашиваем список свойств каталога
			$arResult['PROP_LIST'] = array();
			$res = CIBlockProperty::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => 5));
			while ($arFields = $res->GetNext())
				if (!in_array($arFields['CODE'], $arExProp))
					$arResult['PROP_LIST'][$arFields['CODE']] = $arFields['~NAME'];

			// Добавляем поле цены
			$arResult['PROP_LIST']['PRICE'] = 'Цена';

			$arResult['FILE_ID'] = $fileID;
			$arResult['PAGE'] = 2;

		} else
			$arResult['ERROR_MESSAGE'] = array('TYPE' => 'ERROR', 'MESSAGE' => 'В файле не найдены строки с корретным номером ФП.<br>Импорт невозможен!');

	} elseif ($_POST['BTN'] == 'LOAD2') {

		$fileID = intval($_POST['FILE_ID']);

		// Записываем массив соответствия полей
		$arFields = array();
		$fpLoad = false;
		foreach ($_POST as $code => $strNum) {
			if (($code != 'PRIM' && strlen($strNum) == 0) || ($code == 'PRIM' && strlen($strNum[0]) == 0)) continue;
			switch ($code) {
				case 'BTN':
				case 'FILE_ID': break;
				case 'FP_NO_LOAD': $fpLoad = true; break;
				case 'PRIM':
					$str = 'PRIM';
					foreach ($strNum as $value) $str .= ';' . $value;
					$arFields[] = $str;
					break;
				case 'NDS_MASK':
					if (strpos($strNum, ';') === false) {
						$arTemp = array();
						if (strlen(trim($strNum)) > 0) $arTemp[] = strtoupper(trim($strNum));
					} else {
						$arTemp = explode(';', strtoupper(trim($strNum)));
					}
					if (count($arTemp) > 0) CIBlockElement::SetPropertyValueCode($fileID, 'NDS_MASK', $arTemp);
					break;
				default: $arFields[] = $code . ';' . $strNum;
			}
		}

		// Записываем массив структуры в DATA
		CIBlockElement::SetPropertyValueCode($fileID, 'DATA', $arFields);
		// Записываем признак загрузки кода ФП
		CIBlockElement::SetPropertyValueCode($fileID, 'FP_LOAD', ($fpLoad ? 'N' : 'Y'));

		$arResult['FILE_ID'] = $fileID;
		$arResult['PAGE'] = 3;

	} else {
		$arResult['PAGE'] = 1;
	}

	$this->IncludeComponentTemplate();
}

/*********************************************************
* Функция вытаскивает путь к файлу из ИБ временных файлов
*********************************************************/
function getTempFileByID($id = false) {
	$result = false;
	if ($id && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 35, 'ID' => $id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_FILE'));
		if ($arFields = $res->Fetch()) $result = CFile::GetPath($arFields['PROPERTY_FILE_VALUE']);
	}
	return $result;
}
?>
