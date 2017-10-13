<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта

	$fileType = trim($_GET['t']) == 'x' ? 'XLSX' : 'XLS';

	$fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . trim($_GET['f'] . '.' . $fileType);

	if (file_exists($fileName)) {

		// Готовим браузер клиента к загрузке файла
		if ($fileType == 'XLS')
			header('Content-Type: application/vnd.ms-excel');
		else
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header ("Accept-Ranges: bytes");
		header ("Content-Disposition: attachment; filename=" . trim($_GET['f']) . '.' . $fileType);
		readfile($fileName);
		unlink($fileName);
	}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>