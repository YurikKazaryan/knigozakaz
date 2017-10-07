<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
/******************************************
* Выгрузка каталога издательства в файл CSV
******************************************/
if (1 && $USER->IsAdmin() && CModule::IncludeModule('iblock')) {

	//***** Параметры *****
	$izdID = IZD_PROSV;		// ID издательства
	$fileName = 'izd_download.csv';	// Имя файла

	$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/'.$fileName, 'w');

	// Готовим список свойств для выборки

	$arFields = array(
		0 => array(
			'NAME' => 'IBLOCK_ID',
			'CODE' => 'IBLOCK_ID'
		),
		1 => array(
			'NAME' => 'ID',
			'CODE' => 'ID'
		),
		2 => array(
			'NAME' => 'ID издательства',
			'CODE' => 'IBLOCK_SECTION_ID'
		),
		3 => array(
			'NAME' => 'Название+Автор (для поиска)',
			'CODE' => 'NAME'
		),
	);

	$res = CIBlockProperty::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_BOOKS));

	while ($arResFields = $res->Fetch())
			$arFields[] = array('NAME' => $arResFields['NAME'], 'CODE' => $arResFields['CODE']);

	// Составляем строку со списком полей для GetList() и строку с заголовками для CSV
	$str = '';
	$arFieldsL = array();
	foreach ($arFields as $value) {
		$str .= '"' . str_replace('"', '""', $value['NAME']) . '"' . ';';
		switch ($value['CODE']) {
			case 'IBLOCK_ID':
			case 'ID':
			case 'IBLOCK_SECTION_ID':
			case 'NAME':
				$arFieldsList[] = $value['CODE'];
				break;
			case 'SUBSECTION':
			case 'REGION':
				$arFieldsList[] = 'PROPERTY_'.$value['CODE'].'.NAME';
				break;
			default:
				$arFieldsList[] = 'PROPERTY_'.$value['CODE'];
		}
	}

	fwrite($fp, iconv('utf-8', 'windows-1251', $str.'"Цена";') . "\n");

	// Получаем информацию об учебнике
	$res = CIBlockElement::GetList(
		array('PROPERTY_FP_CODE' => 'asc'),
		array('IBLOCK_ID' => IB_BOOKS, 'SECTION_ID' => $izdID),
		false, false,
		$arFieldsList
	);

	$arReport = array();
	$arBookID = array();

	while ($arResFields = $res->GetNext()) {
		$arBookID[] = $arResFields['ID'];
		$str = '';
		foreach ($arFields as $value) {
			switch ($value['CODE']) {
				case 'IBLOCK_ID':
				case 'ID':
				case 'IBLOCK_SECTION_ID':
				case 'NAME':
					$str .= '"'.str_replace('"','""',$arResFields[$value['CODE']]).'";';
					break;
				case 'SUBSECTION':
				case 'REGION':
					$str .= '"'.str_replace('"','""',$arResFields['PROPERTY_'.$value['CODE'].'_NAME']).'";';
					break;
				default:
					$str .= '"'.str_replace('"','""',$arResFields['PROPERTY_'.$value['CODE'].'_VALUE']).'";';
			}
		}
		$arReport[] = array(
			'BOOK_ID' => $arResFields['ID'],
			'STR' => $str
		);
	}

	// Получаем текущую цену книги
	$arBookPrice = getPrice($arBookID, false, true);

	// Формируем файл
	foreach ($arReport as $arStr)
		fwrite($fp, iconv('utf-8','windows-1251',$arStr['STR'].$arBookPrice[$arStr['BOOK_ID']].';') . "\n");

	fclose($fp);

	echo 'Выгрузка завершена. <a href="/'.$fileName.'" target="_blank">Ссылка на файл</a>';
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>