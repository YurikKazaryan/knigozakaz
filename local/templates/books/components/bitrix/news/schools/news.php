<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<?
	// Удаление школы
	if ($_GET['mode'] == 'remove' && (CSite::InGroup(array(6)))) {
		$sid = intval($_GET['sid']);
		if ($sid) {
			// Проверяем наличие школы с указанным ID
			if (CIBlockElement::GetList(false, array('IBLOCK_ID' => 10, 'ID' => $sid), array(), false, array('IBLOCK_ID', 'ID')) > 0) {

				// Получаем список пользователей школы
				$arUsers = array();
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 10, 'ID' => $sid), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ADMIN'));
				if ($arFields = $res->GetNext())
					foreach ($arFields['PROPERTY_ADMIN_VALUE'] as $value) if ($value != 1) $arUsers[] = $value;

				// Ищем заказы и книги указанной школы для удаления
				$arTemp = array();
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 11, 'PROPERTY_SCHOOL_ID' => $sid), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID'));
				while ($arFields = $res->GetNext()) $arTemp[] = $arFields['ID'];

				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 9, 'PROPERTY_SCHOOL_ID' => $sid), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID'));
				while ($arFields = $res->GetNext()) $arTemp[] = $arFields['ID'];

				if (CIBlockElement::Delete($sid)) {
					foreach ($arTemp as $value) CIBlockElement::Delete($value);
					foreach ($arUsers as $value) CUser::Delete($value);
					$result_state = 'info';
					$result_info = 'Школа успешно удалена!';
				}
			} else {
				$result_state = 'danger';
				$result_info = 'Нет школы с указанным идентификатором!<br><b>Удаление отменено!</b>';
			}
		} else {
			$result_state = 'danger';
			$result_info = 'Передан ошибочный идентификатор школы!<br><b>Удаление отменено!</b>';
		}
	}

	// Обрабатываем добавление школы
	if ($_POST['MODE'] == 'NEWSCHOOL') {
		if (is_user_in_group(6) || is_user_in_group(7)) {
			$mun = intval($_POST['schoolMun']);
			$name = trim($_POST['NAME']);
			if (strlen($name) > 10 && $mun) {
				$temp = new CIBlockElement;
				$new_id = $temp->Add(array(
					'MODIFIED_BY' => $USER->GetID(),
					'IBLOCK_SECTION_ID' => false,
					'IBLOCK_ID' => 10,
					'NAME' => $name,
					'ACTIVE' => 'Y',
					'PROPERTY_VALUES' => array(
						'MUN' => $mun,
						'OBLAST' => getUserRegion()
					)
				));
				if ($new_id) {
					$result_state = 'info';
					$result_info = 'Школа успешно добавлена!';
				} else {
					$result_state = 'danger';
					$result_info = 'Ошибка при добавлении школы!';
				}
			}
		}
	}

	// Выставляем фильтр по принадлежности текущего пользователя (админ системы, муниципалитета, школы)
	if ($USER->IsAdmin() || is_user_in_group(6)) {	// Админ системы
		unset($GLOBALS['arFilterSchools']);

	} elseif (is_user_in_group(7)) {							// Админ муниципалитета
		$munList = get_munID_list($USER->GetID());
		$GLOBALS['arFilterSchools'] = ($munList ? array('PROPERTY_MUN' => $munList) : '###');
	} elseif (is_user_in_group(8)) {							// Админ школы
		$schoolID = get_schoolID($USER->GetID());
		$GLOBALS['arFilterSchools'] = ($schoolID ? array('ID' => $schoolID) : '###');
	}

	// Обрабатываем фильтр из списка школ
	if ($_POST['SET_FILTER'] == 'SET_FILTER') {

		$sch = trim($_POST['FILTER_SCHOOL_NAME']);
		if (strlen($sch) > 0)
			$_SESSION['SCHOOLS_FILTER']['NAME'] = '%' . $sch . '%';
		else
			unset($_SESSION['SCHOOLS_FILTER']['NAME']);

		$mun = intval(trim($_POST['FILTER_MUN']));
		if ($mun > 0) {
			$_SESSION['SCHOOLS_FILTER']['PROPERTY_MUN'] = get_mun_id_for_filter($mun);
			$_SESSION['SCHOOLS_FILTER_MUN'] = $mun;
		} else {
			unset($_SESSION['SCHOOLS_FILTER']['PROPERTY_MUN']);
			unset($_SESSION['SCHOOLS_FILTER_MUN']);
		}
	}

	// Сброс фильтров
	if ($_POST['SET_FILTER'] == 'DEL_FILTER') {
		unset($_SESSION['SCHOOLS_FILTER']);
		unset($_SESSION['SCHOOLS_FILTER_MUN']);
	}

	// Применение фильтров
	if (is_array($_SESSION['SCHOOLS_FILTER']))
		foreach ($_SESSION['SCHOOLS_FILTER'] as $key => $value)
			$GLOBALS['arFilterSchools'][$key] = $value;


	// Выставляем фильтр по региону
	$GLOBALS['arFilterSchools']['PROPERTY_OBLAST'] = getUserRegion();

?>

<?if ($result_state):?>
	<div class="alert alert-<?=$result_state?> text-center" id="alert-box"><?=$result_info?></div>
	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout(function() { $('#alert-box').slideUp('slow') }, 3000);
		});
	</script>
<?endif;?>

<?if ($GLOBALS['arFilterSchools'] != '###'):?>

	<?if($arParams["USE_SEARCH"]=="Y"):?>
	<?=GetMessage("SEARCH_LABEL")?><?$APPLICATION->IncludeComponent(
		"bitrix:search.form",
		"flat",
		Array(
			"PAGE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["search"]
		),
		$component
	);?>
	<br />
	<?endif?>

	<?$APPLICATION->IncludeComponent(
		"bitrix:news.list",
		"",
		Array(
			"IBLOCK_TYPE"	=>	$arParams["IBLOCK_TYPE"],
			"IBLOCK_ID"	=>	$arParams["IBLOCK_ID"],
			"NEWS_COUNT"	=>	$arParams["NEWS_COUNT"],
			"SORT_BY1"	=>	$arParams["SORT_BY1"],
			"SORT_ORDER1"	=>	$arParams["SORT_ORDER1"],
			"SORT_BY2"	=>	$arParams["SORT_BY2"],
			"SORT_ORDER2"	=>	$arParams["SORT_ORDER2"],
			"FIELD_CODE"	=>	$arParams["LIST_FIELD_CODE"],
			"PROPERTY_CODE"	=>	$arParams["LIST_PROPERTY_CODE"],
			"DETAIL_URL"	=>	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
			"SECTION_URL"	=>	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
			"IBLOCK_URL"	=>	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["news"],
			"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"],
			"SET_TITLE"	=>	$arParams["SET_TITLE"],
			"SET_STATUS_404" => $arParams["SET_STATUS_404"],
			"INCLUDE_IBLOCK_INTO_CHAIN"	=>	$arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
			"CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
			"CACHE_TIME"	=>	$arParams["CACHE_TIME"],
			"CACHE_FILTER"	=>	$arParams["CACHE_FILTER"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"DISPLAY_TOP_PAGER"	=>	$arParams["DISPLAY_TOP_PAGER"],
			"DISPLAY_BOTTOM_PAGER"	=>	$arParams["DISPLAY_BOTTOM_PAGER"],
			"PAGER_TITLE"	=>	$arParams["PAGER_TITLE"],
			"PAGER_TEMPLATE"	=>	$arParams["PAGER_TEMPLATE"],
			"PAGER_SHOW_ALWAYS"	=>	$arParams["PAGER_SHOW_ALWAYS"],
			"PAGER_DESC_NUMBERING"	=>	$arParams["PAGER_DESC_NUMBERING"],
			"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
			"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
			"DISPLAY_DATE"	=>	$arParams["DISPLAY_DATE"],
			"DISPLAY_NAME"	=>	"Y",
			"DISPLAY_PICTURE"	=>	$arParams["DISPLAY_PICTURE"],
			"DISPLAY_PREVIEW_TEXT"	=>	$arParams["DISPLAY_PREVIEW_TEXT"],
			"PREVIEW_TRUNCATE_LEN"	=>	$arParams["PREVIEW_TRUNCATE_LEN"],
			"ACTIVE_DATE_FORMAT"	=>	$arParams["LIST_ACTIVE_DATE_FORMAT"],
			"USE_PERMISSIONS"	=>	$arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS"	=>	$arParams["GROUP_PERMISSIONS"],
			"FILTER_NAME"	=>	$arParams["FILTER_NAME"],
			"HIDE_LINK_WHEN_NO_DETAIL"	=>	$arParams["HIDE_LINK_WHEN_NO_DETAIL"],
			"CHECK_DATES"	=>	$arParams["CHECK_DATES"],
		),
		$component
	);?>
<?endif;?>