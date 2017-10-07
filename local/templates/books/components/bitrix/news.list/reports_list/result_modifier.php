<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	if ($arResult['IS_ADMIN'] = (is_user_in_group(6) || is_user_in_group(7) || is_user_in_group(9))) $arSchoolFilter = array();

	$arIst = get_istochnik_spr();

	$arPeriodList = getPeriodList();

	$arResult['SUM'] = 0;

	foreach ($arResult['ITEMS'] as $key => $arItem) {
		$arResult['ITEMS'][$key]['ISTOCHNIK'] = $arIst[$arItem['PROPERTIES']['ISTOCHNIK']['VALUE']] ? $arIst[$arItem['PROPERTIES']['ISTOCHNIK']['VALUE']]['SHORT'] : 'НЕ УКАЗАН!';
		$arResult['ITEMS'][$key]['DATAPOSTAVKI'] = $arItem['PROPERTIES']['DATAPOSTAVKI']['VALUE'] ? date('d.m.Y', MakeTimeStamp($arItem['PROPERTIES']['DATAPOSTAVKI']['VALUE'])) : '<span class="error">НЕ УКАЗАНА!</span>';
		$arResult['ITEMS'][$key]['DATAPOSTAVKI_VAL'] = $arItem['PROPERTIES']['DATAPOSTAVKI']['VALUE'] ? date('d.m.Y', MakeTimeStamp($arItem['PROPERTIES']['DATAPOSTAVKI']['VALUE'])) : '';
		$arResult['ITEMS'][$key]['SUM'] = sprintf('%.2f', $arItem['PROPERTIES']['SUM']['VALUE']);
		$arResult['ITEMS'][$key]['IZD'] = get_izd_name($arItem['PROPERTIES']['IZD_ID']['VALUE']);
		$arResult['ITEMS'][$key]['STATUS'] = get_spr_name($arItem['PROPERTIES']['STATUS']['VALUE']) . ($arItem['PROPERTIES']['DELETE']['VALUE'] == 1 ? '<br><b>Удаление</b>' : '');
		if ($arResult['IS_ADMIN'] && !in_array($arItem['PROPERTIES']['SCHOOL_ID']['VALUE'], $arSchoolFilter)) $arSchoolFilter[] = $arItem['PROPERTIES']['SCHOOL_ID']['VALUE'];

		// Подсчитываем ИТОГО по странице
		$arResult['SUM'] += $arItem['PROPERTIES']['SUM']['VALUE'];

		// Проверяем на архивный рабочий период (доступность изменений)
		$arResult['ITEMS'][$key]['READONLY'] = $arPeriodList[$arItem['PROPERTIES']['PERIOD']['VALUE']]['ARCHIVE'];
		
		// Обрабатываем комментарий оператора
		if ($arItem['PROPERTIES']['OPER_REM']['VALUE']) {
			$arTemp = explode('@@@', $arItem['PROPERTIES']['OPER_REM']['VALUE']['TEXT']);
			$arResult['ITEMS'][$key]['REMARKS'] = array(
				'REM' => $arTemp[2] . ' (' . $arTemp[1] . ', ' . date('d.m.Y H:i', $arTemp[0]) . ')'
			);
		}

		// Вычисляем следующий статус
		$arTemp = get_status_spr();
		if (($indexTemp = status_search($arItem['PROPERTIES']['STATUS']['VALUE'])) < 5)
			$arResult['ITEMS'][$key]['STATUS_NEXT'] = $arTemp[$indexTemp+1]['NAME'];
		else
			$arResult['ITEMS'][$key]['STATUS_NEXT'] = false;

		// Вычисляем предыдущий статус
		$arTemp = get_status_spr();
		$indexTemp = status_search($arItem['PROPERTIES']['STATUS']['VALUE']);
		if (($indexTemp > 1) && ($indexTemp < 5))
			$arResult['ITEMS'][$key]['STATUS_PREV'] = $arTemp[$indexTemp-1]['NAME'];
		else
			$arResult['ITEMS'][$key]['STATUS_PREV'] = false;
	}

	if ($arResult['IS_ADMIN'] && count($arSchoolFilter) > 0) {
		$arResult['SCHOOL_NAMES'] = array();
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 10, 'ID' => $arSchoolFilter), false, false, array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_MUN'));
		while ($arFields = $res->GetNext())
			$arResult['SCHOOL_NAMES'][$arFields['ID']] = $arFields['NAME'] . ',<br>' . get_izd_name($arFields['PROPERTY_MUN_VALUE']);
	}

	// Вычисляем символ режима сортировки
	$arResult['SORT_MODE'] = '<span class="glyphicon glyphicon-chevron-' . ($_SESSION['ORDERS_SORT_ORDER'] == 'ASC' ? 'up' : 'down') . '" aria-hidden="true"></span> ';
	$arResult['SORT_GET'] = $_SESSION['ORDERS_SORT_GET'];

	// Вычисляем значения фильтров для формы
	if ($_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID.NAME']) $arResult['FILTER_SCH'] = substr($_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID.NAME'], 1, strlen($_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID.NAME']) - 2);
	if ($_SESSION['REPORTS_FILTER']['ID']) $arResult['FILTER_NUM'] = $_SESSION['REPORTS_FILTER']['ID'];
	if ($_SESSION['REPORTS_FILTER']['PROPERTY_IZD_ID']) $arResult['FILTER_IZD'] = $_SESSION['REPORTS_FILTER']['PROPERTY_IZD_ID'];
	if ($_SESSION['REPORTS_FILTER']['PROPERTY_STATUS']) $arResult['FILTER_STATUS'] = $_SESSION['REPORTS_FILTER']['PROPERTY_STATUS'];
	if ($_SESSION['REPORTS_FILTER']['>DATE_ACTIVE_FROM']) $arResult['FILTER_DATE'] = $_SESSION['REPORTS_FILTER']['>DATE_ACTIVE_FROM'];
	if ($_SESSION['REPORTS_FILTER_MUN']) $arResult['FILTER_MUN'] = $_SESSION['REPORTS_FILTER_MUN'];

	// Списки издательств и статусов для фильтра
	$arResult['FILTER_IZD_LIST'] = get_izd_list();
	$arResult['FILTER_MUN_LIST'] = get_mun_list($USER->GetID());

?>