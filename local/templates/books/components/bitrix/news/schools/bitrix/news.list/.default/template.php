<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="row"><div class="col-xs-12 text-center"><h2>Список школ</h2></div></div>

<div class="schools-list">

	<?if (is_user_in_group(6) || is_user_in_group(7)):?>
		<div class="row">
			<div class="col-xs-12 text-right add-button">
				<a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#schoolNew"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> &nbsp;Добавить школу</a>
			</div>
		</div>

		<div class="modal fade edit-panel" id="schoolNew" tabindex="-1" role="dialog" aria-labelledby="schoolNewLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Добавление новой школы</h4>
					</div>
					<div class="modal-body">
						<form role="form" id="schoolNewForm" method="POST" action="/schools/">
							<input type="hidden" name="MODE" value="NEWSCHOOL">

							<div class="form-group">
								<label for="schoolMun">Принадлежность к муниципалитету</label>
								<select class="form-control" name="schoolMun">
									<option value="" <?if (count($arResult['MUN_LIST']) > 1):?>selected<?endif;?>>Не выбран</option>
									<?foreach ($arResult['MUN_LIST'] as $key => $value):?>
										<option value="<?=$key;?>" <?if (count($arResult['MUN_LIST']) == 1):?>selected<?endif;?>><?=$value;?></option>
									<?endforeach;?>
								</select>
							</div>

							<div class="form-group">
								<label for="schoolName">Краткое наименование новой школы</label>
								<input type="text" class="form-control" id="schoolName" placeholder="Введите краткое наименование школы" name="NAME">
							</div>
							<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
							<button type="submit" class="btn btn-primary">Сохранить</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	<?endif;?>

	<?if (is_user_in_group(6) || is_user_in_group(7) || is_user_in_group(9)):?>
		<div class="panel panel-default">
			<div class="panel-body">
				<form class="form-inline" method="post" action="/schools/">
					<div class="row">
						<div class="col-xs-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Название школы:</div>
									<input class="form-control" type="text" name="FILTER_SCHOOL_NAME" value="<?=str_replace('%', '', $_SESSION['SCHOOLS_FILTER']['NAME'])?>">
								</div>
							</div>
						</div>
						<div class="col-xs-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Муниципалитет:</div>
									<select class="form-control" name="FILTER_MUN">
										<?foreach ($arResult['MUN_LIST'] as $key => $value):?>
											<option value="<?=$key?>" <?if ($_SESSION['SCHOOLS_FILTER_MUN'] == $key):?>selected<?endif;?>><?=$value?></option>
										<?endforeach;?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row filter-line">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-sm btn-success" name="SET_FILTER" value="SET_FILTER"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span> &nbsp;Фильтр</button>
							<button type="submit" class="btn btn-sm btn-danger" name="SET_FILTER" value="DEL_FILTER"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> &nbsp;Сбросить</button>
						</div>
					</div>


				</form>
			</div>
		</div>
	<?endif;?>

	<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>

	<table class="table table-striped">
		<?foreach($arResult["ITEMS"] as $arItem):?>
			<?
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
			?>
			<tr class="news-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
				<td>
					<div class="row">
						<div class="col-xs-12">
							<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>" title="<?=$arItem["PROPERTIES"]["FULL_NAME"]["VALUE"]?>"><b><?echo $arItem["NAME"]?></b></a>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-11 col-xs-offset-1 full-name">
							<b>Муниципалитет: <?=get_obl_name($arItem['PROPERTIES']['MUN']['VALUE'])?></b>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-11 col-xs-offset-1 full-name">
							<?=$arItem['ADR_STRING']?>
						</div>
					</div>
				</td>

				<?if (is_user_in_group(6) || is_user_in_group(7) || is_user_in_group(9)):?>
					<td width="50">
						<?if ($arItem['BASKET']):?>
							<a class="btn btn-primary" href="/orders/basket/?sch_id=<?=$arItem['ID']?>&back=/schools/"><span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> </a>
						<?else:?>
							&nbsp;
						<?endif;?>
					</td>
				<?endif;?>

			</tr>
		<?endforeach;?>
	</table>

	<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>

</div>
