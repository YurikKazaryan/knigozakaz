<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
if (isset($_GET['PAGEN_1'])) $_SESSION['ORDERS_LIST_PAGE'] = $_GET['PAGEN_1'];
?>


<script>showFilter=<?if ($_COOKIE['ORDERS_LIST_FILTER_COLLAPSE'] == 0):?>0<?else:?>1<?endif;?>;</script>

<div class="row"><div class="col-md-12 section-title"><h1>Список заказов</h1><br></div></div>

<div class="row orders-list">
	<div class="col-md-12">

		<?/***** Форма фильтра списка заказов *****/?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-6">
						<b>Фильтр</b>
					</div>
					<div class="col-xs-6 text-right">
						<button type="button" class="btn btn-sm btn-default" onClick="toggleFilter();" id="collapseFilterButton">
							<?if ($_COOKIE['ORDERS_LIST_FILTER_COLLAPSE'] == 0):?>
								<span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Развернуть
							<?else:?>
								<span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span> Свернуть
							<?endif;?>
						</button>
					</div>
				</div>
			</div>
			<div class="panel-body" id="collapseFilter" <?if ($_COOKIE['ORDERS_LIST_FILTER_COLLAPSE'] == 0):?>hidden<?endif;?>>
					<form class="form-inline" name="orders_filter_form" action="/orders/" method="post">
						<div class="row">
							<div class="col-xs-4 filter-col">
								<div class="form-group form-group-sm filter-group" title="ДД.ММ.ГГГГ<?=chr(10);?>Точное соответствие">
									<div class="input-group filter-group">
										<div class="input-group-addon title">Дата заказа</div>
										<?$APPLICATION->IncludeComponent(
											'bitrix:main.calendar',
											'izd_svod_calendar',
											array(
												'SHOW_INPUT' => 'Y',
												'FORM_NAME' => 'orders_filter_form',
												'INPUT_NAME' => 'ORDERS_FILTER_DATE',
												'INPUT_VALUE' => $arResult['FILTER_DATE'],
												'SHOW_TIME' => 'N'
											),
											null,
											array('HIDE_ICONS' => 'Y')
										);?>
									</div>
								</div>
							</div>
							<div class="col-xs-4 filter-col">
								<div class="form-group form-group-sm filter-group">
									<div class="input-group filter-group">
										<div class="input-group-addon title">№ заказа</div><input class="form-control" type="text" name="ORDERS_FILTER_NUM" value="<?=$arResult['FILTER_NUM'];?>" title="Любая часть номера">
									</div>
								</div>
							</div>
							<div class="col-xs-4 filter-col">
								<div class="form-group form-group-sm filter-group" title="ДД.ММ.ГГГГ<?=chr(10);?>или - (минус) для не указанной даты.<?=chr(10);?>Точное соответствие">
									<div class="input-group filter-group">
										<div class="input-group-addon title">Дата поставки</div>
										<?$APPLICATION->IncludeComponent(
											'bitrix:main.calendar',
											'izd_svod_calendar',
											array(
												'SHOW_INPUT' => 'Y',
												'FORM_NAME' => 'orders_filter_form',
												'INPUT_NAME' => 'ORDERS_FILTER_DATE_POST',
												'INPUT_VALUE' => $arResult['FILTER_DATE_POST'],
												'SHOW_TIME' => 'N'
											),
											null,
											array('HIDE_ICONS' => 'Y')
										);?>
									</div>
								</div>
							</div>
						</div>

						<div class="row filter-line">
							<div class="col-xs-4 filter-col">
								<div class="form-group form-group-sm filter-group">
									<div class="input-group filter-group">
										<div class="input-group-addon title">Школа</div><input class="form-control" type="text" name="ORDERS_FILTER_SCH" value="<?=$arResult['FILTER_SCH'];?>" title="Любая часть названия" <?if (is_user_in_group(8)):?>disabled<?endif;?>>
									</div>
								</div>
							</div>
							<div class="col-xs-4 filter-col">
								<div class="form-group form-group-sm filter-group">
									<div class="input-group filter-group">
										<div class="input-group-addon title">Издательство</div>
										<select class="form-control" name="ORDERS_FILTER_IZD">
											<option value="0" <?if (!$arResult['FILTER_IZD']):?>selected<?endif;?>>Все</option>
											<?foreach ($arResult['FILTER_IZD_LIST'] as $key => $value):?>
												<option value="<?=$key;?>" <?if ($key == $arResult['FILTER_IZD']):?>selected<?endif;?>><?=$value;?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-4 filter-col">
								<div class="form-group form-group-sm filter-group">
									<div class="input-group filter-group">
										<div class="input-group-addon title">Статус</div>
										<select class="form-control" name="ORDERS_FILTER_STATUS">
											<option value="" <?if (!$arResult['FILTER_STATUS']):?>selected<?endif;?>>Все</option>
											<?foreach ($arResult['FILTER_STATUS_LIST'] as $value):
												if ($value['VALUE'] == 'oscart') continue;
											?>
												<option value="<?=$value['VALUE'];?>" <?if ($value['VALUE'] == $arResult['FILTER_STATUS']):?>selected<?endif;?>><?=$value['NAME'];?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="row filter-line">
							<div class="col-xs-4 filter-col">
								<?if (is_user_in_group(6) || is_user_in_group(9)):?>
									<div class="form-group form-group-sm filter-group">
										<div class="input-group filter-group">
											<div class="input-group-addon title">Муниципалитет</div>
											<select class="form-control" name="ORDERS_FILTER_MUN">
												<option value="0" <?if (!$arResult['FILTER_MUN']):?>selected<?endif;?>>Все</option>
												<?foreach ($arResult['FILTER_MUN_LIST'] as $key => $value):?>
													<option value="<?=$key;?>" <?if ($key == $arResult['FILTER_MUN']):?>selected<?endif;?>><?=$value;?></option>
												<?endforeach;?>
											</select>
										</div>
									</div>
								<?endif;?>
							</div>
							<div class="col-xs-4 filter-col">
								<?if (is_user_in_group(6)|| is_user_in_group(7) || is_user_in_group(9)):?>
									<div class="form-group form-group-sm filter-group">
										<div class="input-group filter-group">
											<div class="input-group-addon title">Комментарии</div>
											<select class="form-control" name="ORDERS_FILTER_REM">
												<option value="0" <?if (!$arResult['FILTER_REM']):?>selected<?endif;?>>Все заказы</option>
												<option value="1" <?if ($arResult['FILTER_REM'] == 1):?>selected<?endif;?>>Только с комментариями</option>
											</select>
										</div>
									</div>
								<?endif;?>
							</div>
							<div class="col-xs-4 filter-col">
								<?if (is_user_in_group(6) || is_user_in_group(9)):?>
									<div class="form-group form-group-sm filter-group">
										<div class="input-group filter-group">
											<div class="input-group-addon title">Признак удаления</div>
											<select class="form-control" name="ORDERS_FILTER_DELETE">
												<option value="0" <?if (!$arResult['FILTER_DELETE']):?>selected<?endif;?>>Все заказы</option>
												<option value="1" <?if ($arResult['FILTER_DELETE'] == 1):?>selected<?endif;?>>Только на удаление</option>
											</select>
										</div>
									</div>
								<?endif;?>
							</div>
						</div>

						<div class="row filter-line">
							<div class="col-md-12 text-right filter-button">
								<button type="submit" class="btn btn-sm btn-success" name="SET_FILTER" value="SET_FILTER"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span> &nbsp;Фильтр</button>
								<button type="submit" class="btn btn-sm btn-danger" name="SET_FILTER" value="DEL_FILTER"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> &nbsp;Сбросить</button>
							</div>
						</div>

					</form>
			</div>
		</div>
		<?/***** //Форма фильтра списка заказов *****/?>

		<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>

		<?if (is_user_in_group(9)):?>
			<div class="panel panel-default" id="orders_action_panel" hidden>
				<div class="panel-heading"><h3 class="panel-title">Действия с выбранными заказами</h3></div>
				<div class="panel-body text-center">
					<a href="javascript:void(0);" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#groupDataPost"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> &nbsp;Дата поставки</a>
					<button type="button" onClick="orders_pack(1);" class="btn btn-primary btn-sm" id="order_pack_button"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> Скачать</button>
					<button disabled class="btn btn-primary btn-sm" type="submit" name="BUTTON" value="SEND" onClick="return confirm('Вы уверены, что хотите отправить выбранные заказы в издательства автоматически?');"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> &nbsp;Отправить</button>
					<a href="javascript:orders_status(1);" class="btn btn-info btn-sm" onClick="return confirm('Подтвердите групповой сдвиг статусов выбранных заказов!');"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> &nbsp;Следующий статус</a>
					<a href="javascript:orders_status(-1);" class="btn btn-danger btn-sm" onClick="return confirm('Подтвердите групповой возврат статусов выбранных заказов!');"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> &nbsp;Предыдущий статус</a>
				</div>
			</div>
		<?endif;?>

		<table class="table table-striped">

			<thead>
				<tr>
					<th class="order-date-w">
						<a href="/orders/?sort=num">№ заказа <?if ($arResult['SORT_GET'] == 'num'):?><?=$arResult['SORT_MODE']?><?endif;?></a>
						<br>
						<a href="/orders/?sort=date1">Дата <?if ($arResult['SORT_GET'] == 'date1'):?><?=$arResult['SORT_MODE']?><?endif;?></a></th>
					<?if ($arResult['IS_ADMIN']):?>
						<th><a href="/orders/?sort=sch">Школа <?if ($arResult['SORT_GET'] == 'sch'):?><?=$arResult['SORT_MODE']?><?endif;?></a></th>
					<?endif;?>
					<th>Издательство</th>
					<th class="order-sum-w">Сумма</th>
					<th class="istochnik">Источник<br>финансирования</th>
					<th class="dpost"><a href="/orders/?sort=date2">Дата &nbsp;<?if ($arResult['SORT_GET'] == 'date2'):?><?=$arResult['SORT_MODE']?><?endif;?><br>поставки<a></th>
					<th class="order-stat-w" colspan="2">Статус</th>
					<?if (is_user_in_group(9)):?><th class="order-check-w">&nbsp;</th><?endif;?>
					<th class="order-cmd-w">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?foreach($arResult["ITEMS"] as $arItem):?>
					<tr class="news-item">
						<td class="order-date">
							<a name="order<?=$arItem['ID'];?>"></a>
							<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE'].'-'.$arItem['PROPERTIES']['ORDER_NUM']['VALUE']?><br>
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
							<td><?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?></td>
						<?endif;?>
						<td><?=$arItem['IZD']?></td>
						<td class="order-sum"><?=$arItem['SUM']?></td>
						<td class="istochnik">
							<div id="ist_info_<?=$arItem['ID']?>">
								<div id="ist_name_<?=$arItem['ID']?>"><?=$arItem['ISTOCHNIK']?></div>
								<?if (is_user_in_group(8)):?>
									<a class="btn btn-primary btn-xs" href="javascript:change_ist(<?=$arItem['ID']?>,1)" onClick="return confirm('Изменить источник финансирования для заказа №<?=$arItem['ID']?>?')">Изменить</a>
								<?endif;?>
							</div>
							<div id="ist_edit_<?=$arItem['ID']?>" hidden>
								<div class="row">
									<div class="col-md-12">
										<select id="ist_select_<?=$arItem['ID']?>">
											<option value="none">Не указан</option>
											<?$arTemp = get_istochnik_spr();?>
											<?foreach ($arTemp as $key => $value):?>
												<option value="<?=$key?>" <?if ($arItem['PROPERTIES']['ISTOCHNIK']['VALUE'] == $key):?>selected<?endif;?>><?=$value['SHORT']?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<a class="btn btn-primary btn-sm" href="javascript:save_ist(<?=$arItem['ID']?>);" title="Сохранить изменения"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> </a>
										<a class="btn btn-primary btn-sm" href="javascript:change_ist(<?=$arItem['ID']?>,0)" title="Отменить"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
									</div>
								</div>
							</div>
						</td>
						<td class="order-date">
							<div id="dpost_info_<?=$arItem['ID']?>">
								<div id="dpost_name_<?=$arItem['ID']?>"><?=$arItem['DATAPOSTAVKI']?></div>
								<?if (is_user_in_group(9) && !in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osclosed'))):?>
									<a class="btn btn-primary btn-xs" href="javascript:change_dpost(<?=$arItem['ID']?>,1)">Изменить</a>
								<?endif;?>
							</div>
							<div id="dpost_edit_<?=$arItem['ID']?>" hidden>
								<div class="row">
									<div class="col-md-12">
										<input onkeydown="if(event.keyCode==13){save_dpost(<?=$arItem['ID']?>);} if(event.keyCode==27){change_dpost(<?=$arItem['ID']?>,0);}" style="width:68px;" id="dpost_input_<?=$arItem['ID']?>" type="text" placeholder="ДД.ММ.ГГГГ" <?if ($arItem['DATAPOSTAVKI_VAL']):?>value="<?=$arItem['DATAPOSTAVKI_VAL']?>"<?endif;?>>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<div class="btn-group btn-group-sm">
											<a class="btn btn-success" href="javascript:save_dpost(<?=$arItem['ID']?>);" title="Сохранить изменения"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> </a>
											<a class="btn btn-danger" href="javascript:change_dpost(<?=$arItem['ID']?>,0);" title="Отменить"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
										</div>
									</div>
								</div>
							</div>
						</td>
						<td class="order-<?=$arItem['PROPERTIES']['STATUS']['VALUE']?> order-stat-color"></td>
						<td class="order-stat"><?=$arItem['STATUS']?></td>

						<?if (is_user_in_group(9)):?>
							<td>
								<?if (!in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osclosed'))):?>
									<input type="checkbox" name="SELECT[]" value="<?=$arItem['ID']?>" class="orders_select" onClick="javascript:orders_test_selection();">
								<?endif;?>
							</td>
						<?endif;?>

						<td class="order-cmd">

							<?if (is_user_in_group(8)):?>
								<div class="btn-group-vertical btn-group-sm">
									<a class="btn btn-info" href="/orders/view/?order_id=<?=$arItem['ID']?>" title="Просмотр заказа"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> </a>
									<?if (!$arItem['READONLY'] && in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osaction'))):?>
										<button type="button" class="btn btn-primary" onClick="getOrderDocs(<?=$arItem['ID']?>)" title="Скачать пакет документов" id="getOrderPackButton_<?=$arItem['ID']?>"><span class="glyphicon glyphicon-download" aria-hidden="true"></span></button>
									<?endif;?>
									<?if (!$arItem['READONLY'] && in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osdocs'))):?>
										<?if ($arItem['PROPERTIES']['DELETE']['VALUE'] == 1):?>
											<a class="btn btn-info" href="/orders/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=undelete" title="Снять пометку на удаление"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> </a>
										<?else:?>
											<a class="btn btn-danger" href="/orders/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=set_delete" title="Пометить заказ на удаление"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
										<?endif;?>
									<?endif;?>
								</div>

							<?elseif (is_user_in_group(6) || is_user_in_group(7)):?>
								<div class="btn-group-vertical btn-group-sm">
									<a class="btn btn-info" href="/orders/view/?order_id=<?=$arItem['ID']?>" title="Просмотр заказа"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> </a>
									<a class="btn btn-primary" href="javascript:oper_rem(<?=$arItem['ID']?>);" title="Посмотреть комментарий к заказу"><span class="glyphicon glyphicon-comment" aria-hidden="true"></span> </a>
									<?if (!$arItem['READONLY']):?>

										<?if (in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osdocs', 'oscheck', 'osaction', 'oschecked', 'osconfirm'))):?>
											<button type="button" class="btn btn-primary" onClick="getOrderDocs(<?=$arItem['ID']?>)" title="Скачать пакет документов" id="getOrderPackButton_<?=$arItem['ID']?>"><span class="glyphicon glyphicon-download" aria-hidden="true"></span></button>
										<?endif;?>
										<?if (is_user_in_group(6) || $USER->IsAdmin()):?>
											<button type="button" class="btn btn-danger" onClick="order2report(<?=$arItem['ID']?>)" title="Перенос заказа в отчёты"><span class="glyphicon glyphicon-random" aria-hidden="true"></span> </button>
										<?endif;?>
									<?endif;?>
								</div>

							<?elseif (is_user_in_group(9)):?>
								<table>
									<tr>
										<td>
											<div class="btn-group-vertical btn-group-sm">
												<?if (!$arItem['READONLY']):?>
													<a class="btn btn-primary" href="javascript:oper_rem(<?=$arItem['ID']?>);" title="Создать/изменить комментарий к заказу"><span class="glyphicon glyphicon-comment" aria-hidden="true"></span> </a>
												<?endif;?>
												<a class="btn btn-primary" href="/orders/view/?order_id=<?=$arItem['ID']?>" title="Просмотр заказа"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> </a>
												<?if (!$arItem['READONLY']):?>
													<?if (in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osdocs', 'oscheck', 'osaction', 'oschecked', 'osconfirm'))):?>
														<button type="button" class="btn btn-primary" onClick="getOrderDocs(<?=$arItem['ID']?>)" title="Скачать пакет документов" id="getOrderPackButton_<?=$arItem['ID']?>"><span class="glyphicon glyphicon-download" aria-hidden="true"></span></button>
													<?endif;?>
													<?if (in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osdocs', 'oscheck'))):?>
														<a class="btn btn-primary" href="/orders/?order_id=<?=$arItem['ID']?>&m=send" title="Отправить заказ в издательство" onClick="return confirm('Вы хотите отправить в издательство пакет документов по заказу №<?=$arItem['ID']?> школы &quot;<?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?>&quot;?')"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> </a>
													<?endif;?>
												<?endif;?>
											</div>
										</td>
										<td>
											<?if (!$arItem['READONLY']):?>
												<div class="btn-group-vertical btn-group-sm">
													<?if ($arItem['STATUS_NEXT'] !== false):?>
														<a class="btn btn-info" href="/orders/?order_id=<?=$arItem['ID']?>&m=next_status" title="Установить статус '<?=$arItem['STATUS_NEXT']?>'" onClick="return confirm('Присвоить заказу №<?=$arItem['ID']?> статус \'<?=$arItem['STATUS_NEXT']?>\'');"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> </a>
													<?endif;?>
													<?if ($arItem['STATUS_PREV'] !== false):?>
														<a class="btn btn-danger" href="/orders/?order_id=<?=$arItem['ID']?>&m=prev_status" title="Установить статус '<?=$arItem['STATUS_PREV']?>'" onClick="return confirm('Вернуть заказу №<?=$arItem['ID']?> статус \'<?=$arItem['STATUS_PREV']?>\'');"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> </a>
													<?endif;?>
													<?if ($arItem['PROPERTIES']['DELETE']['VALUE'] == 1):?>
														<a class="btn btn-danger" href="/orders/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=undelete" title="Снять пометку на удаление"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> </a>
													<?endif;?>
													<a class="btn btn-danger" href="/orders/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=delete" title="Удалить заказ (вернуть в Корзину)" onClick="return confirm('Вы хотите удалить заказ №<?=$arItem['ID']?> и вернуть его содержимое в Корзину школы &quot;<?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?>&quot;?')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
													<?if (in_array($arItem['PROPERTIES']['STATUS']['VALUE'], array('osdocs', 'oscheck', 'osaction'))):?>
														<a class="btn btn-danger" href="/orders/?order_id=<?=$arItem['ID']?>&sch_id=<?=$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']?>&m=delete_full" title="Удалить заказ (полностью)" onClick="return confirm('Вы хотите ПОЛНОСТЬЮ удалить заказ №<?=$arItem['ID']?> школы &quot;<?=$arResult['SCHOOL_NAMES'][$arItem['PROPERTIES']['SCHOOL_ID']['VALUE']]?>&quot;?')"><span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span> </a>
													<?endif;?>
												</div>
											<?endif;?>
										</td>
									</tr>
								</table>
							<?endif;?>

						</td>
					</tr>
				<?endforeach;?>
			</tbody>
		</table>

		<?if (is_user_in_group(7)):?>
			<div class="modal fade edit-panel" id="oper_rem" tabindex="-1" role="dialog" aria-labelledby="oper_rem_label" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="oper_rem_title">Работа с комментарием</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">Автор:</label>
								<span class="form-control_static" id="oper_rem_author"></span>
							</div>
							<div class="form-group">
								<label class="control-label">Дата:</label>
								<span class="form-control_static" id="oper_rem_date"></span>
							</div>
							<div class="form-group">
								<label for="groupDpost">Текст комментария</label>
								<textarea class="form-control " id="oper_rem_text" name="oper_rem_text" onkeydown="if(event.keyCode==13){void(0);}" disabled></textarea>
							</div>
							<input type="hidden" id="oper_rem_order_id">
							<a href="javascript:void(0);" class="btn btn-default" data-dismiss="modal">Закрыть</a>
						</div>
					</div>
				</div>
			</div>
		<?endif;?>

		<?if (is_user_in_group(9)):?>

			<div class="modal fade edit-panel" id="groupDataPost" tabindex="-1" role="dialog" aria-labelledby="groupDataPostLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Групповое изменение даты поставки</h4>
						</div>
						<div class="modal-body">
							<input type="hidden" name="MODE" value="GROUPDPOST">
							<input type="hidden" name="SCHOOL_ID" value="<?=$arResult['ID']?>">
							<div class="form-group">
								<label for="groupDpost">Введите дату поставки для выбранных заказов<br>(для удаления даты поставки оставьте поле пустым)</label>
								<input type="text" class="form-control" id="groupDpost" placeholder="ДД.ММ.ГГГГ или -" name="groupDpost" onkeydown="if(event.keyCode==13){save_group_dpost();}">
							</div>
							<a href="javascript:void(0);" class="btn btn-default" data-dismiss="modal">Отменить</a>
							<a href="javascript:void(0);" onClick="return save_group_dpost();" class="btn btn-primary">Сохранить</a>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade edit-panel" id="oper_rem" tabindex="-1" role="dialog" aria-labelledby="oper_rem_label" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="oper_rem_title">Работа с комментарием</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label">Автор:</label>
								<span class="form-control_static" id="oper_rem_author"></span>
							</div>
							<div class="form-group">
								<label class="control-label">Дата:</label>
								<span class="form-control_static" id="oper_rem_date"></span>
							</div>
							<div class="form-group">
								<label for="groupDpost">Текст комментария</label>
								<textarea class="form-control " id="oper_rem_text" name="oper_rem_text" onkeydown="if(event.keyCode==13){void(0);}" disabled></textarea>
							</div>
							<input type="hidden" id="oper_rem_order_id">
							<a href="javascript:void(0);" class="btn btn-default" data-dismiss="modal">Отменить</a>
							<a href="javascript:void(0);" class="btn btn-primary" onClick="oper_rem_save(1)">Сохранить</a>
							<a href="javascript:void(0);" class="btn btn-danger" onClick="oper_rem_save(0)">Удалить</a>
						</div>
					</div>
				</div>
			</div>

		<?endif;?>

		<?if (is_user_in_group(6)):?>

			<div class="modal fade edit-panel" id="order2report" tabindex="-1" role="dialog" aria-labelledby="order2reportLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="order2reportLabel">Перенос заказа в отчёты</h4>
						</div>
						<div class="modal-body text-center" id="order2report_id"></div>
						<div class="modal-footer">
							<a href="" class="btn btn-danger" id="order2report_link">Перенести</a>
							<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
						</div>
					</div>
				</div>
			</div>

		<?endif;?>

		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>

	</div>
</div>
