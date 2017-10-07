<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
	$APPLICATION->SetTitle("Корзина");

	if (!CSite::InGroup(array(1,6,7,8,9))) LocalRedirect('/auth/');	// Вход в корзину возможен только администраторам школ и муниципалитетов (без возможности изменения)

	$arPeriod = getWorkPeriod();

	$make_spec = false;

//	echo '<pre>' .print_r($_POST, 1) . '</pre>';

	// Обрабатываем сохранение изменений
	if ($_POST['btnAction'] == 'SAVE') {

		// Если есть список на удаление - обрабатываем
		foreach($_POST['REMOVE'] as $value) {
			if (CIBlockElement::Delete($value)) // Если удалено успешно - удаляем этот элемент из списка на изменение
				if (isset($_POST['COUNT'][$value])) unset($_POST['COUNT'][$value]);
		}

		// Обрабатываем список на изменение, если еще остались элементы

		if (count($_POST['COUNT']) > 0) {
			$arFilter = array();
			foreach ($_POST['COUNT'] as $key => $value)
				if (intval($value) > 0) $arFilter[] = $key;

			$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS, 'ID' => $arFilter), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_COUNT'));
			while ($arFields = $res->GetNext()) {
				if ($arFields['PROPERTY_COUNT_VALUE'] != $_POST['COUNT'][$arFields['ID']])	// Если количество было изменено, меняем в базе
					CIBlockElement::SetPropertyValuesEx($arFields['ID'], IB_ORDERS, array('COUNT' => $_POST['COUNT'][$arFields['ID']]));
			}
		}

		$result_state = 'success';
		$result_info = 'Все изменения были сохранены!';
	}

	// Обрабатываем формирование заказа
	if ($_POST['btnAction'] == 'ORDER') {

		$school_id = getSchoolID($USER->getID());
		$school_name = getSchoolName($USER->getID());

		$arPeriod = getWorkPeriod();

		// Вычисляем возможность создания заказов по издательствам
		$arIzd = getIzdList();
		$arIzdId = array();
		foreach ($arIzd as $key => $value)
			if (canMakeOrder($school_id, $key)) $arIzdId[] = $key;

		$res = CIBlockElement::GetList(
			false,
			array(
				'IBLOCK_ID' => IB_ORDERS,
				'PROPERTY_SCHOOL_ID' => $school_id,
				'PROPERTY_STATUS' => 'oscart',
				'PROPERTY_REGION' => getUserRegion($USER->GetID()),
				'PROPERTY_PERIOD' => $arPeriod['ID'],
				'PROPERTY_IZD_ID' => $arIzdId
			),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS', 'PROPERTY_SCHOOL_ID', 'PROPERTY_IZD_ID', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_NDS')
		);

		$arBooks = array();
		$arOrders = array();
		$arSpec = array();

		while ($arFields = $res->GetNext()) {
			$arBooks[] = array('ID' => $arFields['ID'], 'IZD_ID' => $arFields['PROPERTY_IZD_ID_VALUE']);	// Запоминаем ID записи
			if (!is_array($arOrders[$arFields['PROPERTY_IZD_ID_VALUE']])) $arOrders[$arFields['PROPERTY_IZD_ID_VALUE']] = array('SUM' => 0, 'SUM_10' => 0, 'SUM_18' => 0,'ORDER_NUM' => 0);
			$arOrders[$arFields['PROPERTY_IZD_ID_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
			if ($arFields['PROPERTY_NDS_VALUE'] == 18)
				$arOrders[$arFields['PROPERTY_IZD_ID_VALUE']]['SUM_18'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
			else
				$arOrders[$arFields['PROPERTY_IZD_ID_VALUE']]['SUM_10'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
		}

		// Создаем новые заказы
		foreach ($arOrders as $izd_id => $arOrder) {
			$newNum = getNextOrderNum($school_id);
			$el = new CIBlockElement;
			$new_id = $el->Add(array(
				'MODIFIED_BY' => $USER->GetID(),
				'IBLOCK_SECTION_ID' => false,
				'IBLOCK_ID' => IB_ORDERS_LIST,
				'NAME' => $school_id . '-' . $newNum,
				'ACTIVE' => 'Y',
				'DATE_ACTIVE_FROM' => ConvertTimeStamp(false, 'FULL'),
				'PROPERTY_VALUES' => array(
					'SCHOOL_ID' => $school_id,
					'STATUS' => 'osdocs',
					'IZD_ID' => $izd_id,
					'SUM' => $arOrder['SUM'],
					'SUM_10' => $arOrder['SUM_10'],
					'SUM_18' => $arOrder['SUM_18'],
					'PERIOD' => $arPeriod['ID'],
					'REGION' => getUserRegion($USER->GetID()),
					'ORDER_NUM' => $newNum
				)
			));
			if ($new_id) {
				$arOrders[$izd_id]['ORDER_NUM'] = $new_id;
				$arSpec[] = $new_id;
			} else {
				die('Ошибка обработки заказа: обратитесь к администратору системы через форму в личном кабинете!');
			}
		}

		// Меняем статус в корзине и добавляем номер заказа
		foreach ($arBooks as $arBook) {
			CIBlockElement::SetPropertyValuesEx($arBook['ID'], 9, array('STATUS' => 'osdocs', 'ORDER_NUM' => $arOrders[$arBook['IZD_ID']]['ORDER_NUM']));
		}

		$result_state = 'success';
		$result_info = 'Ваш заказ отправлен на формирование! Состояние заказа Вы можете посмотреть в разделе <a href="/orders/">"Заказы"</a>.';

		$make_spec = true;
		// Формируем строку для передачи в скрипт формирования спецификаций
		$strSpecID = '';
		foreach ($arSpec as $value) $strSpecID .= $value . ',';
	}

	// Устанавливаем фильтр по ID школы пользователя и по состоянию "В корзине", или по параметру GET sch_id, если пользователь - админ
	$arFilter = array('PROPERTY_SCHOOL_ID' => (CSite::InGroup(array(8)) ? get_schoolID($USER->GetID()) : $_GET['sch_id']), 'PROPERTY_STATUS' => 'oscart');

//	echo '<pre>' .print_r($arFilter, 1) . '</pre>';


?>

<?if ($make_spec):?>
	<script type="text/javascript">
		$(document).ready(function() {
			makeSpec('<?=$strSpecID;?>');
		});
	</script>
<?endif;?>

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
	"basket", 
	array(
		"IBLOCK_TYPE" => "school_books",
		"IBLOCK_ID" => "9",
		"NEWS_COUNT" => "99999",
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
			1 => "COUNT",
			2 => "PRICE",
			3 => "BOOK",
			4 => "IZD_ID",
			5 => "",
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
		"SET_TITLE" => "Y",
		"SET_BROWSER_TITLE" => "Y",
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
		"AJAX_OPTION_ADDITIONAL" => "",
		"COMPONENT_TEMPLATE" => "basket",
		"SET_LAST_MODIFIED" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>