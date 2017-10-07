<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 27.08.17
 * Time: 20:39
 */
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

$data = Array();
if (CModule::IncludeModule("iblock")) {

    if (in_array(8, $USER->GetUserGroupArray())) {
        $arOrder = Array(
            "SORT" => "ASC"
        );

        $arFilter = Array(
            "IBLOCK_ID" => 25,
            "CREATED_BY" => $USER->GetID()
        );

        $arSelectedFields = Array(
            "ID",
            "NAME"
        );

        $books = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelectedFields);

        while ($book = $books->Fetch()) {
            $bid[] = $book["NAME"];
        }

        $bid = array_unique($bid);

        foreach ($bid as $book_id) {
            $arFilter = Array(
                "IBLOCK_ID" => 24,
                "ID" => $book_id
            );

            $arOrder = Array(
                "SORT" => "ASC"
            );

            $arSelectedFields = Array(
                "NAME",
                "PROPERTY_AUTHOR",
                "PROPERTY_YEAR",
                "PROPERTY_FP_CODE",
                "PROPERTY_IZD_ID"
            );

            $book_props = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelectedFields);

            while ($book_prop = $book_props->Fetch()) {
                $detail["BOOK_NAME"] = preg_replace("/  +/", " ", $book_prop["NAME"]);
                $detail["AUTHOR"] = preg_replace("/  +/", " ", $book_prop["PROPERTY_AUTHOR_VALUE"]);
                $detail["YEAR"] = $book_prop["PROPERTY_YEAR_VALUE"];
                $detail["FP_CODE"] = $book_prop["PROPERTY_FP_CODE_VALUE"];
                $arOrder = Array(
                    "SORT" => "ASC"
                );

                $arFilter = Array(
                    "IBLOCK_ID" => 5,
                    "ID" => $book_prop["PROPERTY_IZD_ID_VALUE"]
                );

                $arSelect = Array(
                    "NAME"
                );

                $izd_id = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect, false)->Fetch();

                $detail["IZD"] = $izd_id["NAME"];

                $arFilter = Array(
                    "IBLOCK_ID" => 25,
                    "NAME" => $book_id,
                    "CREATED_BY" => $USER->GetID()
                );

                $arOrder = Array(
                    "SORT" => "ASC"
                );

                $arSelectedFields = Array(
                    "ID",
                    "PROPERTY_177",
                    "PROPERTY_178",
                    "PROPERTY_271"
                );

                $inv_infos = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelectedFields);

                $inv_array = array();

                while ($inv_info = $inv_infos->Fetch()) {
                    $inv_nf["YEAR_PURCHASE"] = $inv_info["PROPERTY_177_VALUE"];
                    $inv_nf["COUNT"] = $inv_info["PROPERTY_178_VALUE"];
                    $inv_nf["WRITEOFF"] = $inv_info["PROPERTY_271_VALUE"];
                    $inv_nf["INV_ID"] = $inv_info["ID"];
                    $inv_array[] = $inv_nf;
                }

                $detail["INV_INFO"] = $inv_array;

                $data[] = $detail;
            }
        }

        echo json_encode($data);
    }
} else {
    echo json_encode("Данный функционал доступен только администраторам школ!");
}