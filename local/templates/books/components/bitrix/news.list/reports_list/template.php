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

<div class="row text-center"><div class="col-md-12"><h2>Список отчётов</h2><br></div></div>

<div class="row orders-list">
	<div class="col-md-12">

		<?/***** Форма фильтра списка отчетов *****/?>
		<div class="panel panel-default">
			<div class="panel-body">
				<form class="form-inline" name="orders_filter_form" action="/reports/" method="post">

					<div class="row">
						<div class="col-md-4">
							<div class="form-group form-group-sm">
								<div class="input-group">
									<div class="input-group-addon title">Дата отчёта:</div><input class="form-control" type="text" name="ORDERS_FILTER_DATE" value="<?=$arResult['FILTER_DATE'];?>" title="<?=GetMessage('ORDERS_LIST_DATE_FORMAT')?><?=chr(10);?><?=GetMessage('ORDERS_LIST_FILTER_REM1')?>">
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-group-sm">
								<div class="input-group">
									<div class="input-group-addon title">Номер отчёта:</div><input class="form-control" type="text" name="ORDERS_FILTER_NUM" value="<?=$arResult['FILTER_NUM'];?>" title="<?=GetMessage('ORDERS_LIST_FILTER_REM1')?>">
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-group-sm">
								<div class="input-group">
									<div class="input-group-addon title">Муниципалитет:</div>
									<select class="form-control" name="ORDERS_FILTER_MUN">
										<option value="0" <?if (!$arResult['FILTER_MUN']):?>selected<?endif;?>>Все</option>
										<?foreach ($arResult['FILTER_MUN_LIST'] as $key => $value):?>
											<option value="<?=$key;?>" <?if ($key == $arResult['FILTER_MUN']):?>selected<?endif;?>><?=$value;?></option>
										<?endforeach;?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row filter-line">
						<div class="col-md-4">
							<div class="form-group form-group-sm">
								<div class="input-group">
									<div class="input-group-addon title">Школа:</div><input class="form-control school-filter" type="text" name="ORDERS_FILTER_SCH" value="<?=$arResult['FILTER_SCH'];?>" title="<?=GetMessage('ORDERS_LIST_FILTER_REM3')?>" <?if (is_user_in_group(8)):?>disabled<?endif;?>>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-group-sm">
								<div class="input-group">
									<div class="input-group-addon title">Издательство:</div>
									<select class="form-control" name="ORDERS_FILTER_IZD">
										<option value="0" <?if (!$arResult['FILTER_IZD']):?>selected<?endif;?>>Все</option>
										<?foreach ($arResult['FILTER_IZD_LIST'] as $key => $value):?>
											<option value="<?=$key;?>" <?if ($key == $arResult['FILTER_IZD']):?>selected<?endif;?>><?=$value;?></option>
										<?endforeach;?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							&nbsp;
						</div>
					</div>

					<div class="row filter-line">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-sm btn-success" name="SET_FILTER" value="SET_FILTER"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span> &nbsp;<?=GetMessage('ORDERS_LIST_FILTER')?></button>
							<button type="submit" class="btn btn-sm btn-danger" name="SET_FILTER" value="DEL_FILTER"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> &nbsp;<?=GetMessage('ORDERS_LIST_FILTER_CLEAR')?></button>
						</div>
					</div>

				</form>
			</div>
		</div>
		<?/***** //Форма фильтра списка отчетов *****/?>

		<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>

		<table class="table table-striped">

			<thead>
				<tr>
					<th class="order-date-w">
						<a href="/reports/?sort=num"><?=GetMessage('ORDERS_LIST_ORDERS_NUM')?> <?if ($arResult['SORT_GET'] == 'num'):?><?=$arResult['SORT_MODE']?><?endif;?></a>
						<br>
						<a href="/reports/?sort=date1"><?=GetMessage('ORDERS_LIST_DATE')?> <?if ($arResult['SORT_GET'] == 'date1'):?><?=$arResult['SORT_MODE']?><?endif;?></a></th>
					<?if ($arResult['IS_ADMIN']):?>
						<th><a href="/reports/?sort=sch"><?=GetMessage('ORDERS_LIST_SCHOOL')?> <?if ($arResult['SORT_GET'] == 'sch'):?><?=$arResult['SORT_MODE']?><?endif;?></a></th>
					<?endif;?>
					<th><?=GetMessage('ORDERS_LIST_IZD')?></th>
					<th class="order-sum-w"><?=GetMessage('ORDERS_LIST_SUM')?></th>
					<th class="order-stat-w" colspan="2"><?=GetMessage('ORDERS_LIST_STATUS')?></th>
					<th class="order-cmd-w">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?foreach($arResult["ITEMS"] as $arItem):?>
					<tr class="news-item">
						<td class="order-date">
							<?=$arItem['ID']?><br>
							<?=date('d.m.Y', MakeTimeStamp($arItem['DATE_ACTIVE_FROM']))?>
							<?if (is_user_in_group(9) || is_user_in_group(6)):?>
								<br>
								<div id="rem_flag_<?=$arItem['ID']?>" <?if (!is_array($arItem['REMARKS'])):?>hidden<?endif;?>>
									<button type="button" class="btn btn-default" data-toggle="tooltip" title="<?=$arItem['REMARKS']['REM']?>" data-placement="left">
										<span class="glyphicon glyphicon-flag rem-flag" aria-hidden="true"></span>
									</button>
								</div>
							<?endif;?>
						</td>
						<?if ($arResult['IS_ADMIN']):?>
							<td>
								<?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?>
							</td>
						<?endif;?>
						<td><?=$arItem['IZD']?></td>
						<td class="order-sum"><?=$arItem['SUM']?></td>
						<td width="10" class="order-<?=$arItem['PROPERTIES']['STATUS']['VALUE']?>">&nbsp;</td>
						<td class="order-stat"><?=$arItem['STATUS']?></td>

						<td class="order-cmd">

							<?if (is_user_in_group(8)):?>
								<div class="btn-group-vertical btn-group-sm">
									<a class="btn btn-info" href="/reports/view/?order_id=<?=$arItem['ID']?>&back=/reports/" title="Просмотр отчета"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> </a>
									<?if (!$arItem['READONLY']):?>
										<?if ($arItem['PROPERTIES']['DELETE']['VALUE'] == 1):?>
											<a class="btn btn-info" href="/reports/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=undelete" title="Снять пометку на удаление"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> </a>
										<?else:?>
											<a class="btn btn-danger" href="/reports/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=set_delete" title="Пометить отчет на удаление"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
										<?endif;?>
									<?endif;?>
								</div>

							<?elseif (is_user_in_group(6) || is_user_in_group(7) || is_user_in_group(9)):?>
								<div class="btn-group-vertical btn-group-sm">
									<a class="btn btn-info" href="/reports/view/?order_id=<?=$arItem['ID']?>&back=/reports/" title="Просмотр отчёта"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> </a>
									<?if (!$arItem['READONLY']):?>
										<a class="btn btn-danger" href="/reports/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=delete" title="Удалить отчёт (вернуть на редактирование)" onClick="return confirm('Вы хотите вернуть отчёт №<?=$arItem['ID']?> школы &quot;<?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?>&quot; на редактирование?')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
										<?if (in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osrepready'))):?>
											<a class="btn btn-danger" href="/reports/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=delete_full" title="Удалить отчёт (полностью)" onClick="return confirm('Вы хотите ПОЛНОСТЬЮ удалить отчёт №<?=$arItem['ID']?> школы &quot;<?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?>&quot;?')"><span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span> </a>
										<?endif;?>
									<?endif;?>
								</div>

							<?endif;?>
						</td>
					</tr>
				<?endforeach;?>
			</tbody>
		</table>

		<div class="row">
			<div class="col-xs-12 text-center">
				<h4>Итого сумма по листу: <?=sprintf('%.2f', $arResult['SUM'])?> руб.</h4>
			</div>
		</div>

		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>

	</div>
</div>
