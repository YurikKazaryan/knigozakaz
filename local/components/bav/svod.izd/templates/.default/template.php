<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<div class="row"><div class="col-xs-12 section-title"><h1>Своды для издательств</h1></div></div>

<?MyShowMessage($arResult['ERROR_MESSAGE']);?>

<div class="panel panel-default">
	<div class="panel-body">
		<form class="form" name="izdform">
			<div class="row">
				<div class="col-xs-9">
					<div class="row">
						<div class="col-xs-12">

							<div class="row">
								<div class="col-xs-4 form-group">
									<label>Издательство</label>
									<select class="form-control report-control" name="IZD" id="izd_select">
										<option value="prosv">«Просвещение»</option>
										<option value="drofa">«Дрофа»</option>
										<option value="astrel">«Астрель»</option>
										<option value="ventana">«Вентана-Граф»</option>
										<option value="russlovo">«Русское слово»</option>
										<option value="binom">«Бином»</option>
<?if ($USER->GetID() == 1):?>
										<option value="prosv_step">Просвещение - Шаги</option>
<?endif;?>
									</select>
								</div>
								<div class="col-xs-4 form-group">
									<label>Отчётный период</label>
									<select class="form-control report-control" name="IZD_PERIOD" id="izd_period">
										<?foreach ($arResult['PERIOD'] as $key => $value):?>
											<option value="<?=$key?>" <?if ($key == $arResult['WORK_PERIOD']['ID']):?>selected<?endif;?>><?=$value['NAME']?></option>
										<?endforeach;?>
									</select>
								</div>
								<div class="col-xs-4 form-group">
									<label>Муниципалитет:</label>
									<select class="form-control report-control" name="IZD_MUN_LIST" id="izd_mun_list">
										<?foreach ($arResult['MUN_LIST'] as $munID => $munName):?>
											<option value="<?=$munID?>"><?=$munName?></option>
										<?endforeach;?>
									</select>
								</div>
							</div>

							<div class="row" style="padding-top:15px">
								<div class="col-xs-12">
									<button type="button" class="btn btn-primary report-control" style="width:150px;" onClick="get_izd_svod()"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Сформировать</button>
									<button type="button" class="btn btn-default report-control" style="width:150px;" onClick="document.location.href='/'" title="Вернуться на главную страницу сайта"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Отменить</button>
								</div>
							</div>
						</div>
					</div>

				</div>
				<div class="col-xs-3 form-group">
					<label>Дата начала отчета</label>
					<?$APPLICATION->IncludeComponent(
						'bitrix:main.calendar',
						'izd_svod_calendar',
						array(
							'SHOW_INPUT' => 'Y',
							'FORM_NAME' => 'izdform',
							'INPUT_NAME' => 'DATE',
							'INPUT_VALUE' => '',
							'SHOW_TIME' => 'N'
						),
						null,
						array('HIDE_ICONS' => 'Y')
					);?>
					<p class="help-block">(оставьте пустым, если нужно сформировать свод за весь отчетный период)</p>
				</div>
			</div>

		</form>
	</div>
</div>

<div id="izd_result"></div>

<div class="row"><div class="col-xs-12">
	<div id="izd_progress_main" class="progress" hidden>
		<div id="izd_progress" class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
			0%
		</div>
	</div>
</div></div>