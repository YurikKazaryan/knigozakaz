<?
/********************************************************************
* Возвращает информацию об учебнике для добавления в инвентаризацию
*
* Параметры (передаются через POST)
*    BOOK_ID - ID учебника
*    ALIGN_LEFT - 1 (влево), иначе (по умолчанию) - как для списка
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметров
$bookID = trim($_POST['BOOK_ID']);
$alignLeft = ($_POST['ALIGN_LEFT'] == 1 ? true : false);

$result = array('error' => 1, 'body' => '');

if($bookID && CModule::IncludeModule('iblock')) {

	$arFilter = array('IBLOCK_ID' => 24, 'ID' => $bookID);

	// Запрашиваем информацию об учебнике
	$res = CIBlockElement::GetList(
		false,
		$arFilter,
		false, array('nTopCount' => $maxResult),
		array('IBLOCK_ID', 'ID', 'NAME',
			'PROPERTY_IZD_ID',
			'PROPERTY_AUTHOR',
			'PROPERTY_FP_CODE',
			'PROPERTY_CLASS',
			'PROPERTY_YEAR',
			'PROPERTY_UMK',
			'PROPERTY_SYSTEM',
			'PROPERTY_EFU'
		)
	);

	if ($arFields = $res->GetNext()) {
		$result['body'] .=
			'<div class="row"><div class="col-xs-12 find-book-name ' . ($alignLeft ? '' : 'text-center') . '">' . $arFields['NAME'] . '</div></div>' .
			($arFields['PROPERTY_AUTHOR_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Автор:</b> ' . $arFields['PROPERTY_AUTHOR_VALUE'] . '</div></div>' : '') .
			($arFields['PROPERTY_FP_CODE_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Код ФП:</b> ' . $arFields['PROPERTY_FP_CODE_VALUE'] . '</div></div>' : '') .
			($arFields['PROPERTY_CLASS_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Класс:</b> ' . $arFields['PROPERTY_CLASS_VALUE'] . '</div></div>' : '') .
			($arFields['PROPERTY_YEAR_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Год издания:</b> ' . $arFields['PROPERTY_YEAR_VALUE'] . '</div></div>' : '') .
			($arFields['PROPERTY_UMK_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>УМК:</b> ' . $arFields['PROPERTY_UMK_VALUE'] . '</div></div>' : '') .
			($arFields['PROPERTY_SYSTEM_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>УМК:</b> ' . $arFields['PROPERTY_SYSTEM_VALUE'] . '</div></div>' : '') .
			($arFields['PROPERTY_EFU_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Электронная форма учебника:</b> ' . ($arFields['PROPERTY_EFU_VALUE'] == 'Y' ? 'Да' : 'Нет') . '</div></div>' : '');
		$result['error'] = 0;
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>