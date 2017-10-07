<?
/********************************************************************
* Поиск книг для добавления в инвентаризацию
*
* Параметры (передаются через POST)
*    FIND_STR - образец для поиска
*    MAX_RESULT - максимальное количество найденных книг в результате
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// Обработка параметров
$findStr = trim($_POST['FIND_STR']);
$maxResult = intval($_POST['MAX_RESULT']);

$result = array('cnt' => 0, 'body' => '');

if(CModule::IncludeModule('iblock')) {

	$arFilter = array(
		'IBLOCK_ID' => 24,
		array(
			'LOGIC' => 'OR',
			'NAME' => '%' . $findStr . '%',
			'PROPERTY_AUTHOR' => '%' . $findStr . '%'
		)
	);

	// Считаем кол-во результатов
	$result['cnt'] = CIBlockElement::GetList(false, $arFilter, array(), false, array('IBLOCK_ID', 'ID'));

	if ($result['cnt'] <= $maxResult) {
		// Выбираем учебники по образцу
		$res = CIBlockElement::GetList(
			array('name' => 'asc'),
			$arFilter,
			false, array('nTopCount' => $maxResult),
			array('IBLOCK_ID', 'ID', 'NAME',
				'PROPERTY_IZD_ID',
				'PROPERTY_AUTHOR',
				'PROPERTY_FP_CODE',
				'PROPERTY_CLASS',
				'PROPERTY_YEAR',
				'PROPERTY_UMK',
				'PROPERTY_SYSTEM'
			)
		);

		$row = 1;
		while ($arFields = $res->GetNext()) {
			$result['body'] .= '
				<tr>
					<td>' . $row++ . '</td>
					<td>
						<div class="row"><div class="col-xs-12 find-book-name">' . $arFields['NAME'] . '</div></div>' .
						($arFields['PROPERTY_AUTHOR_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Автор:</b> ' . $arFields['PROPERTY_AUTHOR_VALUE'] . '</div></div>' : '') .
						($arFields['PROPERTY_FP_CODE_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Код ФП:</b> ' . $arFields['PROPERTY_FP_CODE_VALUE'] . '</div></div>' : '') .
						($arFields['PROPERTY_IZD_ID_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Издательство:</b> ' . get_izd_name($arFields['PROPERTY_IZD_ID_VALUE']) . '</div></div>' : '') .
						($arFields['PROPERTY_CLASS_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Класс:</b> ' . $arFields['PROPERTY_CLASS_VALUE'] . '</div></div>' : '') .
						($arFields['PROPERTY_YEAR_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>Год издания:</b> ' . $arFields['PROPERTY_YEAR_VALUE'] . '</div></div>' : '') .
						($arFields['PROPERTY_UMK_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>УМК:</b> ' . $arFields['PROPERTY_UMK_VALUE'] . '</div></div>' : '') .
						($arFields['PROPERTY_SYSTEM_VALUE'] ? '<div class="row"><div class="col-xs-12 find-book-data"><b>УМК:</b> ' . $arFields['PROPERTY_SYSTEM_VALUE'] . '</div></div>' : '') .
					'</td>
					<td>
						<button type="button" class="btn btn-primary" onClick="addBookModal(' . $arFields['ID'] . ');">Выбрать</button>
					</td>
				</tr>
			';
		}

		if ($result['body']) $result['body'] = '<table class="table table-striped table-bordered">' . $result['body'] . '</table>';
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>