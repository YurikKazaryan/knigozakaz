<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	$arTempPeriod = getWorkPeriod();

	$arResult['CURRENT_PERIOD'] = $arTempPeriod['NAME'];
	$arResult['CURRENT_PERIOD_ID'] = $arTempPeriod['ID'];

	$arBookID = array();
	foreach ($arResult['ITEMS'] as $arItem)
		if (!in_array($arItem['PROPERTIES']['BOOK_ID']['VALUE'])) $arBookID[] = $arItem['PROPERTIES']['BOOK_ID']['VALUE'];

	$arResult['BOOKS'] = array();
	foreach ($arBookID as $value) {
		$arTemp = getInvBookInfo($value);
		$arResult['BOOKS'][$value] = $arTemp;

		$str1 = ($arTemp['FP_CODE'] ? '<b>Код ФП: </b>' . $arTemp['FP_CODE'] . '&nbsp;' : '') .
				($arTemp['YEAR'] ? '<b>Год издания: </b>' . $arTemp['YEAR'] . '&nbsp;' : '') .
				($arTemp['CLASS'] ? '<b>Класс: </b>' . $arTemp['CLASS'] : '');

		$str2 = ($arTemp['UMK'] ? '<b>УМК: </b>' . $arTemp['UMK'] . '&nbsp;' : '') .
				($arTemp['SYSTEM'] ? '<b>Система: </b>' . $arTemp['SYSTEM'] : '');

		$arResult['BOOKS'][$value]['FULL_NAME'] =
			'<div class="row"><div class="col-xs-12 book-name">' . $arTemp['NAME'] . '</div></div>' .
			($arTemp['AUTHOR'] ? '<div class="row"><div class="col-xs-12 book-author"><b>Автор: </b>' . $arTemp['AUTHOR'] . '</div></div>' : '') .
			($arTemp['IZD_ID'] ? '<div class="row"><div class="col-xs-12 book-izd"><b>Издательство: </b>' . get_izd_name($arTemp['IZD_ID']) . '</div></div>' : '') .
			($str1 ? '<div class="row"><div class="col-xs-12 book-class">' . $str1 . '</div></div>' : '') .
			($str2 ? '<div class="row"><div class="col-xs-12 book-umk">' . $str2 . '</div></div>' : '');
	}

    //if ( $_SESSION["INV_FILTER"]["PROPERTY_BOOK_ID.NAME"]) $arResult['INV_FILTER_BOOKNAME'] =  substr($_SESSION["INV_FILTER"]["PROPERTY_BOOK_ID.NAME"], 1, strlen( $_SESSION["INV_FILTER"]["PROPERTY_BOOK_ID.NAME"]) - 2);

?>