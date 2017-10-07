function make_respec() {
	$(".report-control").attr('disabled','disabled');
	$('#respec_result').html('Расчёт количества заказов для обработки...');
	$('#respec_error_log').html('');

	// Узнаём количество заказов для обработки
	var allCount = 0;
	$.ajax({
		url: "/include/ajax/respec_get_count.php",
		method: "POST",
		data: {"MUN_ID" : $("#izd_mun_list").val(), "IZD_ID" : $('#izd_select').val()},
		cache: false,
		async: false,
		success: function(data){
			var result = jQuery.parseJSON(data);
			allCount = result.count;
			orders = result.orders;
		},
		error: function(){
			alert('COUNT ERROR');
		}
	});

	if (allCount) {
		$('#respec_result').html('Найдено заказов для обработки: '+allCount);
		setProgressPosition(allCount, 0);
		$('#respec_progress_main').removeAttr('hidden');
		for (var i=0; i<allCount; i++) {
			setProgressPosition(allCount, i+1);
			$('#respec_progress_title').html('Обработано ' + (i+1) + ' из ' + allCount + '...');
			res = makeSpec(orders[i], false);
			for (var s in res)
				$('#respec_error_log').html($('#respec_error_log').html() + '<br>' + s);
		}
		$('#respec_progress_main').attr('hidden','hidden');
		$('#respec_result').html('Обработка завершена.');
	} else {
		$('#respec_result').html('Нет заказов для обработки...');
	}


	$(".report-control").removeAttr('disabled');
}

function setProgressPosition(max, cur) {
	var x = Math.round(cur / max * 100);
	$('#respec_progress').css('width', x+'%').html(x+'%');
}