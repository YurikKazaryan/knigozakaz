$(document).ready(function() {

	$('#add_prim_button').click(function(){
		$('.prim-group').clone().removeClass('prim-group').addClass('prim-group-added').appendTo('.prim-container');
	});

	$('#izd_select').change(function(){
		$.ajax({
			url: loadSubsectionUrl,
			method: "POST",
			data: { "IZD" : $('#izd_select').val() },
			cache: false,
			async: false,
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (result.error != 1) {
					if (result.empty != 1) {
						$('#izd_sub_div').html(result.body).slideDown('fast');
					} else {
						$('#izd_sub_div').slideUp('fast').html('');
					}
				} else {
					alert('ОШИБКА загрузки списка подразделов для издательства ' + $('#izd_select').val());
				}
			},
			error: function(){
				alert('ОШИБКА AJAX!');
			}
		});
	});

    $('#form_load_price').bootstrapValidator({
        message: 'Значение неверное',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            IZD: {
                validators: {
                    notEmpty: {
                        message: 'Нужно указать издательство!'
                    }
                }
            },
            IZD_SUBSECTION: {
                validators: {
                    notEmpty: {
                        message: 'Нужно указать издательство!'
                    }
                }
            },
            START_DATE: {
                validators: {
                    notEmpty: {
                        message: 'Укажите дату начала действия прайса!'
                    },
					date: {
                        format: 'DD.MM.YYYY',
						separator: '.',
						message: 'Формат даты: ДД.ММ.ГГГГ'
                    }
                }
            },
            PRICE_FILE: {
                validators: {
                    notEmpty: {
                        message: 'Нужно выбрать файл с прайсом!'
                    },
                    file: {
                        extension: 'csv',
                        message: 'Прайс должен быть в формате CSV!'
                    }
                }
            }
		}
    });

    $('#form_load_price_2').bootstrapValidator({
        message: 'Значение неверное',
        fields: {
            CODE_1C: {
                validators: {
                    notEmpty: {
                        message: 'Необходимо указать поле с уникальным идентификатором учебника!'
                    }
                }
            },
            FP_CODE: {
                validators: {
                    notEmpty: {
                        message: 'Необходимо указать поле с кодом ФП, по которому определяются значимые строки прайса!'
                    }
                }
            },
			PRICE: {
                validators: {
                    notEmpty: {
                        message: 'Ценя - обязательное для загрузки поле!'
                    }
                }
            },
            TITLE: {
                validators: {
                    notEmpty: {
                        message: 'Название учебника - обязательное для загрузки поле!'
                    }
                }
            }
		}
    });

});

function start_load_price(fileID, url) {

	var stepSize = 50;
	var exitFlag = 0;
	var fullExitFlag = 0;

	for (mode = 0; mode <= 3 && fullExitFlag == 0; mode++) {
		$('#step_' + mode + '_glyph').removeClass('glyphicon-minus').addClass('glyphicon-hourglass');

		exitFlag = 0;

		for (var i = 1; exitFlag == 0; i++) {
			$.ajax({
				url: url,
				method: "POST",
				data: {"FILE_ID" : fileID, "STEP" : i, "SIZE" : stepSize, "MODE" : mode},
				cache: false,
				async: false,
				success: function(data){
					var result = jQuery.parseJSON(data);
					exitFlag = result.exit;
					$("#step_" + mode + "_3").html('Обработано ' + (mode == 2 || mode == 0 ? 'строк' : 'записей') + ': ' +
													((i - 1) * stepSize + result.count) + (mode == 2 || mode == 0 ? '/' + result.allCount : ''));
					if (mode == 0 && result.step0error == 1) {
						$('#step_' + mode + '_glyph').removeClass('glyphicon-hourglass').addClass('glyphicon-remove');
						$("#step_" + mode + "_3").html('Ошибки в уникальном коде!<br>Повторяющиеся коды:<br>' + result.step0list);
						exitFlag = 1;
						fullExitFlag = 1;
					}
				},
				error: function(){
					$("#step_" + mode + "_3").html('Ошибка AJAX');
					exitFlag = 1;
				}
			});
		}

		$('#step_' + mode + '_glyph').removeClass('glyphicon-hourglass').addClass('glyphicon-ok');

	}
}