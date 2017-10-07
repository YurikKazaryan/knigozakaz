<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

/****************************************************************
* Формирование массива для сводного отчета по Просвещению (2017)
****************************************************************/
function report_prosv_new($munID, $period, $izdID = IZD_PROSV, $startDate = false, $useSection = true) {

	global $USER;

	if (CModule::IncludeModule('iblock')) {

		if ($useSection) $arSubSections = getSubsections($izdID);

		// Выбираем все школы муниципалитета
		$arMun = get_mun_id_for_filter($munID);

		$arSchools = array();
		$arFilter = array();
		$res = CIBlockElement::GetList(
			array('PROPERTY_MUN' => 'asc'),
			array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_MUN' => $arMun),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_MUN')
		);
		while ($arFields = $res->Fetch()) {
			$arFilter[$arFields['ID']] = $arFields['ID'];
			$arSchools[$arFields['ID']] = false;
		}

		// Выбираем школы, которые сделали заказы для издательства
		$arOrderFilter = array('IBLOCK_ID' => IB_ORDERS_LIST, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_SCHOOL_ID' => $arFilter, '!PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $period);
		if ($startDate) $arOrderFilter['>=DATE_ACTIVE_FROM'] = ConvertTimeStamp($startDate);

		$arOrderList = array();
		$arTemp = array();

		$res = CIBlockElement::GetList(
			false,
			$arOrderFilter,
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID')
		);
		while ($arFields = $res->GetNext()) {
			$arOrderList[] = $arFields['ID'];
			$arTemp[] = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
		}

		// Убираем школы, которые не работают с издательством
		foreach ($arSchools as $key => $arSchool)
			if (!in_array($key, $arTemp)) {
				unset($arFilter[$key]);
				unset($arSchools[$key]);
		}

		// Загружаем информацию о школах, оставшихся в отчёте
		foreach ($arSchools as $key => $value)
			$arSchools[$key] = getSchoolInfo($key);

		// Получаем список заказанных книг
		$arReport = array();

		if (count($arFilter) > 0) {

			$arBookFilter = array(
				'IBLOCK_ID' => IB_ORDERS,
				'PROPERTY_IZD_ID' => $izdID,
				'PROPERTY_SCHOOL_ID' => $arFilter,
				'!PROPERTY_STATUS' => 'osrepready',
				'PROPERTY_PERIOD' => $period,
				'PROPERTY_ORDER_NUM' => $arOrderList
			);

			// Составляем список ID книг для получения информации из каталога

			$arBookList = array();
			$res = CIBlockElement::GetList(false, $arBookFilter, false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_BOOK'));
			while ($arFields = $res->fetch())
				if (!in_array($arFields['PROPERTY_BOOK_VALUE'], $arBookList)) $arBookList[] = $arFields['PROPERTY_BOOK_VALUE'];

			$arBooks = getBookInfo($arBookList, true);

			$arBookPrice = getPrice($arBookList, false, true);

			// Если есть подсекции, готовим массив для подсчета итого по школам
			if (is_array($arSubSections)) {
				$arSubCount = array();
				foreach ($arSubSections as $subValue)
					foreach ($arSchools as $key => $value)
						$arSubCount[$subValue['SUB_ID']][$key] = 0;
			}

			$res = CIBlockElement::GetList(false, $arBookFilter, false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_BOOK', 'PROPERTY_COUNT', 'PROPERTY_SCHOOL_ID'));

			while ($arFields = $res->GetNext()) {

				$keyValue = $arBooks[$arFields['PROPERTY_BOOK_VALUE']]['PROPERTY_CODE_1C_VALUE'];

				if (is_array($arSubSections)) {
					$subID = $arBooks[$arFields['PROPERTY_BOOK_VALUE']]['PROPERTY_SUBSECTION_VALUE'];
					if (!isset($arReport[$subID][$keyValue])) {
						$arReport[$subID][$keyValue] = array();
						foreach ($arSchools as $key => $value)
							$arReport[$subID][$keyValue][$key] = 0;
					}
					$arReport[$subID][$keyValue][$arFields['PROPERTY_SCHOOL_ID_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
					$arSubCount[$subID][$arFields['PROPERTY_SCHOOL_ID_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
				} else {
					if (!isset($arReport[$keyValue])) {
						$arReport[$keyValue] = array();
						foreach ($arSchools as $key => $value)
							$arReport[$keyValue][$key] = 0;
					}
					$arReport[$keyValue][$arFields['PROPERTY_SCHOOL_ID_VALUE']] += $arFields['PROPERTY_COUNT_VALUE'];
				}
			}

			// Если отчет разбит на подсекции, нужно убрать из разделов школы, у которых нет заказов в конкретной подсекции
			if (is_array($arSubSections)) {
				foreach ($arSubCount as $subID => $arSch) {
					foreach ($arSch as $schID => $schCount) {
						if (!$schCount) {	// Если в данной подсекции школа не заказала ничего - удаляем школу из подсекции
							foreach ($arReport[$subID] as $bookCode => $arBookList) {
								unset($arReport[$subID][$bookCode][$schID]);
							}
						}
					}
				}
			}

		}
	}

	// Считаем данные для показа статистики
	if (is_array($arSubSections)) {
		$arStat = array('ALL_SCHOOL' => 0);
		foreach ($arSubSections as $arSec) {
			$cnt = count($arReport[$arSec['SUB_ID']]);
			$arStat[$arSec['SUB_ID']] = $cnt;
			$arStat['ALL_SCHOOL'] += $cnt;
		}
	} else
		$arStat = array('ALL_SCHOOL' => count(reset($arReport)));
	$arStat['MAX_PROGRESS'] = $arStat['ALL_SCHOOL'];
	$arStat['CUR_PROGRESS'] = 0;

	return array(
		'REPORT' => $arReport,
		'SCHOOLS' => $arSchools,
		'SECTIONS' => $arSubSections,
		'STAT' => $arStat,
		'BOOK_PRICE' => $arBookPrice
	);
}

?>