<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<div class="row"><div class="col-xs-12 section-title"><h1>Реформирование спецификаций</h1></div></div>

<?MyShowMessage($arResult['ERROR_MESSAGE']);?>

<div class="panel panel-default">
	<div class="panel-body">
		<form class="form" name="izdform">
			<div class="row">
				<div class="col-xs-12">

					<div class="row">
						<div class="col-xs-4 form-group">
							<label>Издательство</label>
							<select class="form-control report-control" name="IZD" id="izd_select">
								<?foreach ($arResult['IZD_LIST'] as $izdID => $izdName):?>
									<option value="<?=$izdID;?>"><?=$izdName;?></option>
								<?endforeach;?>
							</select>
						</div>
						<div class="col-xs-4 form-group">
							<label>Отчётный период</label>
							<input class="form-control" type="text" value="<?=$arResult['WORK_PERIOD']['NAME']?>" disabled>
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
							<button type="button" class="btn btn-primary report-control" style="width:150px;" onClick="make_respec()"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Выполнить</button>
							<button type="button" class="btn btn-default report-control" style="width:150px;" onClick="document.location.href='/'" title="Вернуться на главную страницу сайта"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Отменить</button>
						</div>
					</div>

				</div>
			</div>

		</form>
	</div>
</div>

<div id="respec_result"></div>

<div class="row"><div class="col-xs-12">
	<div id="respec_progress_main" class="panel panel-default" hidden>
		<div class="panel-heading" id="respec_progress_title" >Обработка...</div>
		<div class="panel-body">
			<div class="progress">
				<div id="respec_progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width:0%;">0%</div>
			</div>
		</div>
	</div>
</div></div>

<div id="respec_error_log"></div>