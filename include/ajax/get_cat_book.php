<?
/********************************************************************
* Возвращает информацию об учебнике для каталога
*
* Параметры (передаются через POST)
*    BOOK_ID - ID учебника
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$arBook = getBookInfo(trim($_POST['BOOK_ID']));

$result = array('error' => 1, 'body' => '');

if(is_array($arBook) && CModule::IncludeModule('iblock')) {

	$result['error'] = 0;
	$result['body'] =
		'<div class="row"><div class="control-label col-xs-3">Издательство:</div><div class="col-xs-9">' . get_izd_name($arBook['IBLOCK_SECTION_ID']) . '</div></div>' .
		'<div class="row"><div class="control-label col-xs-3">Код по ФП:</div><div class="col-xs-9">' . $arBook['PROPERTY_FP_CODE_VALUE'] . '</div></div>' .
		'<div class="row"><div class="control-label col-xs-3">Название:</div><div class="col-xs-9">' . $arBook['PROPERTY_TITLE_VALUE'] . '</div></div>' .
		($arBook['PROPERTY_AUTHOR_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">Автор:</div><div class="col-xs-9">' . $arBook['PROPERTY_AUTHOR_VALUE'] . '</div></div>' : '') .
		($arBook['PROPERTY_CLASS_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">Класс:</div><div class="col-xs-9">' . $arBook['PROPERTY_CLASS_VALUE'] . '</div></div>' : '') .
		($arBook['PROPERTY_YEAR_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">Год изд.:</div><div class="col-xs-9">' . $arBook['PROPERTY_YEAR_VALUE'] . '</div></div>' : '') .
		(getOptions('SHOW_CAT_PRICE') ? '<div class="row"><div class="control-label col-xs-3">Цена:</div><div class="col-xs-9">' . sprintf('%0.2f руб.', getPrice($arBook['ID'])) . '</div></div>' : '') .
		($arBook['PROPERTY_UMK_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">УМК:</div><div class="col-xs-9">' . $arBook['PROPERTY_UMK_VALUE'] . '</div></div>' : '') .
		($arBook['PROPERTY_SYSTEM_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">Система:</div><div class="col-xs-9">' . $arBook['PROPERTY_SYSTEM_VALUE'] . '</div></div>' : '') .
		($arBook['PROPERTY_STANDART_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">Стандарт:</div><div class="col-xs-9">' . $arBook['PROPERTY_STANDART_VALUE'] . '</div></div>' : '') .
		($arBook['PROPERTY_PRIM_VALUE'] ? '<div class="row"><div class="control-label col-xs-3">Примечание:</div><div class="col-xs-9">' . $arBook['PROPERTY_PRIM_VALUE'] . '</div></div>' : '') .
		'<div class="row"><div class="control-label col-xs-3">Уникальный код:</div><div class="col-xs-9">' . $arBook['PROPERTY_CODE_1C_VALUE'] . '</div></div>';
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>