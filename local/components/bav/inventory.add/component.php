<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('iblock');

if (($arParams['CACHE_TYPE'] == 'N') || $this->StartResultCache($arParams['CACHE_TIME']))
{

	$arResult['MAX_RESULT'] = (intval($arParams['MAX_RESULT']) > 0 ? intval($arParams['MAX_RESULT']) : 10);
	$arResult['USE_PATH'] = $arParams['USE_PATH'];

	$arTemp = getWorkPeriod();
	$arResult['WORK_PERIOD'] = $arTemp['NAME'];

	$arResult['IZD_LIST'] = get_izd_list();

	$this->IncludeComponentTemplate();
}
?>
