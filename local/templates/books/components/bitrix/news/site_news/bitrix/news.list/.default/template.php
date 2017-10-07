<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="news-list">

	<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>

	<?foreach($arResult["ITEMS"] as $arItem):?>
		<div class="row news-title">
			<div class="col-xs-8">
				<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
					<h4><?=$arItem["NAME"];?></h4>
				<?endif;?>
			</div>
			<div class="col-xs-4 text-right news-date">
				<div class="row"><div class="col-xs-12"><?=$arItem["DISPLAY_ACTIVE_FROM"];?></div></div>
				<div class="row"><div class="col-xs-12"><?=getIzdName($arItem['PROPERTIES']['REGION']['VALUE']);?></div></div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-10 col-xs-offset-1 news-text">
				<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
					<?echo $arItem["PREVIEW_TEXT"];?>
				<?endif;?>
			</div>
		</div>
		<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
			<div class="row news-delim">
				<div class="col-xs-12 text-right">
					<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="btn btn-default btn-xs">Подробнее&nbsp;&nbsp;<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> </a>
				</div>
			</div>
		<?endif;?>
		<hr>
	<?endforeach;?>

	<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>

</div>
