<?
/********************************************
* Запись cookie
*
* Параметры (передаются через POST)
*    NAME - название
*    VALUE - значение
*    TIME - время жизни в сек
*********************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

// Обработка параметров
$name = trim($_POST['NAME']);
$value = trim($_POST['VALUE']);
$time = intval($_POST['TIME']);

if ($name && $time) {
	setcookie($name, $value, time() + $time);
}

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>