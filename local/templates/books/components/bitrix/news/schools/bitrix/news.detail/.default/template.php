<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
ini_set('precision',30);
?>

<div class="school-detail">

	<?if ($arResult['ACCESS_MODE'] == 1 || $arResult['ACCESS_MODE'] == 2 || $arResult['ACCESS_MODE'] == 3):?>

		<form role="form" method="POST" action="/schools/<?=$arResult['ID']?>/">

			<input type="hidden" name="MODE" value="INFO">

			<div class="text-right">
				<button type="submit" class="btn btn-primary" name="btnAction" value="SAVE" id="btnSave" disabled><span class="glyphicon glyphicon-ok"></span> Сохранить</button>
				<button type="submit" class="btn btn-primary" name="btnAction" value="CANCEL" id="btnCancel" disabled><span class="glyphicon glyphicon-remove"></span> Отменить</button>
				<input type="hidden" name="ID" value="<?=$arResult['ID']?>">
			</div>

	<?endif;?>

		<!-- Табы -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#organisation" role="tab" data-toggle="tab">Основные сведения</a></li>
			<li role="presentation"><a href="#address" role="tab" data-toggle="tab">Адрес</a></li>
			<li role="presentation"><a href="#rekv" role="tab" data-toggle="tab">Реквизиты</a></li>
			<li role="presentation"><a href="#admin" role="tab" data-toggle="tab">Администраторы</a></li>
			<?if (CSite::InGroup(array(6))):?>
				<li role="presentation"><a href="#remove" role="tab" data-toggle="tab">Удаление школы</a></li>
			<?endif;?>
		</ul>
		<!-- // Табы -->

		<!-- // Содержание табов -->
		<div class="tab-content">

			<!-- Организация -->
				<div role="tabpanel" class="tab-pane fade in active" id="organisation">

					<div class="form-group" style="padding: 10px 0 0 0;">
						<label for="orgMun">Муниципалитет</label>
						<?if ($arResult['ACCESS_MODE'] == 2 || $arResult['ACCESS_MODE'] == 3):?>
							<select class="form-control" name="MUN" id="orgMun" onchange="javascript:save_enable();">
								<?foreach ($arResult['MUN_LIST'] as $key => $value):?>
									<option value="<?=$key?>"<?if ($arResult['PROPERTIES']['MUN']['VALUE'] == $key):?> selected<?endif;?>><?=$value;?></option>
								<?endforeach;?>
							</select>
						<?else:?>
							<input type="text" class="form-control" id="orgMun" name="MUN" value="<?=$arResult['MUN_NAME']?>" <?if ($arResult['ACCESS_MODE'] != 3):?>disabled<?endif;?>>
						<?endif;?>
					</div>

					<div class="form-group" style="padding: 10px 0 0 0;">
						<label for="orgTitle">Сокращённое наименование</label>
						<input type="text" class="form-control" id="orgTitle" name="NAME" value="<?=$arResult['NAME']?>" <?if ($arResult['ACCESS_MODE'] == 0):?>disabled<?endif;?> onchange="javascript:save_enable();">
					</div>
					<div class="form-group">
						<label for="orgTitleFull">Полное наименование</label>
						<textarea class="form-control full-name" id="orgTitleFull" name="FULL_NAME" rows="5" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();"><?=$arResult['PROPERTIES']['FULL_NAME']['VALUE']?></textarea>
					</div>

					<div class="row">
						<div class="col-xs-6">
							<label for="orgStatus">По какому закону производятся закупки</label>
							<select class="form-control" name="STATUS" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
								<option value="0">- Не выбрано -</option>
								<?foreach ($arResult['STATUS_SPR'] as $arStatus):?>
									<option value="<?=$arStatus['VALUE']?>" <?if ($arStatus['VALUE'] == $arResult['PROPERTIES']['PUNKT_FZ']['VALUE']):?>selected<?endif;?>><?=$arStatus['NAME']?></option>
								<?endforeach;?>
							</select>
						</div>
						<div class="col-xs-6">
							<label for="orgStatus">Тип школы</label>
							<select class="form-control" name="TYPE" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
								<option value="0">- Не выбрано -</option>
								<?foreach ($arResult['TYPE_SPR'] as $arType):?>
									<option value="<?=$arType['VALUE']?>" <?if ($arType['VALUE'] == $arResult['PROPERTIES']['STATUS']['VALUE']):?>selected<?endif;?>><?=$arType['NAME']?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>

					<div class="row" style="padding: 10px 0 0 0;">
						<div class="col-xs-6">
							<div class="form-group">
								<label for="orgPhone">Телефон</label>
								<input type="text" class="form-control" id="orgPhone" name="PHONE" value="<?=$arResult['PROPERTIES']['PHONE']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-6">
							<div class="form-group">
								<label for="orgEmail">E-mail</label>
								<input type="text" class="form-control" id="orgEmail" name="EMAIL" value="<?=$arResult['PROPERTIES']['EMAIL']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="orgDirFIO">Фамилия Имя Отчество директора организации (в именительном падеже)</label>
						<input type="text" class="form-control" id="orgDirFIO" name="DIR_FIO" value="<?=$arResult['PROPERTIES']['DIR_FIO']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
					</div>
					<div class="form-group">
						<label for="orgDirFIOR">Фамилия Имя Отчество директора организации (в родительном падеже)</label>
						<input type="text" class="form-control" id="orgDirFIOR" name="DIR_FIO_R" value="<?=$arResult['PROPERTIES']['DIR_FIO_R']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
					</div>
					<div class="form-group">
						<label for="orgOsn">Директор действует на основании (в родительном падеже)</label>
						<input type="text" class="form-control" id="orgOsn" name="DIR_DOC" value="<?=$arResult['PROPERTIES']['DIR_DOC']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
					</div>
					<div class="form-group">
						<label for="orgOtvDolg">Отвественный за заказы (должность, фио, телефон)</label>
						<div class="row">
							<div class="col-xs-3">
								<input type="text" class="form-control" id="orgOtvDolg" name="OTV_DOLG" value="<?=$arResult['PROPERTIES']['OTV_DOLG']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
							<div class="col-xs-6">
								<input type="text" class="form-control" id="orgOtvFio" name="OTV_FIO" value="<?=$arResult['PROPERTIES']['OTV_FIO']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
							<div class="col-xs-3">
								<input type="text" class="form-control" id="orgOtvPhone" name="OTV_PHONE" value="<?=$arResult['PROPERTIES']['OTV_PHONE']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
				</div>
			<!-- // Организация -->

			<!-- Адрес -->
				<div role="tabpanel" class="tab-pane fade" id="address">
					<div class="form-group">
						<div class="row">
							<div class="col-xs-4">
								<label for="orgIndex">Почтовый индекс</label>
								<input type="text" class="form-control" id="orgIndex" name="INDEX" value="<?=$arResult['PROPERTIES']['INDEX']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
							<div class="col-xs-8">
								<label for="orgOblast">Область</label>
								<input type="text" class="form-control" id="orgOblast" name="OBLAST" value="<?=$arResult['OBLAST_NAME']?>" disabled>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-xs-6">
								<label for="orgRajon">Район области (город)</label>
								<input type="text" class="form-control" id="orgRajon" name="RAJON" value="<?=$arResult['PROPERTIES']['RAJON']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
							<div class="col-xs-6">
								<label for="orgPunkt">Населённый пункт</label>
								<input type="text" class="form-control" id="orgPunkt" name="PUNKT" value="<?=$arResult['PROPERTIES']['PUNKT']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-xs-5">
								<label for="orgRajonGoroda">Район города</label>
								<input type="text" class="form-control" id="orgRajonGoroda" name="RAJON_GORODA" value="<?=$arResult['PROPERTIES']['RAJON_GORODA']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
							<div class="col-xs-5">
								<label for="orgUlica">Улица</label>
								<input type="text" class="form-control" id="orgUlica" name="ULICA" value="<?=$arResult['PROPERTIES']['ULICA']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
							<div class="col-xs-2">
								<label for="orgDom">Дом</label>
								<input type="text" class="form-control" id="orgDom" name="DOM" value="<?=$arResult['PROPERTIES']['DOM']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>

				</div>
			<!-- // Адрес -->

			<!-- Реквизиты -->
				<div role="tabpanel" class="tab-pane fade" id="rekv">
					<div class="row">
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgOgrn">ОГРН</label>
								<input type="text" class="form-control" id="orgOgrn" name="OGRN" value="<?=sprintf("%s", $arResult['PROPERTIES']['OGRN']['VALUE'])?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgInn">ИНН</label>
								<input type="text" class="form-control" id="orgInn" name="INN" value="<?=$arResult['PROPERTIES']['INN']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgKpp">КПП</label>
								<input type="text" class="form-control" id="orgKpp" name="KPP" value="<?=$arResult['PROPERTIES']['KPP']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgPfr">ПФР</label>
								<input type="text" class="form-control" id="orgPfr" name="PFR" value="<?=$arResult['PROPERTIES']['PFR']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgOkpo">ОКПО</label>
								<input type="text" class="form-control" id="orgOkpo" name="OKPO" value="<?=$arResult['PROPERTIES']['OKPO']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgOkogu">ОКОГУ</label>
								<input type="text" class="form-control" id="orgOkogu" name="OKOGU" value="<?=$arResult['PROPERTIES']['OKOGU']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgOkfs">ОКФС</label>
								<input type="text" class="form-control" id="orgOkfs" name="OKFS" value="<?=$arResult['PROPERTIES']['OKFS']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgOkopf">ОКОПФ</label>
								<input type="text" class="form-control" id="orgOkopf" name="OKOPF" value="<?=$arResult['PROPERTIES']['OKOPF']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group">
								<label for="orgBik">БИК</label>
								<input type="text" class="form-control" id="orgBik" name="BIK" value="<?=$arResult['PROPERTIES']['BIK']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-8">
							<div class="form-group">
								<label for="orgRsch">Расчётный счёт</label>
								<input type="text" class="form-control" id="orgRsch" name="RASCH" value="<?=$arResult['PROPERTIES']['RASCH']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="orgBank">Банк</label>
						<input type="text" class="form-control" id="orgBank" name="BANK" value="<?=$arResult['PROPERTIES']['BANK']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
					</div>
					<div class="row">
						<div class="col-xs-8">
							<div class="form-group">
								<label for="orgLs">Лицевой счёт</label>
								<input type="text" class="form-control" id="orgLs" name="LS" value="<?=$arResult['PROPERTIES']['LS']['VALUE']?>" <?if ($arResult['ACCESS_MODE'] != 1):?>disabled<?endif;?> onchange="javascript:save_enable();">
							</div>
						</div>
					</div>
				</div>
			<!-- //Реквизиты -->

			<!-- Админы школы -->
				<div role="tabpanel" class="tab-pane fade" id="admin">

					<?if ($arResult['CAN_ADD_ADMIN']):?>
						<div class="row">
							<div class="col-xs-12 text-right add-button">
								<a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#userNew"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> &nbsp;Добавить администратора</a>
							</div>
						</div>
					<?endif;?>

					<table class="table table-striped table-hover">
						<thead>
							<tr>
								<th width="200">Логин</th>
								<th>ФИО</th>
								<?if (!is_user_in_group(9)):?>
									<th width="100">Опции</th>
								<?endif;?>
							</tr>
						</thead>
						<tbody>
							<?foreach ($arResult['ADMINS'] as $arAdmin):?>
								<tr>
									<td><?=$arAdmin['LOGIN']?></td>
									<td><?=$arAdmin['NAME']?></td>
									<?if (!is_user_in_group(9)):?>
										<td>
											<div class="btn-group">
												<a href="#" class="btn btn-primary btn-sm" title="Редактировать ФИО" data-toggle="modal" data-target="#userEdit_<?=$arAdmin['ID']?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> </a>
												<a href="#" class="btn btn-primary btn-sm" title="Изменить пароль" data-toggle="modal" data-target="#userEditPass_<?=$arAdmin['ID']?>"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> </a>
<?if (0):?>
												<a href="#" class="btn btn-primary btn-sm" title="Удалить пользователя" data-toggle="modal" data-target="#userDel_<?=$arAdmin['ID']?>" <?if (count($arResult['ADMINS']) < 2):?>disabled<?endif;?>><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> </a>
<?endif;?>
											</div>

										</td>
									<?endif;?>
								</tr>
							<?endforeach;?>
						</tbody>
					</table>
				</div>
			<!-- //Админы школы -->

			<!-- Удаление школы -->
				<?if (is_user_in_group(6) || is_user_in_group(7)):?>
					<div role="tabpanel" class="tab-pane fade" id="remove">
						<div class="row">
							<div class="col-xs-12 remove-alert-panel">
								<div class="alert alert-danger text-center" role="alert">
									Внимание!<br>
									При удалении школы будет удалена вся информация о ней,<br>а также все ее заказы и отчеты!
								</div>
							</div>
						</div>

						<div class="row" id="remove_button_panel">
							<div class="col-xs-12 text-center">
								<button type="button" class="btn btn-danger" onClick="remove_test()">Удалить школу</button>
							</div>
						</div>

						<div id="remove_button_panel_2" hidden>
							<div class="row">
								<div class="col-xs-12 text-center remove-alert-panel">
									<div class="alert alert-danger text-center" role="alert">
										Вы уверены, что хотите полностью удалить из базы школу<br>"<?=$arResult['NAME']?>"?
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12 text-center">
									<a href="/schools/?mode=remove&sid=<?=$arResult['ID']?>" class="btn btn-danger">Подтверждаю удаление</a>
									<a href="<?=$_SERVER['SCRIPT_NAME']?>" class="btn btn-default">Отменить</a>
								</div>
							</div>

						</div>
					</div>
				<?endif;?>

		</div>
	<?if ($arResult['ACCESS_MODE'] == 1 || $arResult['ACCESS_MODE'] == 2 || $arResult['ACCESS_MODE'] == 3):?>
		</form>
	<?endif;?>

</div>


<?foreach ($arResult['ADMINS'] as $arAdmin):?>
	<div class="modal fade edit-panel" id="userDel_<?=$arAdmin['ID']?>" tabindex="-1" role="dialog" aria-labelledby="userDel_<?=$arAdmin['ID']?>Label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Удаление пользователя</h4>
				</div>
				<div class="modal-body">
					<form role="form" id="userDelForm_<?=$arAdmin['ID']?>" method="POST" action="/schools/<?=$arResult['ID']?>/">
						<input type="hidden" name="MODE" value="DELETE">
						<input type="hidden" name="USER_ID" value="<?=$arAdmin['ID']?>">
						<input type="hidden" name="SCHOOL_ID" value="<?=$arResult['ID']?>">
						<div class="form-group">
							<label for="fio_<?=$arAdmin['ID']?>">Подтвердите удаление пользователя<br><?=$arAdmin['NAME']?> (<?=$arAdmin['LOGIN']?>)</label>
						</div>
						<div class="form-group">
							<label for="Pass">Ваш пароль администратора</label>
							<input type="password" class="form-control" id="Pass" placeholder="Ваш пароль администратора" name="PASS">
						</div>
						<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
						<button type="submit" class="btn btn-primary">Удалить</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade edit-panel" id="userEdit_<?=$arAdmin['ID']?>" tabindex="-1" role="dialog" aria-labelledby="userEdit_<?=$arAdmin['ID']?>Label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Редактирование пользователя</h4>
				</div>
				<div class="modal-body">
					<form role="form" id="userEditForm_<?=$arAdmin['ID']?>" method="POST" action="/schools/<?=$arResult['ID']?>/">
						<input type="hidden" name="MODE" value="USER">
						<input type="hidden" name="USER_ID" value="<?=$arAdmin['ID']?>">
						<div class="form-group">
							<label for="fio_<?=$arAdmin['ID']?>">ФИО</label>
							<input type="text" class="form-control" id="fio_<?=$arAdmin['ID']?>" placeholder="Фамилия Имя Отчество" value="<?=$arAdmin['NAME']?>" name="NAME">
						</div>
<?if(0):?>
						<div class="form-group">
							<label for="login_<?=$arAdmin['ID']?>">Логин</label>
							<input type="text" class="form-control" id="login_<?=$arAdmin['ID']?>" placeholder="Логин" value="<?=$arAdmin['LOGIN']?>" name="LOGIN">
						</div>
<?endif;?>
						<div class="form-group">
							<label for="Pass">Ваш пароль администратора</label>
							<input type="password" class="form-control" id="Pass" placeholder="Ваш пароль администратора" name="PASS">
						</div>
						<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
						<button type="submit" class="btn btn-primary">Сохранить</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade edit-panel" id="userEditPass_<?=$arAdmin['ID']?>" tabindex="-1" role="dialog" aria-labelledby="userEdit_<?=$arAdmin['ID']?>Label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel"><?=$arAdmin['NAME']?> (<?=$arAdmin['LOGIN']?>)</h4>
				</div>
				<div class="modal-body">
					<form role="form" id="userEditFormPass_<?=$arAdmin['ID']?>" method="POST" action="/schools/<?=$arResult['ID']?>/">
						<input type="hidden" name="MODE" value="PASS">
						<input type="hidden" name="USER_ID" value="<?=$arAdmin['ID']?>">
						<div class="form-group">
							<label for="NewPass">Новый пароль</label>
							<input type="password" class="form-control" id="NewPass_<?=$arAdmin['ID']?>" placeholder="Новый пароль" name="NEWPASS">
						</div>
						<div class="form-group">
							<label for="ReNewPass">Новый пароль еще раз</label>
							<input type="password" class="form-control" id="ReNewPass" placeholder="Новый пароль еще раз" name="RENEWPASS">
						</div>
						<div class="form-group">
							<label for="Pass">Текущий пароль</label>
							<input type="password" class="form-control" id="Pass" placeholder="Текущий пароль" name="PASS">
						</div>
						<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
						<button type="submit" class="btn btn-primary">Сохранить</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#userDelForm_<?=$arAdmin['ID']?>").validate({
				rules: {
					PASS: "required"
				},
				messages: {
					PASS: '<div class="label label-danger" role="alert">Укажите Ваш текущий пароль!</div>'
				}
			});
			$("#userEditForm_<?=$arAdmin['ID']?>").validate({
				rules: {
					LOGIN: {
						required: true,
						minlength: 3
					},
					NAME: {
						required: true,
						minlength: 10
					},
					PASS: "required"
				},
				messages: {
					LOGIN: '<div class="label label-danger" role="alert">Логин должден быть не менее 3 символов!</div>',
					NAME:  '<div class="label label-danger" role="alert">Введите ФИО!</div>',
					PASS: '<div class="label label-danger" role="alert">Укажите Ваш текущий пароль!</div>'
				}
			});
			$("#userEditFormPass_<?=$arAdmin['ID']?>").validate({
				rules: {
					NEWPASS: {
						required: true,
						minlength: 6
					},
					RENEWPASS: {
						equalTo: '#NewPass_<?=$arAdmin['ID']?>'
					},
					PASS: "required"
				},
				messages: {
					NEWPASS: '<div class="label label-danger" role="alert">Пароль должен состоять не менее чем из 6 символов!</div>',
					RENEWPASS: '<div class="label label-danger" role="alert">Пароли должны совпадать!</div>',
					PASS: '<div class="label label-danger" role="alert">Укажите Ваш текущий пароль!</div>'
				}
			});
		});
	</script>
<?endforeach;?>


<div class="modal fade edit-panel" id="userNew" tabindex="-1" role="dialog" aria-labelledby="userNewLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Создание пользователя</h4>
			</div>
			<div class="modal-body">
				<form role="form" id="userNewForm" method="POST" action="/schools/<?=$arResult['ID']?>/">
					<input type="hidden" name="MODE" value="NEWUSER">
					<input type="hidden" name="SCHOOL_ID" value="<?=$arResult['ID']?>">
					<div class="form-group">
						<label for="fio">ФИО</label>
						<input type="text" class="form-control" id="fio" placeholder="Фамилия Имя Отчество" name="NAME">
					</div>
					<div class="form-group">
						<label for="login">Логин</label>
						<input type="text" class="form-control" id="login" placeholder="Логин" name="LOGIN">
					</div>
					<div class="form-group">
						<label for="email">E-Mail</label>
						<input type="email" class="form-control" id="email" placeholder="E-Mail" name="EMAIL">
					</div>
					<div class="form-group">
						<label for="NewPass">Новый пароль</label>
						<input type="password" class="form-control" id="NewPass" placeholder="Новый пароль" name="NEWPASS">
					</div>
					<div class="form-group">
						<label for="ReNewPass">Новый пароль еще раз</label>
						<input type="password" class="form-control" id="ReNewPass" placeholder="Новый пароль еще раз" name="RENEWPASS">
					</div>
					<div class="form-group">
						<label for="Pass">Текущий пароль</label>
						<input type="password" class="form-control" id="Pass" placeholder="Текущий пароль" name="PASS">
					</div>
					<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
					<button type="submit" class="btn btn-primary">Сохранить</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

	$(document).ready(function(){
		$("#userNewForm").validate({
			rules: {
				LOGIN: {
					required: true,
					minlength: 3
				},
				NAME: {
					required: true,
					minlength: 10
				},
				NEWPASS: {
					required: true,
					minlength: 6
				},
				RENEWPASS: {
					equalTo: '#NewPass'
				},
				PASS: "required"
			},
			messages: {
				LOGIN: '<div class="label label-danger" role="alert">Логин должден быть не менее 3 символов!</div>',
				NAME:  '<div class="label label-danger" role="alert">Введите ФИО!</div>',
				PASS: '<div class="label label-danger" role="alert">Укажите Ваш текущий пароль!</div>',
				NEWPASS: '<div class="label label-danger" role="alert">Пароль должен состоять не менее чем из 6 символов!</div>',
				RENEWPASS: '<div class="label label-danger" role="alert">Пароли должны совпадать!</div>',
				EMAIL: '<div class="label label-danger" role="alert">Введите корректный адрес электронной почты!</div>'
			}
		});
	});

	function save_enable() { $('#btnSave').removeAttr('disabled'); $('#btnCancel').removeAttr('disabled');}

</script>
