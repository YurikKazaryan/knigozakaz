<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

/*
* Параметры:
*    $level1 - первый уровень группировки: MUN - по муниципалитетам, IZD - по издательствам
*    $level2 - второй уровень группировки: true/false - вкл/выкл.
*              для первого уровня MUN - будет группировка по издательствам
*              для второго уровня IZD - группировка по муниципалитетам
*    $level3 - третий уровень - группировка по школам, вкл/выкл
*    $level4 - четвертый уровень - группировка по учебникам, вкл/выкл
*    $mode	 - режим выборки: 0-заказы, 1-отчёты, 2-заказы+отчеты
*    $period - ID рабочего периода
*    $self - добавление данных по собственным закупкам
*/
function report_svod($mode = 0, $level1, $level2 = true, $level3 = false, $level4 = true, $period, $self = false) {
	global $USER;
	$arReport = false;

	if (CModule::IncludeModule('iblock')) {
		$arIzd = get_izd_list();
		$arMun = get_mun_list($USER->GetID());

		// Готовим справочник школа - муниципалитет и если включена группировка по школе, то школа - название
		$arSchoolSpr = array();
		$arSchoolName = array();
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 10, 'PROPERTY_OBLAST' => getUserRegion()), false, false, array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_MUN'));
		while ($arFields = $res->GetNext()) {
			$arSchoolSpr[$arFields['ID']] = $arFields['PROPERTY_MUN_VALUE'];
			if ($level3) $arSchoolName[$arFields['ID']] = $arFields['NAME'];
		}

//echo count($arSchoolSpr) . '<br>';
//echo '<pre>' . print_r($arSchoolSpr,1) . '</pre>';
//echo '<pre>' . print_r($arIzd,1) . '</pre>';
//echo '<pre>' . print_r($arMun,1) . '</pre>';

		$arReport = array();

		$level1 = strtoupper($level1);

		// Готовим шаблон для отчета

		switch ($level1) {
			case 'MUN':
				foreach ($arMun as $munID => $munName) {
					$arReport[$munID] = array();
					if ($level2 && strpos('...', $munName) !== false)
						foreach ($arIzd as $izdID => $izdName)
							$arReport[$munID][$izdID] = array();
				}
				break;
			case 'IZD':
				foreach ($arIzd as $izdID => $izdName) {
					$arReport[$izdID] = array();
					if ($level2)
						foreach ($arMun as $munID => $munName)
							$arReport[$izdID][$munID] = array();
				}
				break;
		}

		// Выбираем список учебников для отчета

		if ($mode == 0)
			$arStatusFilter = array('osdocs', 'oscheck', 'oschecked', 'osconfirm', 'osready', 'osaction', 'osclosed');
		elseif ($mode == 1)
			$arStatusFilter = array('osrepready');
		else
			$arStatusFilter = array('osdocs', 'oscheck', 'oschecked', 'osconfirm', 'osready', 'osaction', 'osclosed', 'osrepready');

		// Получаем ID всех книг в отчёте
		$arBookID = array();

		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_STATUS' => $arStatusFilter, 'PROPERTY_PERIOD' => $period, 'PROPERTY_REGION_ID' => getUserRegion()),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_BOOK')
		);
		while ($arFields = $res->Fetch())
			if (!in_array($arFields['PROPERTY_BOOK_VALUE'], $arBookID)) $arBookID[] = $arFields['PROPERTY_BOOK_VALUE'];

		// Получаем информацию о книгах
		$arBookInfo = getBookInfo($arBookID, true);

		// Составляем отчет
		$res = CIBlockElement::GetList(
			array('PROPERTY_FP_CODE' => asc),
			array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_STATUS' => $arStatusFilter, 'PROPERTY_PERIOD' => $period, 'PROPERTY_REGION_ID' => getUserRegion()),
			false, false,
			array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_SCHOOL_ID', 'PROPERTY_IZD_ID', 'PROPERTY_BOOK', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_PRICE_KATALOG')
		);

		while ($arFields = $res->GetNext()) {

			$munID = $arSchoolSpr[$arFields['PROPERTY_SCHOOL_ID_VALUE']];
			$izdID = $arFields['PROPERTY_IZD_ID_VALUE'];
			$schoolID = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
			$bookID = $arFields['PROPERTY_BOOK_VALUE'];

			switch ($level1) {
				case 'MUN':

					if ($level2) {
						if ($level3) {
							if ($level4) {	// мун - изд - школа - книга
								if ($arReport[$munID][$izdID][$schoolID][$bookID]) {
									$arReport[$munID][$izdID][$schoolID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$izdID][$schoolID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$izdID][$schoolID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$izdID][$schoolID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// мун - изд - школа
								if ($arReport[$munID][$izdID][$schoolID]) {
									$arReport[$munID][$izdID][$schoolID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$izdID][$schoolID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$izdID][$schoolID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$izdID][$schoolID] = array(
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							}
						} else {
							if ($level4) {	// мун - изд - книга
								if ($arReport[$munID][$izdID][$bookID]) {
									$arReport[$munID][$izdID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$izdID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$izdID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$izdID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// мун - изд
								if ($arReport[$munID][$izdID]) {
									$arReport[$munID][$izdID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$izdID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$izdID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$izdID] = array(
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							}
						}
					} else {
						if ($level3) {
							if ($level4) {	// мун - школа - книга
								if ($arReport[$munID][$schoolID][$bookID]) {
									$arReport[$munID][$schoolID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$schoolID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$schoolID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$schoolID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// мун - школа
								if ($arReport[$munID][$schoolID]) {
									$arReport[$munID][$schoolID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$schoolID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$schoolID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$schoolID] = array(
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							}
						} else {
							if ($level4) {	// мун - книга
								if ($arReport[$munID][$bookID]) {
									$arReport[$munID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// мун
								if ($arReport[$munID]['COUNT']) {
									$arReport[$munID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$munID]['COUNT'] = $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$munID]['SUM'] = $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$munID]['SUM_KATALOG'] = $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								}
							}
						}
					}
					break;

				case 'IZD':

					if ($level2) {
						if ($level3) {
							if ($level4) {	// изд - мун - школа - книга
								if ($arReport[$izdID][$munID][$schoolID][$bookID]) {
									$arReport[$izdID][$munID][$schoolID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$munID][$schoolID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$munID][$schoolID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$munID][$schoolID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// изд - мун - школа
								if ($arReport[$izdID][$munID][$schoolID]) {
									$arReport[$izdID][$munID][$schoolID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$munID][$schoolID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$munID][$schoolID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$munID][$schoolID] = array(
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							}
						} else {
							if ($level4) {	// изд - мун - книга
								if ($arReport[$izdID][$munID][$bookID]) {
									$arReport[$izdID][$munID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$munID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$munID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$munID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// изд - мун
								if ($arReport[$izdID][$munID]) {
									$arReport[$izdID][$munID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$munID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$munID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$munID] = array(
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							}
						}
					} else {
						if ($level3) {
							if ($level4) {	// изд - школа - книга
								if ($arReport[$izdID][$schoolID][$bookID]) {
									$arReport[$izdID][$schoolID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$schoolID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$schoolID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$schoolID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// изд - школа
								if ($arReport[$izdID][$schoolID]) {
									$arReport[$izdID][$schoolID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$schoolID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$schoolID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$schoolID] = array(
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							}
						} else {
							if ($level4) {	// изд - книга
								if ($arReport[$izdID][$bookID]) {
									$arReport[$izdID][$bookID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID][$bookID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID][$bookID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID][$bookID] = array(
										'NAME' => $arBookInfo[$bookID]['~NAME'],
										'FP_CODE' => $arBookInfo[$bookID]['PROPERTY_FP_CODE_VALUE'],
										'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
										'SUM' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'],
										'SUM_KATALOG' => $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE']
									);
								}
							} else {		// изд
								if ($arReport[$izdID]['COUNT']) {
									$arReport[$izdID]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID]['SUM_KATALOG'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								} else {
									$arReport[$izdID]['COUNT'] = $arFields['PROPERTY_COUNT_VALUE'];
									$arReport[$izdID]['SUM'] = $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
									$arReport[$izdID]['SUM_KATALOG'] = $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_KATALOG_VALUE'];
								}
							}
						}
					}


					break;
			}

		}
	}
	return ($arReport);
}

?>