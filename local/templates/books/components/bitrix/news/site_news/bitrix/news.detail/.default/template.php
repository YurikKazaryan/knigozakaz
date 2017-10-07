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
<div class="news-detail">

	<div class="row">
		<div class="col-md-12">
			<?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
				<h4><?=$arResult["NAME"];?></h4>
			<?endif;?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 text-right news-date">
			<?if($arParams["DISPLAY_DATE"]!="N" && $arResult["DISPLAY_ACTIVE_FROM"]):?>
				<?=$arResult["DISPLAY_ACTIVE_FROM"];?>
			<?endif?>
		</div>
	</div>


	<?if(strlen($arResult["DETAIL_TEXT"]) > 0):?>
		<div class="row">
			<div class="col-md-12 news-text">
				<?=$arResult["DETAIL_TEXT"];?>
			</div>
		</div>
	<?endif?>
</div>