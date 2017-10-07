<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сводный отчет");

if (!CSite::InGroup(array(1,6,7,9))) LocalRedirect('/auth/');

global $USER;
// $arReport = get_svod();
$arMunList = get_mun_list($USER->GetID());
$arPeriod = getPeriodList();
$arWorkPeriod = getWorkPeriod();
$arIzd = get_izd_list();
?>

<div class="row">
	<div class="col-xs-12 text-center">
		<h2>Формирование отчетов</h2>
		<h4>(отчеты формируются в формате Excel)</h4>
	</div>
</div>

<div role="tabpanel">

	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#svod" aria-controls="home" role="tab" data-toggle="tab">Сводный</a></li>
		<li role="presentation"><a href="#analiz_umk" aria-controls="profile" role="tab" data-toggle="tab">Анализ УМК</a></li>
		<li role="presentation"><a href="#empty_list" aria-controls="profile" role="tab" data-toggle="tab">«Пустые» школы</a></li>
		<li role="presentation"><a href="#reestr" aria-controls="profile" role="tab" data-toggle="tab">Реестры</a></li>
	</ul>

	<div class="tab-content">

		<?// СВОДНЫЙ ОТЧЕТ ?>
		<div role="tabpanel" class="tab-pane active report-panel" id="svod">
			<form class="form">

				<div class="panel panel-default">
					<div class="panel-heading"><h3 class="panel-title">Настройка отчёта</h3></div>
					<div class="panel-body">

						<div class="row">
							<div class="col-xs-6" style="padding-left: 30px;">
								<div class="row">
									<div class="form-group">
										<label>Отчётный период</label>
										<select class="form-control report-control" name="PERIOD" id="period">
											<?foreach ($arPeriod as $key => $value):?>
												<option value="<?=$key?>" <?if ($key == $arWorkPeriod['ID']):?>selected<?endif;?>><?=$value['NAME']?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label>Вид отчёта</label>
										<select class="form-control report-control" name="LEVEL1" id="report_type">
											<option value="MUN" selected>По муниципалитетам</option>
											<option value="IZD">По издательствам</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label id="level1_name">Группировка по издательствам</label>
										<select class="form-control report-control" name="LEVEL2" id="level2">
											<option value="1" selected>Включить</option>
											<option value="0">Отключить</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label>Группировка по школам</label>
										<select class="form-control report-control" name="LEVEL3" id="level3">
											<option value="1" selected>Включить</option>
											<option value="0">Отключить</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label>Показывать книги</label>
										<select class="form-control report-control" name="LEVEL4" id="level4">
											<option value="1" selected>Включить</option>
											<option value="0">Отключить</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label>Настройка выборки</label>
										<select class="form-control report-control" name="MODE" id="mode">
											<option value="0" selected>Только заказы</option>
											<option value="1">Только отчёты</option>
											<option value="2">Заказы и отчёты вместе</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label>Добавить самостоятельные закупки</label>
										<select class="form-control report-control" name="SELF" id="self">
											<option value="0" selected>Нет</option>
											<option value="1">Да</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-6 text-center" style="padding-top: 100px;" hidden id="report_loading">
								<img border="0" src="<?=SITE_TEMPLATE_PATH;?>/images/wait.gif" width="64" height="64">
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12">
								<button type="button" class="btn btn-primary report-control" onClick="get_report()">Сформировать</button>
								<button type="button" class="btn btn-default report-control" onClick="document.location.href='/'">Отменить</button>
							</div>
						</div>

					</div>
			</div>
			</form>
		</div>

		<?// АНАЛИЗ УМК ?>
		<div role="tabpanel" class="tab-pane report-panel" id="analiz_umk">
			<form class="form">
				<div class="panel panel-default">
					<div class="panel-heading"><h3 class="panel-title">Анализ УМК</h3></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-6" style="padding-left: 30px;">
								<div class="row">
									<div class="form-group">
										<label>Отчётный период</label>
										<select class="form-control report-control" name="PERIOD_UMK" id="period_umk">
											<?foreach ($arPeriod as $key => $value):?>
												<option value="<?=$key?>" <?if ($key == $arWorkPeriod['ID']):?>selected<?endif;?>><?=$value['NAME']?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-6 text-center">
								<div hidden id="report_umk">
									<img border="0" src="<?=SITE_TEMPLATE_PATH;?>/images/wait.gif" width="32" height="32">
								</div>
							</div>
							<div class="col-xs-6 text-right">
								<button type="button" class="btn btn-primary report-control" onClick="get_report_umk()">Сформировать</button>
								<button type="button" class="btn btn-default report-control" onClick="document.location.href='/'">Отменить</button>
							</div>
						</div>

					</div>
				</div>
			</form>
		</div>

		<?// ПУСТЫЕ ШКОЛЫ ?>
		<div role="tabpanel" class="tab-pane report-panel" id="empty_list">
			<form class="form">
				<div class="panel panel-default">
					<div class="panel-heading"><h3 class="panel-title">"Пустые" школы</h3></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-6" style="padding-left: 30px;">
								<div class="row">
									<div class="form-group">
										<label>Отчётный период</label>
										<select class="form-control report-control" name="PERIOD_EMPTY" id="period_empty">
											<?foreach ($arPeriod as $key => $value):?>
												<option value="<?=$key?>" <?if ($key == $arWorkPeriod['ID']):?>selected<?endif;?>><?=$value['NAME']?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6 text-center">
								<div hidden id="empty_list_loading">
									<img border="0" src="/bitrix/templates/books/images/loading.png" width="32" height="32">
								</div>
							</div>
							<div class="col-xs-6 text-right">
								<button type="button" class="btn btn-primary report-control" onClick="get_empty_list()">Сформировать</button>
								<button type="button" class="btn btn-default report-control" onClick="document.location.href='/'">Отменить</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>

		<?// Реестры ?>
		<div role="tabpanel" class="tab-pane report-panel" id="reestr">
			<form class="form">
				<div class="panel panel-default">
					<div class="panel-heading"><h3 class="panel-title">Реестры</h3></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-6" style="padding-left: 30px;">
								<div class="row">
									<div class="form-group">
										<label>Вид реестра</label>
										<select class="form-control report-control" name="REESTR_TYPE" id="reestr_type">
											<option value="BY_MUN">По районам</option>
											<option value="BY_SCH">По школам</option>
											<option value="REESTR_3">Реестр 3</option>
										</select>
									</div>
								</div>
								<div class="row" hidden id="reestr_mode_div">
									<div class="form-group">
										<label>Включить в отчёт:</label>
										<select class="form-control report-control" name="REESTR_MODE" id="reestr_mode">
											<option value="1">Количество</option>
											<option value="2">Суммы</option>
											<option value="3">Количество и суммы</option>
										</select>
									</div>
								</div>
								<div class="row" hidden id="reestr_izd_div">
									<div class="form-group">
										<label>Издательство</label>
										<select class="form-control report-control" name="REESTR_IZD" id="reestr_izd">
											<?foreach ($arIzd as $key => $value):?>
												<option value="<?=$key?>"><?=$value?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<label>Отчётный период</label>
										<select class="form-control report-control" name="PERIOD_REESTR" id="period_reestr">
											<?foreach ($arPeriod as $key => $value):?>
												<option value="<?=$key?>" <?if ($key == $arWorkPeriod['ID']):?>selected<?endif;?>><?=$value['NAME']?></option>
											<?endforeach;?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6 text-center">
								<div hidden id="reestr_loading">
									<img border="0" src="/bitrix/templates/books/images/loading.png" width="32" height="32">
								</div>
							</div>
							<div class="col-xs-6 text-right">
								<button type="button" class="btn btn-primary report-control" onClick="get_reestr()">Сформировать</button>
								<button type="button" class="btn btn-default report-control" onClick="document.location.href='/'">Отменить</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>



	</div>

</div>

<script>
	$(document).ready(function(){

		$('#report_type').change(function(){
			if ($('#report_type').val() == 'MUN')
				$('#level1_name').html('Группировка по издательствам');
			else
				$('#level1_name').html('Группировка по муниципалитетам');
		});

		$('#reestr_type').change(function(){
			if ($('#reestr_type').val() != 'BY_SCH') {
				$('#reestr_izd_div').slideUp('fast');
			} else {
				$('#reestr_izd_div').slideDown('fast');
			}

			if ($('#reestr_type').val() != 'REESTR_3') {
				$('#reestr_mode_div').slideUp('fast');
			} else {
				$('#reestr_mode_div').slideDown('fast');
			}
		});
	});

	function get_izd_svod() {
		izd = $('#izd_select').val();
		switch (izd) {
			case 'prosv':
				url = "/include/PHPExcel_ajax/make_prosv.php";
				break;

			case 'drofa':
				url = "/include/PHPExcel_ajax/make_drofa.php";
				break;

			case 'astrel':
				url = "/include/PHPExcel_ajax/make_astrel.php";
				break;

			case 'ventana':
				url = "/include/PHPExcel_ajax/make_ventana1.php";
				break;

			case 'binom':
				url = "/include/PHPExcel_ajax/make_binom.php";
				break;
		}
		$.ajax({
			url: url,
			method: "POST",
			data: {"MUN_ID" : $("#izd_mun_list").val(), "PERIOD" : $('#izd_period').val(), "START_DATE" : $('.izd-svod-date').val()},
			cache: false,
			async: false,
			beforeSend: function(){
				$("#svod_izd_loading").removeAttr('hidden');
				$(".report-control").attr('disabled','disabled');
			},
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (!result.error) {
					window.open('/reports/download/?t=x&f=' + result.file);
				} else
					alert(result.err_message);
				$("#svod_izd_loading").attr('hidden', 'hidden');
				$(".report-control").removeAttr('disabled');
			},
			error: function(){
				$("#svod_izd_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
			}
		});
	}

	function get_report_umk() {
		$.ajax({
			url: "/include/PHPExcel_ajax/make_books.php",
			method: "POST",
			data: {"PERIOD" : $('#period_umk').val()},
			cache: false,
			async: false,
			beforeSend: function(){
				$("#report_umk").removeAttr('hidden');
				$(".report-control").attr('disabled','disabled');
			},
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (!result.error) window.open('/reports/download/?f=' + result.file);
				$("#report_umk").attr('hidden', 'hidden');
				$(".report-control").removeAttr('disabled');
			},
			error: function(){
				$("#report_umk").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
				$(".report-control").removeAttr('disabled');
			}
		});
	}

	function get_report() {
		$.ajax({
			url: "/include/PHPExcel_ajax/make_svod.php",
			method: "POST",
			data: { "LEVEL1" : $("#report_type").val(),
					"LEVEL2" : $("#level2").val(),
					"LEVEL3" : $("#level3").val(),
					"LEVEL4" : $("#level4").val(),
					"MODE" : $("#mode").val(),
					"PERIOD" : $('#period').val(),
					"SELF" : $('#self').val()
				},
			cache: false,
			async: false,
			beforeSend: function(){
				$("#report_loading").removeAttr('hidden');
				$(".report-control").attr('disabled','disabled');
			},
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (!result.error) window.open('/reports/download/?f=' + result.file);
				$("#report_loading").attr('hidden', 'hidden');
				$(".report-control").removeAttr('disabled');
			},
			error: function(){
				$("#report_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
			}
		});
	}

	function get_empty_list() { // Список школ без заказов и отчетов
		$.ajax({
			url: "/include/PHPExcel_ajax/make_empty_list.php",
			method: "POST",
			data: {"PERIOD" : $('#period_empty').val()},
			cache: false,
			async: false,
			beforeSend: function(){
				$("#empty_list_loading").removeAttr('hidden');
				$(".report-control").attr('disabled','disabled');
			},
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (!result.error) window.open('/reports/download/?f=' + result.file);
				$("#empty_list_loading").attr('hidden', 'hidden');
				$(".report-control").removeAttr('disabled');
			},
			error: function(){
				$("#empty_list_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
			}
		});
	}

	function get_reestr() { // Реестры

		var type = $('#reestr_type').val();
		switch (type) {
			case 'BY_MUN':
				url = "/include/PHPExcel_ajax/make_reestr_by_mun.php";
				break;

			case 'BY_SCH':
				url = "/include/PHPExcel_ajax/make_reestr_by_school.php";
				break;

			case 'REESTR_3':
				url = "/include/PHPExcel_ajax/make_reestr_3.php";
				break;
		}

		$.ajax({
			url: url,
			method: "POST",
			data: {"PERIOD" : $('#period_reestr').val(), "IZD" : $('#reestr_izd').val(), "MODE" : $('#reestr_mode').val()},
			cache: false,
			async: false,
			beforeSend: function(){
				$("#reestr_loading").removeAttr('hidden');
				$(".report-control").attr('disabled','disabled');
			},
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (!result.error) window.open('/reports/download/?t=x&f=' + result.file);
				$("#reestr_loading").attr('hidden', 'hidden');
				$(".report-control").removeAttr('disabled');
			},
			error: function(){
				$("#reestr_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
			}
		});
	}

</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>