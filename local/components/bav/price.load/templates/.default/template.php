<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<div class="row"><div class="col-xs-12 section-title"><h1>Загрузка прайс-листа</h1></div></div><br>

<?MyShowMessage($arResult['ERROR_MESSAGE']);?>

<div class="load-price">

	<?if ($arResult['PAGE'] == 1):?>	<?// Первая страница - загрузка файла и указание параметров ?>

		<div class="row"><div class="col-xs-12 section-title"><h2>Шаг 1: Выбор файла и настройка параметров</h2></div></div><br>

		<form class="form" method="POST" enctype="multipart/form-data" action="" name="price_load_form" id="form_load_price">
			<div class="form-group">
				<label>Файл прайса</label>
				<input type="file" name="PRICE_FILE">
			</div>
			<div class="row">
				<div class="col-xs-9">
					<div class="row">
						<div class="col-xs-6">
							<div class="form-group">
								<label>Регион</label>
								<select class="form-control" name="REGION">
									<?foreach ($arResult['REG_LIST'] as $regID => $arReg):?>
										<option value="<?=$regID?>" <?if ($regID == $REGION_ID):?>selected<?endif;?>><?=$arReg['NAME']?></option>
									<?endforeach;?>
								</select>
							</div>
						</div>
						<div class="col-xs-6">
							<div class="form-group izd-group">
								<label>Издательство</label>
								<select class="form-control" name="IZD" id="izd_select">
									<option value="">- Не выбрано -</option>
									<?foreach ($arResult['IZD_LIST'] as $izdID => $izdName):?>
										<option value="<?=$izdID?>"><?=$izdName?></option>
									<?endforeach;?>
								</select>
							</div>
							<div class="form-group izd-group" hidden id="izd_sub_div">
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-3">
					<div class="form-group start-date-group">
						<label>Дата начала действия</label>
						<?$APPLICATION->IncludeComponent(
							'bitrix:main.calendar',
							'izd_svod_calendar',
							array(
								'SHOW_INPUT' => 'Y',
								'FORM_NAME' => 'price_load_form',
								'INPUT_NAME' => 'START_DATE',
								'INPUT_VALUE' => date('d.m.Y'),
								'SHOW_TIME' => 'N'
							),
							null,
							array('HIDE_ICONS' => 'Y')
						);?>
					</div>
				</div>
			</div>

			<div class="row"><div class="col-xs-12">
				<button type="submit" class="btn btn-primary" name="BTN" value="LOAD">Загрузить</button>
				<button type="button" class="btn btn-default" onClick="document.location.href='/'">Отменить</button>
			</div></div>
		</form>

		<script>loadSubsectionUrl = '<?=$this->GetFolder() . "/ajax_load_subsection.php"?>';</script>

	<?elseif ($arResult['PAGE'] == 2):?>	<?// Вторая страница - установка соответствия полей ?>

		<div class="row"><div class="col-xs-12 section-title"><h2>Шаг 2: Установка соответствий полей</h2></div></div><br>

		<div class="panel panel-default">
			<div class="panel-body">
				Выберите соответствие полей загружаемого прайс-листа полям каталога на сайте.<br>
				Для поля «Примечание» можно выбрать несколько полей - в этом случае их значения будут записаны в поле примечания каталога.
			</div>
		</div>

		<form class="form" method="POST" action="" name="price_load_form_2" id="form_load_price_2">

			<?foreach ($arResult['PROP_LIST'] as $code => $name):?>

				<?if ($code == 'FP_CODE' || $code == 'PRIM' || $code == 'NDS'):?>
					<div class="panel panel-default">
						<div class="panel-body">
				<?endif;?>

				<div class="form-group">
					<label><?=$name?></label>

					<?if ($code == 'PRIM'):?><div class="prim-container"><div class="prim-group"><?endif;?>

					<select class="form-control" name="<?=$code?><?if ($code == 'PRIM'):?>[]<?endif;?>">
						<option value="">- Не выбрано -</option>
						<?foreach ($arResult['VALUE_LIST'] as $valueKey => $valueText):?>
							<option value="<?=$valueKey?>"><?=$valueKey?>: <?=$valueText?></option>
						<?endforeach;?>
					</select>

					<?if ($code == 'PRIM'):?></div></div><?endif;?>

				</div>

				<?if ($code == 'NDS'):?>
					<div class="form-group">
						<label>Варианты признаков НДС 18%</label>
						<input class="form-control" type="text" name="NDS_MASK" placeholder="Укажите варианты через «;» Регистр не имеет значения. Например +CD;+ CD;с диском">
					</div>
				<?endif;?>

				<?if ($code == 'PRIM'):?>
					<div class="row prim-button"><div class="col-xs-12 text-right">
						<button type="button" class="btn btn-primary btn-sm" id="add_prim_button">Добавить поле в примечание</button>
					</div></div>
				<?endif;?>

				<?if ($code == 'FP_CODE'):?>
					<div class="checkbox">
						<label>
							<input type="checkbox" name="FP_NO_LOAD"> Не добавлять код ФП в каталог
						</label>
					</div>
				<?endif;?>

				<?if ($code == 'FP_CODE' || $code == 'PRIM' || $code == 'NDS'):?>
						</div>
					</div>
				<?endif;?>

			<?endforeach;?>

			<div class="row"><div class="col-xs-12">
				<input type="hidden" name="FILE_ID" value="<?=$arResult['FILE_ID']?>">
				<button type="submit" class="btn btn-primary" name="BTN" value="LOAD2">Продолжить</button>
				<button type="button" class="btn btn-default" onClick="document.location.href='/'">Отменить</button>
			</div></div>
			<br>
		</form>

	<?elseif ($arResult['PAGE'] == 3):?>	<?// Обработка файла импорта?>

		<div class="row"><div class="col-xs-12 section-title"><h2>Шаг 3: Импорт каталога</h2></div></div><br>

		<h3>Этапы обработки:</h3>

		<table class="table">
			<tr>
				<td class="col-1" id="step_0_1"><span class="glyphicon glyphicon-minus" aria-hidden="true" id="step_0_glyph"></span></td>
				<td class="col-2" id="step_0_2">Проверка выбранного уникального поля на корректность</td>
				<td class="col-3" id="step_0_3"></td>
			</tr>
			<tr>
				<td class="col-1" id="step_1_1"><span class="glyphicon glyphicon-minus" aria-hidden="true" id="step_1_glyph"></span></td>
				<td class="col-2" id="step_1_2">Подготовка текущего каталога к импорту</td>
				<td class="col-3" id="step_1_3"></td>
			</tr>
			<tr>
				<td class="col-1" id="step_2_1"><span class="glyphicon glyphicon-minus" aria-hidden="true" id="step_2_glyph"></span></td>
				<td class="col-2" id="step_2_2">Импорт каталога</td>
				<td class="col-3" id="step_2_3"></td>
			</tr>
			<tr>
				<td class="col-1" id="step_3_1"><span class="glyphicon glyphicon-minus" aria-hidden="true" id="step_3_glyph"></span></td>
				<td class="col-2" id="step_3_2">Обработка учебников, отсутствующих в импорте</td>
				<td class="col-3" id="step_3_3"></td>
			</tr>
		</table>

		<script>
			$(document).ready(function(){
				start_load_price(<?=$arResult['FILE_ID']?>, '<?=$this->GetFolder()."/ajax_price_load.php"?>');
			});
		</script>
	<?endif;?>
</div>