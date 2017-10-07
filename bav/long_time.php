<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Длинная операция');

	if (!$USER->IsAdmin()) LocalRedirect('/');
?>

<div class="row">
	<div class="col-xs-4 col-xs-offset-2">
		<div class="row">
			<div class="col-xs-3 text-right">
				<button type="button" class="btn btn-danger" id="start_button">Старт</button>
			</div>
			<div class="col-xs-2">
				<div id="loading_icon" hidden><img src="/bitrix/templates/books/images/loading.png" width="32" height="32"></div>
			</div>
			<div class="col-xs-3 text-right">
				<button type="button" class="btn btn-default" id="clear_button">Очистить вывод</button>
			</div>
		</div>
	</div>
</div>

<br>

<div class="panel panel-default">
	<div class="panel-heading">Результат выполнения</div>
	<div class="panel-body" id="panel_body"></div>
</div>

<br>

<script>

	$(document).ready(function(){

		$('#clear_button').click(function(){
			$('#panel_body').html('');
 		});

		$('#start_button').click(function(){

			var exit_flag = 0;

			$('#start_button').attr('disabled','disabled');
			$('#loading_icon').removeAttr('hidden');

			for (var i = 100; ((i <= 8500) && (exit_flag == 0)); i += 100) {
				$.ajax({
					url: "/bav/update_records.php",
					method: "POST",
					cache: false,
					async: false,
					beforeSend: function(){
						$('#panel_body').html('Обрабатываются записи: ' + i + '<br>' + $('#panel_body').html());
					},
					success: function(data){
						var result = jQuery.parseJSON(data);
						if (result.mode == 'ERROR') exit_flag = 1;
						 $('#panel_body').html(result.text + $('#panel_body').html());
					},
					error: function(){
						$("#panel_body").html($("#panel_body").html() + 'Ошибка AJAX (' + i + ')<br>');
						exit_flag = 0;
					}
				});
			}

			$('#start_button').removeAttr('disabled');
			$('#loading_icon').attr('hidden','hidden');

		});
	});

</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

