<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Список отчетов");?>

<?
	// Обрабатываем опции

	switch ($_GET['m']) {
		case 'delete':							// Удаление отчета администратором и возврат отчета в корзину школы
			if (is_admin($_GET['sch_id']) || is_user_in_group(9)) {
				// Проверяем наличие такого отчета (на всякий случай)
				if (CIBlockElement::GetList(false, array('IBLOCK_ID' => 11, 'ID' => $_GET['order_id'], 'PROPERTY_SCHOOL_ID' => $_GET['sch_id'], 'PROPERTY_STATUS' => 'osrepready'), array(), false, array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_STATUS')) > 0) {

					// Меняем статус учебников
					$res = CIBlockElement::GetList(
						false,
						array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $_GET['order_id']),
						false, false,
						array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_NUM', 'PROPERTY_BOOK_ID', 'PROPERTY_COUNT', 'PROPERTY_SCHOOL_ID')
					);

					while ($arFields = $res->GetNext()) {
						// Если такой учебник уже есть в корзине, то добавляем количество, а этот - удаляем
						$resTest = CIBlockElement::GetList(
							false,
							array('IBLOCK_ID' => 9, 'PROPERTY_SCHOOL_ID' => $arFields['PROPERTY_SCHOOL_ID_VALUE'], 'PROPERTY_BOOK_ID' => $arFields['PROPERTY_BOOK_ID_VALUE'], 'PROPERTY_STATUS' => 'osreport'),
							false, false,
							array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_BOOK_ID', 'PROPERTY_STATUS', 'PROPERTY_COUNT')
						);

						if ($arTestFields = $resTest->GetNext()) {		// Нашли - добавляем количество и удаляем учебник из отчета
							CIBlockElement::SetPropertyValuesEx($arTestFields['ID'], 9, array('COUNT' => $arTestFields['PROPERTY_COUNT_VALUE'] + $arFields['PROPERTY_COUNT_VALUE']));
							CIBlockElement::Delete($arFields['ID']);
						} else {										// Не нашли - просто меняем статус и убираем номер заказа
							CIBlockElement::SetPropertyValuesEx($arFields['ID'], 9, array('ORDER_NUM' => '', 'STATUS' => 'osreport'));
						}

					}
					// Удаляем заказ
					CIBlockElement::Delete($_GET['order_id']);
				}
			}
			break;

		case 'delete_full':						// Полное удаление отчета
			if (is_admin(get_school_by_order($_GET['order_id'])) || is_user_in_group(9)) {
				// Удаляем состав отчета
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $_GET['order_id']), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_NUM'));
				while ($arFields = $res->GetNext()) CIBlockElement::Delete($arFields['ID']);
				CIBlockElement::Delete($_GET['order_id']); // Удаляем сам отчет
			}
			break;

		case 'undelete':						// Снять пометку на удаление
			// Проверяем права (исключаем подстановку параметров)
			if ((is_user_in_group(8) && get_schoolID($USER->GetID()) == $_GET['sch_id']) ||	is_admin($_GET['sch_id']) || is_user_in_group(9)) {
				// Снимаем статус на удаление
				CIBlockElement::SetPropertyValuesEx($_GET['order_id'], 11, array('DELETE' => 0));
				header('Location: /reports/');
			}
			break;

		case 'set_delete':						// Пометить на удаление
			if (is_user_in_group(8) && get_schoolID($USER->GetID()) == $_GET['sch_id']) {
				CIBlockElement::SetPropertyValuesEx($_GET['order_id'], 11, array('DELETE' => 1)); // Ставим статус на удаление
				header('Location: /reports/');
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

		$sch = trim($_POST['ORDERS_FILTER_SCH']);
		if (strlen($sch) > 0)
			$_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID.NAME'] = '%' . $sch . '%';
		else
			unset($_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID.NAME']);

		$num = intval(trim($_POST['ORDERS_FILTER_NUM']));
		if ($num > 0)
			$_SESSION['REPORTS_FILTER']['ID'] = $num;
		else
			unset($_SESSION['REPORTS_FILTER']['ID']);

		$izd = intval(trim($_POST['ORDERS_FILTER_IZD']));
		if ($izd > 0)
			$_SESSION['REPORTS_FILTER']['PROPERTY_IZD_ID'] = $izd;
		else
			unset($_SESSION['REPORTS_FILTER']['PROPERTY_IZD_ID']);

		$mun = intval(trim($_POST['ORDERS_FILTER_MUN']));
		if ($mun > 0) {
			$_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID'] = get_schoolID_by_mun($mun);
			$_SESSION['REPORTS_FILTER_MUN'] = $mun;
		} else {
			unset($_SESSION['REPORTS_FILTER']['PROPERTY_SCHOOL_ID']);
			unset($_SESSION['REPORTS_FILTER_MUN']);
		}

		$stat = trim($_POST['ORDERS_FILTER_STATUS']);
		if (strlen($stat) > 0)
			$_SESSION['REPORTS_FILTER']['PROPERTY_STATUS'] = $stat;
		else
			unset($_SESSION['REPORTS_FILTER']['PROPERTY_STATUS']);

		$date = trim($_POST['ORDERS_FILTER_DATE']);
		if (strlen($date) > 0) {
			$delim = (strpos($date, '.') === false ? (strpos($date, '/') === false ? (strpos($date, '-') === false ? '' : '-') : '/') : '.');
			$arDate = explode($delim, $date);
			$date = mktime(0, 0, 0, $arDate[1], $arDate[0], $arDate[2]);
			if ($date !== false) {
				$_SESSION['REPORTS_FILTER']['>DATE_ACTIVE_FROM'] = ConvertTimeStamp($date, 'SHORT');
				$_SESSION['REPORTS_FILTER']['<DATE_ACTIVE_FROM'] = ConvertTimeStamp($date + 24*60*60, 'SHORT');
			} else {
				unset($_SESSION['REPORTS_FILTER']['>ACTIVE_FROM']);
				unset($_SESSION['REPORTS_FILTER']['<ACTIVE_FROM']);
			}
		} else {
			unset($_SESSION['REPORTS_FILTER']['>ACTIVE_FROM']);
			unset($_SESSION['REPORTS_FILTER']['<ACTIVE_FROM']);
		}
	}

	// Сброс фильтров
	if ($_POST['SET_FILTER'] == 'DEL_FILTER') {
		unset($_SESSION['REPORTS_FILTER']);
		unset($_SESSION['REPORTS_FILTER_MUN']);
	}

	// Применение фильтров
	if (is_array($_SESSION['REPORTS_FILTER'])) {
		foreach ($_SESSION['REPORTS_FILTER'] as $key => $value) {
			if ($key == 'PROPERTY_SCHOOL_ID')
				$arFilter[$key] = $value;
			else
				$arFilter[$key] = array($value);
		}
	}

	$arFilter['PROPERTY_STATUS'] = 'osrepready';

	// Фильтр по рабочему периоду
	$arPeriod = getWorkPeriod();
	$arFilter['PROPERTY_PERIOD'] = $arPeriod['ID'];

  //echo '<pre>' . print_r($arPeriod, 1) . '</pre>';

	// Сортировка
	if (isset($_GET['sort'])) {
		// Определяем поле сортировки, если ошибка - то по-умолчанию
		switch ($_GET['sort']) {
			case 'num':
					if ($_SESSION['REPORTS_SORT_BY'] == 'ID')
						$_SESSION['REPORTS_SORT_ORDER'] = $_SESSION['REPORTS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['REPORTS_SORT_BY'] = 'ID';
						$_SESSION['REPORTS_SORT_ORDER'] = 'DESC';
						$_SESSION['REPORTS_SORT_GET'] = 'num';
					}
					break;
			case 'date1':
					if ($_SESSION['REPORTS_SORT_BY'] == 'ACTIVE_FROM')
						$_SESSION['REPORTS_SORT_ORDER'] = $_SESSION['REPORTS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['REPORTS_SORT_BY'] = 'ACTIVE_FROM';
						$_SESSION['REPORTS_SORT_ORDER'] = 'DESC';
						$_SESSION['REPORTS_SORT_GET'] = 'date1';
					}
					break;
			case 'sch':
					if ($_SESSION['REPORTS_SORT_BY'] == 'PROPERTY_SCHOOL_ID.NAME')
						$_SESSION['REPORTS_SORT_ORDER'] = $_SESSION['REPORTS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['REPORTS_SORT_BY'] = 'PROPERTY_SCHOOL_ID.NAME';
						$_SESSION['REPORTS_SORT_ORDER'] = 'DESC';
						$_SESSION['REPORTS_SORT_GET'] = 'sch';
					}
					break;
			case 'date2':
					if ($_SESSION['REPORTS_SORT_BY'] == 'PROPERTY_DATAPOSTAVKI')
						$_SESSION['REPORTS_SORT_ORDER'] = $_SESSION['REPORTS_SORT_ORDER'] == 'ASC' ? 'DESC' : 'ASC';
					else {
						$_SESSION['REPORTS_SORT_BY'] = 'PROPERTY_DATAPOSTAVKI';
						$_SESSION['REPORTS_SORT_ORDER'] = 'DESC';
						$_SESSION['REPORTS_SORT_GET'] = 'date2';
					}
					break;
			default:
					$_SESSION['REPORTS_SORT_BY'] = 'ACTIVE_FROM';
					$_SESSION['REPORTS_SORT_ORDER'] = 'DESC';
					$_SESSION['REPORTS_SORT_GET'] = 'date1';
		}
	} else {
		$_SESSION['REPORTS_SORT_BY'] = 'ACTIVE_FROM';
		$_SESSION['REPORTS_SORT_ORDER'] = 'DESC';
		$_SESSION['REPORTS_SORT_GET'] = 'date1';
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
	"reports_list",
	array(
		"IBLOCK_TYPE" => "school_books",
		"IBLOCK_ID" => "11",
		"NEWS_COUNT" => "100",
		"SORT_BY1" => $_SESSION["REPORTS_SORT_BY"],
		"SORT_ORDER1" => $_SESSION["REPORTS_SORT_ORDER"],
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
			6 => ""
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
		"PAGER_TEMPLATE" => "modern",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Заказы",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>