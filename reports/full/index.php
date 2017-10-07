<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Общий отчёт по АИС");
if (!CSite::InGroup(array(1,6,7,9))) LocalRedirect('/auth/');
?>

<h1>Общий отчёт</h1>

<div class="panel panel-default"><div class="panel-body">
	<div id="full_progress_main" class="progress">
		<div id="full_progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width:0%;">0%</div>
	</div>
</div></div>

<script>
	$(document).ready(function(){
		$.ajax({
			url: '/include/ajax/make_full_report.php',
			method: "POST",
			data: {'MODE' : 1},
			cache: false,
			async: false,
			success: function(data){
				var result = jQuery.parseJSON(data);
				if (!result.error) {

					var file;

					for (i = 1; i <= result.page_count; i++) {

						$.ajax({
							url: '/include/ajax/make_full_report.php',
							method: "POST",
							data: {'MODE' : 2, 'PAGE_NUM' : i},
							cache: false,
							async: false,
							success: function(data){
								var result2 = jQuery.parseJSON(data);
								if (!result2.error) {
									setProgressPosition(result.page_count, i);
									file = result2.file;
								} else
									alert('UPSsss... ' + result2.error);
							},
							error: function(){
								alert('ERRRORRR');
							}
						});

					}

					window.open('/upload/tmp/'+file);
					document.location.href = '/';

				} else
					alert('UPSsss...');
			},
			error: function(){
				alert('ERRRORRR');
			}
		});
	});

function setProgressPosition(max, cur) {
	var x = Math.round(cur / max * 100);
	$('#full_progress').css('width', x+'%').html(x+'%');
}

</script>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>