<?// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//require($_SERVER['DOCUMENT_ROOT'].'/include/bav.php');
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

	$result = array('text' => '', 'mode' => '');

	$iBlockID = 5; // ***************************************

	global $USER;

	if ($USER->IsAdmin()) {

		CModule::IncludeModule('iblock');

		$result['mode'] = 'ERROR';

		// Получаем следующие записи
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => $iBlockID),
			false, array('nTopCount' => 100),
			array('IBLOCK_ID', 'ID')
		);
		while ($arFields = $res->GetNext()) {
//			CIBlockElement::Delete($arFields['ID']);
			$result['mode'] = 'OK';
		}

		$result['text'] .= 'Выполнено!<br>';
	} else {
		$result['text'] .= 'Запуск разрешен только пользователям со статусом Администратор!';
		$result['mode'] = 'ERROR';
	}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>
