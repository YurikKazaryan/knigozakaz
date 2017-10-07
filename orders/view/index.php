<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	if (!is_user_in_group(9) && !is_user_in_group(8) && !is_user_in_group(7) && !is_user_in_group(6)) header('Location: /');	// Просмотр заказа возможен только администраторам школ и муниципалитетов (без возможности изменения)

	if (!isset($_GET['order_id'])) header('Location: /');

//	echo '<pre>' .print_r($_POST, 1) . '</pre>';

	// Устанавливаем фильтр по номеру заказа
	$arFilter = array('PROPERTY_ORDER_NUM' => $_GET['order_id']);
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	"order_view", 
	array(
		"IBLOCK_TYPE" => "school_books",
		"IBLOCK_ID" => "9",
		"NEWS_COUNT" => "999999",
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
			1 => "BOOK",
			2 => "COUNT",
			3 => "PRICE",
			4 => "IZD_ID",
			5 => "SCHOOL_ID",
			6 => "",
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
		"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>