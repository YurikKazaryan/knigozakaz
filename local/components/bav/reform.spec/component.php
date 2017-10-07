<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('iblock');

if (($arParams['CACHE_TYPE'] == 'N') || $this->StartResultCache($arParams['CACHE_TIME'])) {

	$arResult['MUN_LIST'] = getMunList($USER->GetID());
	$arResult['WORK_PERIOD'] = getWorkPeriod();
	$arResult['IZD_LIST'] = getIzdList();

	$this->IncludeComponentTemplate();
}

?>
