<?
/********************************************************************
* Добавление новой книги в каталог инвентаризации
*
* Параметры (передаются через POST)
*	AUTHOR
*	TITLE
*	IZD
*	FP_CODE
*	YEAR
*	CLASS
*	UMK
*	SYSTEM
*   EFU
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметров
$findStr = trim($_POST['FIND_STR']);
$maxResult = intval($_POST['MAX_RESULT']);

$author = trim($_POST['AUTHOR']);
$title = trim($_POST['TITLE']);
$izd = intval($_POST['IZD']);
$fp_code = trim($_POST['FP_CODE']);
$year = intval($_POST['YEAR']);
$class = trim($_POST['CLASS']);
$umk = trim($_POST['UMK']);
$system = trim($_POST['SYSTEM']);
$efu = (trim($_POST['EFU']) == 'Y' ? 'Y' : 'N');

$result = array('error' => 1, 'id' => 0, 'error_text' => '');

if(CModule::IncludeModule('iblock')) {

	$arNew = Array(
		'MODIFIED_BY' => $USER->GetID(),
		'IBLOCK_SECTION_ID' => false,
		'IBLOCK_ID'      => 24,
		'NAME'           => $title,
		'ACTIVE'         => 'Y',
		'PROPERTY_VALUES'=> array(
			'IZD_ID' => $izd,
			'FP_CODE' => $fp_code,
			'AUTHOR' => $author,
			'CLASS' => $class,
			'YEAR' => $year,
			'ED_IZM' => 33,
			'CODE_1C' => '',
			'UMK' => $umk,
			'SYSTEM' => $system,
			'NOT_VERIFY' => 'Y',
			'WHO_ADD' => $USER->GetID(),
			'EFU' => $efu
		)
	);

	$el = new CIBlockElement;
	$newID = $el->Add($arNew);

	if ($newID) {
		$result['id'] = $newID;
		$result['error'] = 0;
	} else {
		$result['error'] = 1;
		$result['error_text'] = 'Add new book ERROR: ' . $el->LAST_ERROR;
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>