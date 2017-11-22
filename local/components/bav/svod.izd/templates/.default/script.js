function get_izd_svod() {
	$(".report-control").attr('disabled','disabled');
	izd = $('#izd_select').val();
	switch (izd) {
		case 'binom':
			url = "/include/PHPExcel_ajax/make_binom.php";
			break;

		case 'prosv':
			url = "/include/svod/prosv.php";
			break;

        case 'prosv_step':
			url = "/include/PHPExcel_ajax/make_prosv_step.php";
			break;

		case 'drofa':
			url = "/include/svod/drofa.php";
			break;

		case 'astrel':
			url = "/include/PHPExcel_ajax/make_drofa.php";
			break;

		case 'ventana':
			url = "/include/svod/ventana.php";
			break;

		case 'russlovo':
			url = "/include/PHPExcel_ajax/make_drofa.php";
			break;

		default:
            url = "/include/svod/prosv_efu.php";
            break;
	}
	/*
	if (izd == 'prosv_step') {
		// Считаем отчет
		$('#izd_result').html('Формируем массив отчётных данных <span id="izd_result_s1" class="glyphicon glyphicon-hourglass" aria-hidden="true"></span>');
		$.ajax({
			url: url,
			method: "POST",
			data: {"MUN_ID" : $("#izd_mun_list").val(), "PERIOD" : $('#izd_period').val(), "START_DATE" : $('.izd-svod-date').val(), "STEP" : 1},
			cache: false,
			async: false,
			success: function(data){
				$('#izd_result_s1').removeClass('glyphicon-hourglass').addClass('glyphicon-ok');
				$('#izd_result').html($('#izd_result').html() + '<br>Формируем таблицу для отчёта <span id="izd_result_s2" class="glyphicon glyphicon-hourglass" aria-hidden="true"></span>');

				var result = jQuery.parseJSON(data);

				for (i=0; i<result.SEC_COUNT; i++) {

					// Создаём таблицу
					$.ajax({
						url: url,
						method: "POST",
						data: {"MUN_ID" : $("#izd_mun_list").val(), "PERIOD" : $('#izd_period').val(), "START_DATE" : $('.izd-svod-date').val(), "STEP" : 2, 'SEC_ID' : i},
						cache: false,
						async: false,
						success: function(data){
							$('#izd_result_s2').removeClass('glyphicon-hourglass').addClass('glyphicon-ok');
							$('#izd_result').html($('#izd_result').html() + '<br>Заполняем отчётную таблицу ');

						},
						error: function(){
							alert('STEP 2 ERROR');
						}
					});

				}




			},
			error: function(){
				alert('STEP 1 ERROR');
			}
		});

		// Заполнение таблицы
	} else {
		$.ajax({
			url: url,
			method: "POST",
			data: {"MUN_ID" : $("#izd_mun_list").val(), "PERIOD" : $('#izd_period').val(), "START_DATE" : $('.izd-svod-date').val(), "IZD" : izd},
			cache: false,
			async: false,
			success: function(data){
				//console.log(data);
				var result = jQuery.parseJSON(data);
				if (!result.error) {
					window.open('/reports/download/?t=x&f=' + result.file);
				} else
					alert(result.err_message);
			},
			error: function(){
				alert('ERRRORRR');
			}
		});
	}*/
    $.ajax({
        url: url,
        method: "POST",
        data: {"MUN_ID" : $("#izd_mun_list").val(), "PERIOD" : $('#izd_period').val(), "START_DATE" : $('.izd-svod-date').val(), "IZD" : izd},
        cache: false,
        async: false,
        success: function(data){
            //console.log(data);
            var result = jQuery.parseJSON(data);
            if (!result.error) {
                window.open('/reports/download/?t=x&f=' + result.file);
            } else
                alert(result.err_message);
        },
        error: function(){
            alert('ERRRORRR');
        }
    });
	$(".report-control").removeAttr('disabled');
}
