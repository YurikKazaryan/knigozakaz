<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="panel panel-default books-catalog-filter">
	<div class="panel-body">
		<form class="form-inline" name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get">

			<div class="row">
				<div class="col-xs-12">
					<?foreach($arResult["ITEMS"] as $arItem):
						if(array_key_exists("HIDDEN", $arItem)):
							echo $arItem["INPUT"];
						endif;
					endforeach;?>

					<?$cntInput = 0;?>
					<?foreach($arResult["ITEMS"] as $itemKey => $arItem):?>
						<?if(!array_key_exists("HIDDEN", $arItem)):?>
							<?if ($cntInput == 0):?><div class="row filter-row"><?endif;?>
								<div class="col-xs-<?if ($itemKey == 'NAME'):?>8<?else:?>4<?endif;?>">
									<div class="input-group" <?if ($itemKey == 'NAME'):?>style="width:100%;"<?endif;?>>
										<div class="input-group-addon" <?if ($itemKey == 'NAME'):?>style="width:50px;"<?endif;?>><?=$arItem["NAME"]?></div><?=$arItem["INPUT"]?>
									</div>
								</div>
							<?if ($cntInput == 2):?></div><?endif;?>
							<? if (++$cntInput == 3) $cntInput = 0;	?>
						<?endif?>
					<?endforeach;?>
					<?if ($cntInput > 0):?></div><?endif;?>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 buttons-panel">
					<input type="hidden" name="set_filter" value="Y" />
					<button type="submit" class="btn btn-sm btn-success" name="set_filter" value="<?=GetMessage("IBLOCK_SET_FILTER")?>"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span> &nbsp;<?=GetMessage("IBLOCK_SET_FILTER")?></button>
					<button type="submit" class="btn btn-sm btn-danger" name="del_filter" value="<?=GetMessage("IBLOCK_DEL_FILTER")?>"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> &nbsp;<?=GetMessage("IBLOCK_DEL_FILTER")?></button>
				</div>
			</div>
		</form>
	</div>
</div>