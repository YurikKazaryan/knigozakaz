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
    $BDG_IZD = $_POST["BDG_IZD"] === "*" ? "" : $_POST["BDG_IZD"];
    $BDG_GROUP = $_POST["BDG_GROUP"];

    $regionList = get_munID_list($USER->GetID());

    $delete = getUserRegion($USER->GetID());

    $regionList = array_flip($regionList);

    unset($regionList[$delete]);

    $regionList = array_flip($regionList);

    $arFilter = Array(
        "IBLOCK_ID" => 9,
        "PROPERTY_IZD_ID" => $BDG_IZD
    );

    $arSelectedFields = Array(
        "PROPERTY_BOOK",
        "PROPERTY_SCHOOL_ID",
        "PROPERTY_PRICE",
        "PROPERTY_COUNT"
    );

    foreach ($regionList as $key => $regionId) {
        $arFilter["PROPERTY_SCHOOL_ID"] = get_schoolID_by_mun($regionId);
        $arFilter["IBLOCK_ID"] = 9;

        isset($arFilter["PROPERTY_SCHOOL_ID"]) ? : $arFilter["PROPERTY_SCHOOL_ID"] = Array(-1);
        $orders = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields);

        while ($order = $orders->Fetch()) {
            $data[$regionId][$order["PROPERTY_SCHOOL_ID_VALUE"]][] = $order;
        }
    }

    switch ($BDG_GROUP) {
        case 1:
            foreach ($data as $regionId => $schoolData) {
                $costPerMunicipality = 0;
                foreach ($schoolData as $schoolId => $booksArray) {
                    foreach ($booksArray as $item) {
                        $costPerMunicipality += ($item["PROPERTY_PRICE_VALUE"] * $item["PROPERTY_COUNT_VALUE"]);
                    }
                }
                $result[getRegionName($regionId)] = $costPerMunicipality;
            }

            unset($data);

            foreach ($regionList as $item) {
                if (array_key_exists(getRegionName($item), $result))
                    $data[getRegionName($item)] = $result[getRegionName($item)];
                else
                    $data[getRegionName($item)] = 0;
            }

            ksort($data);

            echo(json_encode($data));
            break;
        case 2:
            foreach ($data as $regionId => $schoolData) {
                foreach ($schoolData as $schoolId => $booksArray) {
                    $costPerSchool = 0;
                    foreach ($booksArray as $item) {
                        $costPerSchool += ($item["PROPERTY_PRICE_VALUE"] * $item["PROPERTY_COUNT_VALUE"]);
                        $arFilter = Array(
                            "IBLOCK_ID" => 5,
                            "ID" => $item["PROPERTY_BOOK_VALUE"]
                        );

                        $arSelectedFields = Array(
                            "ID",
                            "PROPERTY_AUTHOR",
                            "PROPERTY_TITLE",
                            "PROPERTY_CLASS",
                        );

                        $bookInfo = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields)->Fetch();
                        $singleBook["SCHOOL_ID"] = $schoolId;
                        $singleBook["AUTHOR"] = $bookInfo["PROPERTY_AUTHOR_VALUE"];
                        $singleBook["TITLE"] = $bookInfo["PROPERTY_TITLE_VALUE"];
                        $singleBook["CLASS"] = $bookInfo["PROPERTY_CLASS_VALUE"];
                        $singleBook["PRICE"] = $item["PROPERTY_PRICE_VALUE"];
                        $singleBook["COUNT"] = $item["PROPERTY_COUNT_VALUE"];
                        $result[getRegionName($regionId)][get_school_name_by_id($schoolId)]["BOOKS"][] = $singleBook;

                        unset($singleBook);
                    }
                    $result[getRegionName($regionId)][get_school_name_by_id($schoolId)]["COST"] = $costPerSchool;
                    $result[getRegionName($regionId)][get_school_name_by_id($schoolId)]["SCHOOL_ID"] = $schoolId;

                }
            }

            unset($data);

            foreach ($regionList as $item) {
                if (array_key_exists(getRegionName($item), $result))
                    $data[getRegionName($item)] = $result[getRegionName($item)];
                else
                    $data[getRegionName($item)] = Array("Нет данных" => Array("COST" => 0, "BOOKS" => 0));
            }

            ksort($data);

            echo(json_encode($data));
            break;
    }
}