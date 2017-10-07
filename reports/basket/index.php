<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?$APPLICATION->SetTitle("Корзина");?>
<?
	if (!is_user_in_group(8) && !is_user_in_group(7) && !is_user_in_group(6) && !is_user_in_group(9)) header('Location: /');	// Вход в корзину возможен только администраторам школ и муниципалитетов (без возможности изменения)

//	echo '<pre>' .print_r($_POST, 1) . '</pre>';

	$arPeriod = getWorkPeriod();

	// Обрабатываем сохранение изменений
	if ($_POST['btnAction'] == 'SAVE') {

		// Если есть список на удаление - обрабатываем
		foreach($_POST['REMOVE'] as $value) {
			if (CIBlockElement::Delete($value)) // Если удалено успешно - удаляем этот элемент из списка на изменение
				if (isset($_POST['COUNT'][$value])) unset($_POST['COUNT'][$value]);
		}

		// Обрабатываем список на измемение, если еще остались элементы

		if (count($_POST['COUNT']) > 0) {

			$arFilter = array();
			foreach ($_POST['COUNT'] as $key => $value) if (intval($value) > 0) $arFilter[] = $key;

			$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'ID' => $arFilter), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE'));
			while ($arFields = $res->GetNext()) {
				if (($arFields['PROPERTY_COUNT_VALUE'] != $_POST['COUNT'][$arFields['ID']]) || ($arFields['PROPERTY_PRICE_VALUE'] != $_POST['PRICE'][$arFields['ID']]))	// Если количество или цена были изменены, меняем в базе
					CIBlockElement::SetPropertyValuesEx($arFields['ID'], 9, array('COUNT' => $_POST['COUNT'][$arFields['ID']], 'PRICE' => $_POST['PRICE'][$arFields['ID']]));
			}
		}

		$result_state = 'success';
		$result_info = 'Все изменения были сохранены!';
	}

	// Обрабатываем формирование отчета
	if ($_POST['btnAction'] == 'REPORT') {

		$school_id = get_schoolID($USER->getID());
		$school_name = get_schoolName($USER->getID());

		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 9, 'PROPERTY_SCHOOL_ID' => $school_id, 'PROPERTY_STATUS' => 'osreport'),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS', 'PROPERTY_SCHOOL_ID', 'PROPERTY_IZD_ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE')
		);

		$arBooks = array();
		$arOrders = array();

		while ($arFields = $res->GetNext()) {
			$arBooks[] = array('ID' => $arFields['ID'], 'IZD_ID' => $arFields['PROPERTY_IZD_ID_VALUE']);	// Запоминаем ID записи
			if (!is_array($arOrders[$arFields['PROPERTY_IZD_ID_VALUE']])) $arOrders[$arFields['PROPERTY_IZD_ID_VALUE']] = array('SUM' => 0, 'ORDER_NUM' => 0);
			$arOrders[$arFields['PROPERTY_IZD_ID_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
		}

		// Создаем новые отчеты
		foreach ($arOrders as $izd_id => $arOrder) {
			$el = new CIBlockElement;
			$new_id = $el->Add(array(
				'MODIFIED_BY' => $USER->GetID(),
				'IBLOCK_SECTION_ID' => false,
				'IBLOCK_ID' => 11,
				'NAME' => $school_name . ' - ' . get_izd_name($izd_id),
				'ACTIVE' => 'Y',
				'DATE_ACTIVE_FROM' => ConvertTimeStamp(false, 'FULL'),
				'PROPERTY_VALUES' => array(
					'SCHOOL_ID' => $school_id,
					'STATUS' => 'osrepready',
					'IZD_ID' => $izd_id,
					'SUM' => $arOrder['SUM'],
					'PERIOD' => $arPeriod['ID'],
					'REGION_ID' => getUserRegion()
				)
			));
			if ($new_id) {
				$arOrders[$izd_id]['ORDER_NUM'] = $new_id;
			} else {
				die('Ошибка обработки заказа: обратитесь к администратору системы!');
			}
		}

		// Меняем статус в корзине и добавляем номер заказа
		foreach ($arBooks as $arBook) {
			CIBlockElement::SetPropertyValuesEx($arBook['ID'], 9, array('STATUS' => 'osrepready', 'ORDER_NUM' => $arOrders[$arBook['IZD_ID']]['ORDER_NUM']));
		}

		$result_state = 'success';
		$result_info = 'Ваш отчёт сохраненн в базе данных! Список отчетов Вы можете посмотреть в разделе <a href="/reports/">"Отчёты"</a>.';

	}

	// Устанавливаем фильтр по ID школы пользователя и по состоянию "В корзине", или по параметру GET sch_id, если пользователь - админ
	$arFilter = array('PROPERTY_SCHOOL_ID' => (is_user_in_group(8) ? get_schoolID($USER->GetID()) : $_GET['sch_id']), 'PROPERTY_STATUS' => 'osreport');

//	echo '<pre>' .print_r($arFilter, 1) . '</pre>';

?>

<?if ($result_state):?>
	<div class="alert alert-<?=$result_state?> text-center" id="alert-box"><?=$result_info?></div>
	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout(function() { $('#alert-box').slideUp('slow') }, 3000);
		});
	</script>
<?endif;?>

<?$APPLICATION->IncludeComponent(
	"bitrix:news.list",
	"report_basket",
	array(
		"IBLOCK_TYPE" => "school_books",
		"IBLOCK_ID" => "9",
		"NEWS_COUNT" => "9999",
		"SORT_BY1" => "ID",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "ID",
		"SORT_ORDER2" => "ASC",
		"FILTER_NAME" => "arFilter",
		"FIELD_CODE" => array(
			0 => "NAME",
			1 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "STATUS",
			1 => "BOOK_ID",
			2 => "COUNT",
			3 => "FP_CODE",
			4 => "AUTHOR",
			5 => "CLASS",
			6 => "YEAR",
			7 => "PRICE",
			8 => "IZD_ID",
			9 => "",
		),
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"PREVIEW_TRUNCATE_LEN" => "",
		"ACTIVE_DATE_FORMAT" => "",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "Y",
		"SET_META_DESCRIPTION" => "Y",
		"SET_STATUS_404" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"INCLUDE_SUBSECTIONS" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "РќРѕРІРѕСЃС‚Рё",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>