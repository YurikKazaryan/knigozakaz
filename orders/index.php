<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Список заказов");
if (!$USER->IsAuthorized()) LocalRedirect('/auth/');
?>
<?
	// Получаем активный период
	$arPeriod = getWorkPeriod();

	// Обрабатываем опции

	switch ($_GET['m']) {

		case 'order2report':
			if (is_user_in_group(6) || $USER->IsAdmin()) {
				$order_id = intval($_GET['order_id']);
				// Ищем указанный заказ
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 11, 'ID' => $order_id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_PERIOD'));
				if ($arFields = $res->GetNext()) {
					// Проверяем период. Если архивный - не меняем
					if ($arFields['PROPERTY_PERIOD_VALUE'] == $arPeriod['ID']) {
						// Нашли, меняем статус
						CIBlockElement::SetPropertyValuesEx($order_id, 11, array('STATUS' => 'osrepready'));
						// Меняем статус у учебьников
						$resBook = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $order_id), false, false, array('IBLOCK_ID', 'ID'));
						while ($arFieldsBook = $resBook->GetNext()) {
							CIBlockElement::SetPropertyValuesEx($arFieldsBook['ID'], 9, array('STATUS' => 'osrepready'));
						}
					}
				}
			}
			break;

		case 'delete':							// Удаление заказа администратором и возврат заказа в корзину школы
			if (is_admin($_GET['sch_id'])) {

				// Проверяем наличие такого заказа
				$resOrder = CIBlockElement::GetList(
					false,
					array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $_GET['order_id'], 'PROPERTY_SCHOOL_ID' => $_GET['sch_id']),
					false, false,
					array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_PERIOD')
				);

				if ($arOrderFields = $resOrder->GetNext()) {

					if ($arOrderFields['PROPERTY_PERIOD_VALUE'] == $arPeriod['ID']) {

						// Меняем статус учебников
						$res = CIBlockElement::GetList(
							false,
							array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_ORDER_NUM' => $_GET['order_id']),
							false, false,
							array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_NUM', 'PROPERTY_BOOK', 'PROPERTY_COUNT', 'PROPERTY_SCHOOL_ID')
						);

						while ($arFields = $res->Fetch()) {
							// Если такой учебник уже есть в корзине, то добавляем количество, а этот - удаляем
							$resTest = CIBlockElement::GetList(
								false,
								array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_SCHOOL_ID' => $arFields['PROPERTY_SCHOOL_ID_VALUE'], 'PROPERTY_BOOK' => $arFields['PROPERTY_BOOK_VALUE'], 'PROPERTY_STATUS' => 'oscart'),
								false, false,
								array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_BOOK', 'PROPERTY_STATUS', 'PROPERTY_COUNT')
							);

							if ($arTestFields = $resTest->GetNext()) {		// Нашли - добавляем количество и удаляем учебник из заказа
								CIBlockElement::SetPropertyValuesEx($arTestFields['ID'], IB_ORDERS, array('COUNT' => $arTestFields['PROPERTY_COUNT_VALUE'] + $arFields['PROPERTY_COUNT_VALUE']));
								CIBlockElement::Delete($arFields['ID']);
							} else {										// Не нашли - просто меняем статус и убираем номер заказа
								CIBlockElement::SetPropertyValuesEx($arFields['ID'], IB_ORDERS, array('ORDER_NUM' => '', 'STATUS' => 'oscart'));
							}

						}
						// Удаляем заказ
						CIBlockElement::Delete($_GET['order_id']);
					}
				}
			}
			LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			break;

		case 'delete_full':						// Полное удаление заказа
			if (is_admin(get_school_by_order($_GET['order_id']))) {
				$resOrder = CIBlockElement::GetList(false, array('IBLOCK_ID' => 11, 'ID' => $_GET['order_id']), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_PERIOD'));
				$arOrderFields = $resOrder->GetNext();
				if ($arOrderFields['PROPERTY_PERIOD_VALUE'] == $arPeriod['ID']) {
					// Удаляем состав заказа
					$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $_GET['order_id']), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_NUM'));
					while ($arFields = $res->GetNext()) CIBlockElement::Delete($arFields['ID']);
					CIBlockElement::Delete($_GET['order_id']); // Удаляем сам заказ
				}
				LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			}
			break;

		case 'undelete':						// Снять пометку на удаление
			// Проверяем права (исключаем подстановку параметров)
			if ((is_user_in_group(8) && get_schoolID($USER->GetID()) == $_GET['sch_id']) ||	is_admin($_GET['sch_id'])) {
				// Снимаем статус на удаление
				CIBlockElement::SetPropertyValuesEx($_GET['order_id'], 11, array('DELETE' => 0));
				LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			}
			break;

		case 'next_status':						// Поменять статус на следующий
			if (is_admin(get_school_by_order($_GET['order_id']))) {
				state_change($_GET['order_id']);
				LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			}
			break;

		case 'cng_stat' :
			if (is_user_in_group(9) && ($_GET['r'] == 1 || $_GET['r'] == -1)) {
				$arOrders = explode(',', $_GET['orders']);
				foreach ($arOrders as $value)
					state_change($value, $_GET['r']);
				LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			}
			break;

		case 'prev_status':						// Поменять статус на предыдущий
			if (is_admin(get_school_by_order($_GET['order_id']))) {
				state_change($_GET['order_id'], -1);
				LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			}
			break;

		case 'send':							// Отправить заказ в издательство
			break;

		case 'set_delete':						// Пометить на удаление
			if (is_user_in_group(8) && get_schoolID($USER->GetID()) == $_GET['sch_id']) {
				CIBlockElement::SetPropertyValuesEx($_GET['order_id'], 11, array('DELETE' => 1)); // Ставим статус на удаление
				LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
			}
			break;

		case 'groupdpost':								// Групповая смена даты поставки
			if (is_user_in_group(9)) {

				// Обрабатываем время
				$temp = trim($_GET['d']);
				if (strlen($temp) > 0) {
					// находим разделитель
					$delim = (strpos($temp, ',') === false ? (strpos($temp, '.') === false ? (strpos($temp, '/') === false ? (strpos($temp, '-') === false ? '' : '-') : '/') : '.') : ',');
					$arDate = explode($delim, $temp);
					$dpost = mktime(0, 0, 0, $arDate[1], $arDate[0], $arDate[2]);
				}

				// Обрабатываем список заказов
				$arOrders = explode(',', trim($_GET['orders']));
				$f = true;
				foreach ($arOrders as $value) $f = $f && (intval($value) > 0);

				if ($f && (strlen($temp) == 0 || ($delim && $dpost !== false))) {

					if(CModule::IncludeModule('iblock')) {
						if (strlen($temp) == 0)
							foreach ($arOrders as $value) CIBlockElement::SetPropertyValuesEx($value, 11, array('DATAPOSTAVKI' => ''));
						else
							foreach ($arOrders as $value) CIBlockElement::SetPropertyValuesEx($value, 11, array('DATAPOSTAVKI' => ConvertTimeStamp($dpost, 'SHORT')));
						LocalRedirect('/orders/'.($_SESSION['ORDERS_LIST_PAGE'] ? '?PAGEN_1='.$_SESSION['ORDERS_LIST_PAGE'] : ''));
					}
				}
			}
			break;

	}	// switch ()

	// Составляем фильтр школ в зависимости от статуса пользователя
	if (is_user_in_group(8))
		$arFilter = array('PROPERTY_SCHOOL_ID' => get_schoolID($USER->GetID()));	// Устанавливаем фильтр по ID школы пользователя
	elseif (is_user_in_group(6) || is_user_in_group(7) || is_user_in_group(9)) {
		$arFilter = array('PROPERTY_SCHOOL_ID' => get_schoolID_list($USER->GetID()));
	} else
		$arFilter = array('PROPERTY_SCHOOL_ID' => -1);

//	$arFilter['PROPERTY_SCHOOL_ID.NAME'] = array('%1%');

	// Установка фильтров
	if ($_POST['SET_FILTER'] == 'SET_FILTER') {

		$delete = intval(trim($_POST['ORDERS_FILTER_DELETE']));
		if ($delete > 0)
			$_SESSION['ORDERS_FILTER']['PROPERTY_DELETE'] = 1;
		else
			unset($_SESSION['ORDERS_FILTER']['PROPERTY_DELETE']);

		$rem = intval(trim($_POST['ORDERS_FILTER_REM']));
		if ($rem > 0)
			$_SESSION['ORDERS_FILTER']['!PROPERTY_OPER_REM'] = false;
		else
			unset($_SESSION['ORDERS_FILTER']['!PROPERTY_OPER_REM']);

		$sch = trim($_POST['ORDERS_FILTER_SCH']);
		if (strlen($sch) > 0)
			$_SESSION['ORDERS_FILTER']['PROPERTY_SCHOOL_ID.NAME'] = '%' . $sch . '%';
		else
			unset($_SESSION['ORDERS_FILTER']['PROPERTY_SCHOOL_ID.NAME']);

		$num = (trim($_POST['ORDERS_FILTER_NUM']));
		if (strlen($num) > 0)
			$_SESSION['ORDERS_FILTER']['NAME'] = '%' . $num . '%';
		else
			unset($_SESSION['ORDERS_FILTER']['NAME']);

		$mun = intval(trim($_POST['ORDERS_FILTER_MUN']));
		if ($mun > 0) {
			$_SESSION['ORDERS_FILTER']['PROPERTY_SCHOOL_ID'] = get_schoolID_by_mun($mun);
			$_SESSION['ORDERS_FILTER_MUN'] = $mun;
		} else {
			unset($_SESSION['ORDERS_FILTER']['PROPERTY_SCHOOL_ID']);
			unset($_SESSION['ORDERS_FILTER_MUN']);
		}

		$izd = intval(trim($_POST['ORDERS_FILTER_IZD']));
		if ($izd > 0)
			$_SESSION['ORDERS_FILTER']['PROPERTY_IZD_ID'] = $izd;
		else
			unset($_SESSION['ORDERS_FILTER']['PROPERTY_IZD_ID']);

		$stat = trim($_POST['ORDERS_FILTER_STATUS']);
		if (strlen($stat) > 0)
			$_SESSION['ORDERS_FILTER']['PROPERTY_STATUS'] = $stat;
		else
			unset($_SESSION['ORDERS_FILTER']['PROPERTY_STATUS']);

		$date = trim($_POST['ORDERS_FILTER_DATE']);
		if (strlen($date) > 0) {
			$delim = (strpos($date, '.') === false ? (strpos($date, '/') === false ? (strpos($date, '-') === false ? '' : '-') : '/') : '.');
			$arDate = explode($delim, $date);
			$date = mktime(0, 0, 0, $arDate[1], $arDate[0], $arDate[2]);
			if ($date !== false) {
				$_SESSION['ORDERS_FILTER']['>DATE_ACTIVE_FROM'] = ConvertTimeStamp($date, 'SHORT');
				$_SESSION['ORDERS_FILTER']['<DATE_ACTIVE_FROM'] = ConvertTimeStamp($date + 24*60*60, 'SHORT');
			} else {
				unset($_SESSION['ORDERS_FILTER']['>ACTIVE_FROM']);
				unset($_SESSION['ORDERS_FILTER']['<ACTIVE_FROM']);
			}
		} else {
			unset($_SESSION['ORDERS_FILTER']['>ACTIVE_FROM']);
			unset($_SESSION['ORDERS_FILTER']['<ACTIVE_FROM']);
		}

		$date = trim($_POST['ORDERS_FILTER_DATE_POST']);
		if (strlen($date) > 0) {
			if ($date !== '-') {
				$delim = (strpos($date, '.') === false ? (strpos($date, '/') === false ? (strpos($date, '-') === false ? '' : '-') : '/') : '.');
				$arDate = explode($delim, $date);
				$date = mktime(0, 0, 0, $arDate[1], $arDate[0], $arDate[2]);
				if ($date !== false) {
					$_SESSION['ORDERS_FILTER']['>PROPERTY_DATAPOSTAVKI'] = date('Y-m-d', $date);
					$_SESSION['ORDERS_FILTER']['<PROPERTY_DATAPOSTAVKI'] = date('Y-m-d', $date+24*60*60);
				} else {
					unset($_SESSION['ORDERS_FILTER']['>PROPERTY_DATAPOSTAVKI']);
					unset($_SESSION['ORDERS_FILTER']['<PROPERTY_DATAPOSTAVKI']);
				}
			} else {
				$_SESSION['ORDERS_FILTER']['PROPERTY_DATAPOSTAVKI'] = false;
			}
		} else {
			unset($_SESSION['ORDERS_FILTER']['>PROPERTY_DATAPOSTAVKI']);
			unset($_SESSION['ORDERS_FILTER']['<PROPERTY_DATAPOSTAVKI']);
			unset($_SESSION['ORDERS_FILTER']['PROPERTY_DATAPOSTAVKI']);
		}
	}

	// Сброс фильтров
	if ($_POST['SET_FILTER'] == 'DEL_FILTER') {
		unset($_SESSION['ORDERS_FILTER']);
		unset($_SESSION['ORDERS_FILTER_MUN']);
	}

	// Применение фильтров
	if (is_array($_SESSION['ORDERS_FILTER']))
		foreach ($_SESSION['ORDERS_FILTER'] as $key => $value)
			if (is_array($value) || $key == 'PROPERTY_DELETE')
				$arFilter[$key] = $value;
			else
				$arFilter[$key] = array($value);

	$arFilter['!PROPERTY_STATUS'] = 'osrepready';

	// Фильтр по рабочему периоду
	$arPeriod = getWorkPeriod();
	$arFilter['PROPERTY_PERIOD'] = $arPeriod['ID'];

//test_print($arFilter);

	// Сортировка
	if (isset($_GET['sort'])) {
		// Определяем поле сортировки, если ошибка - то по-умолчанию
		switch ($_GET['sort']) {
			case 'num':
					if ($_SESSION['ORDERS_SORT_BY'] == 'ID')
						$_SESSION['ORDERS_SORT_ORDER'] = $_SESSION['ORDERS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['ORDERS_SORT_BY'] = 'ID';
						$_SESSION['ORDERS_SORT_ORDER'] = 'DESC';
						$_SESSION['ORDERS_SORT_GET'] = 'num';
					}
					break;
			case 'date1':
					if ($_SESSION['ORDERS_SORT_BY'] == 'ACTIVE_FROM')
						$_SESSION['ORDERS_SORT_ORDER'] = $_SESSION['ORDERS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['ORDERS_SORT_BY'] = 'ACTIVE_FROM';
						$_SESSION['ORDERS_SORT_ORDER'] = 'DESC';
						$_SESSION['ORDERS_SORT_GET'] = 'date1';
					}
					break;
			case 'sch':
					if ($_SESSION['ORDERS_SORT_BY'] == 'PROPERTY_SCHOOL_ID.NAME')
						$_SESSION['ORDERS_SORT_ORDER'] = $_SESSION['ORDERS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['ORDERS_SORT_BY'] = 'PROPERTY_SCHOOL_ID.NAME';
						$_SESSION['ORDERS_SORT_ORDER'] = 'DESC';
						$_SESSION['ORDERS_SORT_GET'] = 'sch';
					}
					break;
			case 'date2':
					if ($_SESSION['ORDERS_SORT_BY'] == 'PROPERTY_DATAPOSTAVKI')
						$_SESSION['ORDERS_SORT_ORDER'] = $_SESSION['ORDERS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['ORDERS_SORT_BY'] = 'PROPERTY_DATAPOSTAVKI';
						$_SESSION['ORDERS_SORT_ORDER'] = 'DESC';
						$_SESSION['ORDERS_SORT_GET'] = 'date2';
					}
					break;
			default:
					$_SESSION['ORDERS_SORT_BY'] = 'ACTIVE_FROM';
					$_SESSION['ORDERS_SORT_ORDER'] = 'DESC';
					$_SESSION['ORDERS_SORT_GET'] = 'date1';
		}
	} else {
		$_SESSION['ORDERS_SORT_BY'] = 'ACTIVE_FROM';
		$_SESSION['ORDERS_SORT_ORDER'] = 'DESC';
		$_SESSION['ORDERS_SORT_GET'] = 'date1';
	}

	/********************
	ВНИМАНИЕ! В случае, если настройка перепишет параметры компонента,
	значения "SORT_BY1" и "SORT_ORDER1" должны заполняться, соответствено,
	переменными сессии $_SESSION['ORDERS_SORT_BY'] и $_SESSION['ORDERS_SORT_ORDER']
	********************/

//echo 'sort_by = ' . $_SESSION['ORDERS_SORT_BY'] . '<br>' .
//	 'sort_order = ' . $_SESSION['ORDERS_SORT_ORDER'] . '<br>' .
//	 'sort_get = ' . $_SESSION['ORDERS_SORT_GET'];

?>

<?$APPLICATION->IncludeComponent(
	"bitrix:news.list",
	"orders_list",
	array(
		"IBLOCK_TYPE" => "school_books",
		"IBLOCK_ID" => "11",
		"NEWS_COUNT" => "20",
		"SORT_BY1" => $_SESSION["ORDERS_SORT_BY"],
		"SORT_ORDER1" => $_SESSION["ORDERS_SORT_ORDER"],
		"SORT_BY2" => "",
		"SORT_ORDER2" => "",
		"FILTER_NAME" => "arFilter",
		"FIELD_CODE" => array(
			0 => "NAME",
			1 => "DATE_ACTIVE_FROM",
			2 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "OPER_REM",
			1 => "STATUS",
			2 => "SUM",
			3 => "DELETE",
			4 => "IZD_ID",
			5 => "SPEC",
			6 => "PERIOD",
			7 => "ORDER_NUM"
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
		"PAGER_TEMPLATE" => "modern",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Заказы",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"AJAX_OPTION_ADDITIONAL" => "",
		"COMPONENT_TEMPLATE" => "orders_list",
		"SET_LAST_MODIFIED" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>