<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 29.08.17
 * Time: 21:33
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$invBookIds = $_POST;
$result = false;

if (CModule::IncludeModule('iblock'))
    foreach ($invBookIds as $book_id => $count) {
        /*$arSort = Array(
            "SORT" => "ASC"
        );
        $arFields = Array(
            "IBLOCK_ID" => 25,
            "ID" => $book_id,
            "CREATED_BY" => $USER->GetID(),
        );
        $arSelectedFields = Array(
            "ID"
        );

        $bid = CIBlockElement::GetList($arSort, $arFields, false, false, $arSelectedFields)->Fetch();*/

        CIBlockElement::SetPropertyValues($book_id, 25, $count, "WRITEOFF");

        $result = true;
    }

if ($result)
    echo "1";
else
    echo "0";