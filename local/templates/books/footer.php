<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
			</div>
			<?// ***** Основная колонка (END) ***** ?>
		</div>
	</div> <?// Главный контейнер ?>

	<footer class="footer">
		<div class="container">
			<div class="container-footer">
				<?$APPLICATION->IncludeFile(
					SITE_DIR."include/copyright.php",
					Array(),
					Array("MODE"=>"html")
				);?>
			</div>
		</div>
	</footer>

	<?// ******* Модальное окно предупреждения о незаполненном типе школы ******* ?>
		<?if (is_user_in_group(8) && !testSchoolStatus(get_schoolID($USER->GetID()))):?>
			<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="statusModalLabel">Предупреждение</h4>
						</div>
						<div class="modal-body fz-modal-body">
							Вам необходимо указать тип школы!<br><br>
							Без указания типа школы правильное формирование договора невозможно!
						</div>
						<div class="modal-footer">
							<a href="/schools/<?=get_schoolID($USER->GetID())?>/" class="btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span> Реквизиты школы</a>
							<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
						</div>
					</div>
				</div>
			</div>
			<script>
				$(document).ready(function(){
					$('#statusModal').modal();
				});
			</script>
		<?endif;?>
	<?// ******* Модальное окно предупреждения о незаполненном типе школы (END) ******* ?>

	<?// ******* Модальное окно предупреждения о незаполненном ФЗ ******* ?>
		<?if (is_user_in_group(8) && !testPunktFZ(get_schoolID($USER->GetID()))):?>
			<div class="modal fade" id="fzModal" tabindex="-1" role="dialog" aria-labelledby="fzModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="fzModalLabel">Предупреждение</h4>
						</div>
						<div class="modal-body fz-modal-body">
							Вам необходимо указать пункт ФЗ,<br>по которому производит закупку учебной литературы Ваша школа!<br><br>
							Без указания пункта ФЗ отправка заказов будет невозможна!
						</div>
						<div class="modal-footer">
							<a href="/schools/<?=get_schoolID($USER->GetID())?>/" class="btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span> Реквизиты школы</a>
							<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
						</div>
					</div>
				</div>
			</div>
			<script>
				$(document).ready(function(){
					$('#fzModal').modal();
				});
			</script>
		<?endif;?>
	<?// ******* Модальное окно предупреждения о незаполненном ФЗ (END) ******* ?>

	<?// ******* Модальное окно подтверждение операции ******* ?>
		<?if ($USER->IsAuthorized()):?>
			<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="confirmModalLabel">Подтверждение действия</h4>
						</div>
						<div class="modal-body confirm-modal-body"></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default">ОК</button>
							<button type="button" class="btn btn-default">Отменить</button>
						</div>
					</div>
				</div>
			</div>
		<?endif;?>
	<?// ******* Модальное окно подтверждение операции (END) ******* ?>

	<script type="text/javascript" src="/include/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/include/bootstrap_validator/js/bootstrapValidator.min.js"></script>
	<script type="text/javascript" src="/include/bav.js"></script>

	<script>
		$(document).ready(function(){
			setTimeout(function(){$(".my-show-message-slide-up").slideUp('slow');}, 5000);
		});
	</script>

	<?// ******* Скрипты для личного кабинета ******* ?>
	<?if (is_user_in_group(8) && $USER_SCHOOL_ID):?>
		<script type="text/javascript" language="javascript">
			$(document).ready(function(){
				// загрузка данных корзины
				$.ajax({
					url: "/include/ajax/get_cart_info.php",
					method: "POST",
					data: {"USER" : <?=$USER->GetID();?>, "MODE" : "CART"},
					cache: false,
					beforeSend: function(){
						$("#cart_sum").html('<img src="<?=SITE_TEMPLATE_PATH?>/images/wait.gif" border="0" width="19" height="19">');
						$("#cart_count").html('&nbsp;');
					},
					success: function(data){
						var result = jQuery.parseJSON(data);
						$("#cart_sum").html(result.sum);
						$("#cart_count").html(result.count + ' ед.');
					},
					error: function(){
						$("#cart_sum").html('Ошибка');
						$("#cart_count").html('&nbsp;');
					}
				});
				// загрузка данных отчета
				$.ajax({
					url: "/include/ajax/get_cart_info.php",
					method: "POST",
					data: {"USER" : <?=$USER->GetID();?>, "MODE" : "REPORT"},
					cache: false,
					beforeSend: function(){
						$("#report_sum").html('<img src="<?=SITE_TEMPLATE_PATH?>/images/wait.gif" border="0"  width="19" height="19">');
						$("#report_count").html('&nbsp;');
					},
					success: function(data){
						var result = jQuery.parseJSON(data);
						$("#report_sum").html(result.sum);
						$("#report_count").html(result.count + ' ед.');
					},
					error: function(){
						$("#report_sum").html('Ошибка');
						$("#report_count").html('&nbsp;');
					}
				});
			});

			function add_to_cart(book_id, mode) {
				// Проверяем указанное количество
				if (mode == 'CART')
					cnt = Number($("#book_counter_" + book_id).val());
				else
					cnt = Number($("#report_counter_" + book_id).val());
				if (isNaN(cnt) || cnt < 1) {
					alert('Неверно указано количество!');
				} else {
					// добавление книги в корзину и обновление данных корзины
					$.ajax({
						url: "/include/ajax/add_to_cart.php",
						method: "POST",
						data: {"USER" : <?=$USER->GetID();?>, "BOOK" : book_id, "COUNT" : cnt, "MODE" : mode},
						cache: false,
						beforeSend: function(){
							if (mode == 'CART')
								$("#cart_sum").html('<img src="<?=SITE_TEMPLATE_PATH?>/images/wait.gif" border="0"  width="19" height="19">');
							else
								$("#report_sum").html('<img src="<?=SITE_TEMPLATE_PATH?>/images/wait.gif" border="0"  width="19" height="19">');
						},
						success: function(data){
							var result = jQuery.parseJSON(data);
							console.log(data);
							//if (result.auth == 1) document.location.href = '/auth/';
							if (mode == 'CART') {
								$("#del_book_" + book_id).removeAttr("hidden");
								$("#add_book_" + book_id).attr("hidden","hidden");
								$("#count_book_" + book_id).html(result.count_book);
								$("#cart_sum").html(result.sum);
								$("#cart_count").html(result.count + ' ед.');
							} else {
								$("#del_report_" + book_id).removeAttr("hidden");
								$("#add_report_" + book_id).attr("hidden","hidden");
								$("#count_report_" + book_id).html(result.count_book);
								$("#report_sum").html(result.sum);
								$("#report_count").html(result.count + ' ед.');
							}
						},
						error: function(){
							if (mode == 'CART')
								$("#cart_sum").html('Ошибка!');
							else
								$("#report_sum").html('Ошибка!');
						}
					});
				}
			}

			function del_from_cart(book_id, mode) {
				// удаление книги и обновление данных корзины
				$.ajax({
					url: "/include/ajax/del_from_cart.php",
					method: "POST",
					data: {"USER" : <?=$USER->GetID();?>, "BOOK" : book_id, "MODE" : mode},
					cache: false,
					beforeSend: function(){
						if (mode == 'CART')
							$("#cart_sum").html('<img src="<?=SITE_TEMPLATE_PATH?>/images/wait.gif" border="0"  width="19" height="19">');
						else
							$("#report_sum").html('<img src="<?=SITE_TEMPLATE_PATH?>/images/wait.gif" border="0"  width="19" height="19">');
					},
					success: function(data){
						var result = jQuery.parseJSON(data);
						if (result.auth == 1) document.location.href = '/auth/';
						if (mode == 'CART') {
							$("#add_book_" + book_id).removeAttr("hidden");
							$("#del_book_" + book_id).attr("hidden","hidden");
							$("#book_counter_" + book_id).val(1);
							$("#cart_sum").html(result.sum);
							$("#cart_count").html(result.count + ' ед.');
						} else {
							$("#add_report_" + book_id).removeAttr("hidden");
							$("#del_report_" + book_id).attr("hidden","hidden");
							$("#report_counter_" + book_id).val(1);
							$("#report_sum").html(result.sum);
							$("#report_count").html(result.count + ' ед.');
						}
					},
					error: function(){
						if (mode == 'CART')
							$("#cart_sum").html('Ошибка!');
						else
							$("#report_sum").html('Ошибка!');
					}
				});
			}
		</script>
	<?endif;?>
	<?// ******* Скрипты для личного кабинета (END) ******* ?>

</body>
</html>