<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arParams['SILENT'] == 'Y') return;

$cnt = strlen($arParams['INPUT_NAME_FINISH']) > 0 ? 2 : 1;

for ($i = 0; $i < $cnt; $i++):
	if ($arParams['SHOW_INPUT'] == 'Y'):
?>

<div class="input-group input-group-sm">
<input class="form-control izd-svod-date report-control" type="text" id="<?=$arParams['INPUT_NAME'.($i == 1 ? '_FINISH' : '')]?>" name="<?=$arParams['INPUT_NAME'.($i == 1 ? '_FINISH' : '')]?>" value="<?=$arParams['INPUT_VALUE'.($i == 1 ? '_FINISH' : '')]?>" <?=(Array_Key_Exists("~INPUT_ADDITIONAL_ATTR", $arParams)) ? $arParams["~INPUT_ADDITIONAL_ATTR"] : ""?>/><?
	endif;
?>

<span class="input-group-btn"><button type="button" class="btn btn-default report_control" onclick="BX.calendar({node:this, field:'<?=htmlspecialcharsbx(CUtil::JSEscape($arParams['INPUT_NAME'.($i == 1 ? '_FINISH' : '')]))?>', form: '<?if ($arParams['FORM_NAME'] != ''){echo htmlspecialcharsbx(CUtil::JSEscape($arParams['FORM_NAME']));}?>', bTime: <?=$arParams['SHOW_TIME'] == 'Y' ? 'true' : 'false'?>, currentTime: '<?=(time()+date("Z")+CTimeZone::GetOffset())?>', bHideTime: <?=$arParams['HIDE_TIMEBAR'] == 'Y' ? 'true' : 'false'?>});"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></button></span>
</div>


<?endfor;?>