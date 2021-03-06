<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 16.09.17
 * Time: 17:54
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

if (CModule::IncludeModule("iblock")) {
    $subjects = array(0 => "", 1 => "Алгебра", 2 => "Английский язык", 3 => "Астрономия", 4 => "Биология", 5 => "История",
        6 => "География", 7 => "Геометрия", 8 => "Естествознание", 9 => "Изобразительное искусство", 10 => "Информатика",
        11 => "Испанский язык", 12 => "Литература", 14 => "Литературное чтение", 15 => "Математика", 16 => "Мировая художественная культура",
        17 => "Музыка", 18 => "Немецкий язык", 19 => "Обществознание", 20 => "Окружающий мир", 21 => "Основы безопасности жизнедеятельности",
        22 => "Основы духовно-нравственной культуры", 23 => "Право", 24 => "Природоведение", 25 => "Родная литература",
        26 => "Родной язык", 27 => "Россия в мире", 28 => "Русский язык и литература", 29 => "Русский язык", 30 => "Технология",
        31 => "Физика", 32 => "Физическая культура", 33 => "Финский язык", 34 => "Французский язык", 35 => "Химия", 36 => "Черчение",
        37 => "Чтение", 38 => "Экология", 39 => "Экономика");

    $izd = $_POST["UMK_IZD"];
    $class = $_POST["UMK_CLASS"] == 0 ? "" : $_POST["UMK_CLASS"];
    $subject = $_POST["UMK_SUBJ"];

    echo json_encode(getBookListByParams($izd, $class, $subject));
}