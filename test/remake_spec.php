<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
global $USER;

// Пересоздание спецификаций, которые заданы параметрами

if (1 && $USER->IsAdmin() && CModule::IncludeModule('iblock')) {

//***** ПАРАМЕТРЫ ВЫБОРКИ СПЕЦИФИКАЦИЙ ******
$izdID = 180;		// Издательство
$regionID = 56;		// Регион
$munID = 56;		// Муниципалитет
$periodID = 63890;	// Рабочий период
//*******************************************

// Режим выборки: 1 - по издательству, 2 - по муниципалитету, 3 - по списку
$modeSelect = 3;

//*******************************************

switch ($modeSelect) {
	case 1:
			$res = CIBlockElement::GetList(
				false,
				array(
					'IBLOCK_ID' => 11,
					'PROPERTY_IZD_ID' => $izdID,
					'PROPERTY_REGION_ID' => $regionID,
					'PROPERTY_PERIOD' => $periodID,
					'!PROPERTY_STATUS' => array('osrepready','osreport')
				),
				false, false,
				array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_PERIOD', 'PROPERTY_REGION_ID')
			);
			$arOrders = array();
			while ($arFields = $res->GetNext()) $arOrders[] = $arFields['ID'];
			break;

	case 2:
			// Выбираем школы муниципалитета
			$arSchoolID = get_schoolID_by_mun($munID);
			// Выбираем заказы этих школ
			$res = CIBlockElement::GetList(
				false,
				array(
					'IBLOCK_ID' => 11,
					'PROPERTY_SCHOOL_ID' => $arSchoolID,
					'PROPERTY_PERIOD' => $periodID,
					'!PROPERTY_STATUS' => array('osrepready','osreport')
				),
				false, false,
				array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_PERIOD')
			);
			$arOrders = array();
			while ($arFields = $res->GetNext()) $arOrders[] = $arFields['ID'];
			break;

	case 3:
			$arOrders = array(
				153317
			);
			break;
}



?>

<script>
	var arID = [<?foreach ($arOrders as $key => $orderID):?><?=$orderID?><?if($key<count($arOrders)-1):?>,<?endif;?><?endforeach;?>];
	$(document).ready(function(){
		for (i=0; i<<?=count($arOrders)?>; i++) {
			$('#result').html('Обрабатывается: ' + (i+1) + ' (номер заказа ' + arID[i] + ')');

			$.ajax({
				url: "/include/PHPExcel_ajax/make_spec.php",
				method: "POST",
				data: {"ORDER_ID" : arID[i]},
				cache: false,
				async: false,
				error: function(){
					$("#error").html('ERROR');
				}
			});


		}

		$('#result').html('Обработка завершена!');
	});
</script>

<div class="row"><div class="col-xs-12">
	Всего заказов в обработке: <?=count($arOrders)?>
</div></div>
<div class="row"><div class="col-xs-12" id="result"></div></div>
<div class="row"><div class="col-xs-12" id="error"></div></div>

<?
}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>