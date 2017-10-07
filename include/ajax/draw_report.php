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
    $fieldIDs = $_GET["fieldIds"];
    $rpType = $_GET["rpType"];

    $data2 = Array();

    switch ($_GET["rpType"]) {
        case "rpOrders":
            $arSelectedFields5 = array();
            $arSelectedFields9 = array();

            $properties9 = Array(24, 25, 33, 34, 76, 249, 257);
            $properties5 = Array(10, 11, 15, 19, 22, 23, 74, 232);

            foreach ($fieldIDs as $fieldID => $value) {
                if (in_array($value, $properties5))
                    array_push($arSelectedFields5, "PROPERTY_" . $value);
                if (in_array($value, $properties9))
                    array_push($arSelectedFields9, "PROPERTY_" . $value);
                if ($value == 250)
                    $arSelectedFields = "PROPERTY_250";
            }

            array_push($arSelectedFields9, "PROPERTY_249");

            $munList = getMunList2($USER->GetID());

            $schoolList = array();

            foreach ($munList as $munID => $value) {
                $schoolList[get_obl_name($munID)] = get_schoolID_by_mun($munID);
            }

            foreach ($schoolList as $key => $schoolId) {
                if (isset($arSelectedFields)) $dataOrder1["PROPERTY_250_VALUE"] = $key;

                $arFilter = Array(
                    "PROPERTY_SCHOOL_ID" => $schoolId,
                    "IBLOCK_ID" => 9,
                    "!PROPERTY_STATUS" => ["osreport", "osrepready"]
                );

                if ((count($arSelectedFields9) > 0) || (count($arSelectedFields5) > 0)) {
                    $dataOrders = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields9);

                    while ($dataOrder = $dataOrders->Fetch()) {
                        $arFilter = Array(
                            "IBLOCK_ID" => 5,
                            "ID" => $dataOrder["PROPERTY_249_VALUE"]
                        );

                        if (isset($dataOrder["PROPERTY_24_VALUE"])) $dataOrder1["PROPERTY_24_VALUE"] = getStatusName($dataOrder["PROPERTY_24_VALUE"]);
                        if (isset($dataOrder["PROPERTY_25_VALUE"])) $dataOrder1["PROPERTY_25_VALUE"] = get_school_name_by_id($dataOrder["PROPERTY_25_VALUE"]);
                        if (isset($dataOrder["PROPERTY_76_VALUE"])) $dataOrder1["PROPERTY_76_VALUE"] = get_izd_name($dataOrder["PROPERTY_76_VALUE"]);

                        $dataBooks = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields5);

                        while ($dataBook = $dataBooks->Fetch())
                            $data[] = array_merge($dataBook, $dataOrder1);
                    }
                }
            }

            foreach ($data as $id => $dataArray) {
                foreach ($dataArray as $key => $value) {
                    if (strpos($key, "_ID", 0) || $key === "PROPERTY_249_VALUE")
                        unset($dataArray[$key]);
                    if ($value === null)
                        $dataArray[$key] = "";
                }
                $data2[] = $dataArray;
            }

            break;
        case "rpInventory":
            $arSelectedFields24 = array();
            $arSelectedFields25 = array();

            $properties24 = Array(166, 167, 168, 169, 170, 171, 172, 173, 185, 270);
            $properties25 = Array(174, 175, 177, 178, 271);

            foreach ($fieldIDs as $fieldID => $value) {
                if (in_array($value, $properties24))
                    array_push($arSelectedFields24, "PROPERTY_" . $value);
                if (in_array($value, $properties25))
                    array_push($arSelectedFields25, "PROPERTY_" . $value);
            }

            array_push($arSelectedFields25, "PROPERTY_176");

            $munList = getMunList2($USER->GetID());

            $schoolList = array();

            foreach ($munList as $munID => $value) {
                $schoolList[get_obl_name($munID)] = get_schoolID_by_mun($munID);
            }

            foreach ($schoolList as $key => $schoolId) {
                $arFilter = Array(
                    "PROPERTY_SCHOOL_ID" => $schoolId,
                    "IBLOCK_ID" => 25
                );

                if ((count($arSelectedFields24) > 0) || (count($arSelectedFields25) > 0)) {
                    $dataInvs = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields25);

                    while($dataInv = $dataInvs->Fetch()) {
                        $arFilter = Array (
                            "IBLOCK_ID" => 24,
                            "ID" => $dataInv["PROPERTY_176_VALUE"]
                        );

                        if (isset($dataInv["PROPERTY_174_VALUE"])) $dataInv["PROPERTY_174_VALUE"] = getRegionName($dataInv["PROPERTY_174_VALUE"]);
                        if (isset($dataInv["PROPERTY_175_VALUE"])) $dataInv["PROPERTY_175_VALUE"] = get_school_name_by_id($dataInv["PROPERTY_175_VALUE"]);

                        $dataBooks = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields24);

                        while($dataBook = $dataBooks->Fetch()) {
                            if (isset($dataBook["PROPERTY_185_VALUE"])) $dataBook["PROPERTY_185_VALUE"] = get_izd_name($dataBook["PROPERTY_185_VALUE"]);
                            $data[] = array_merge($dataBook, $dataInv);
                        }
                    }
                }
            }

            foreach ($data as $id => $dataArray) {
                foreach ($dataArray as $key => $value) {
                    if (strpos($key, "_ID", 0) || $key === "PROPERTY_176_VALUE")
                        unset($dataArray[$key]);
                    if ($value === null)
                        $dataArray[$key] = "";
                }
                $data2[] = $dataArray;
            }

            break;
        case "rpSvod":
            $munIDs = getMunList2($USER->GetID());

            foreach ($munIDs as $munID => $munName) {
                $schoolList = get_schoolID_by_mun($munID);

                foreach ($schoolList as $key => $schoolId) {
                    $schoolName = get_school_name_by_id($schoolId);

                    $arFilter = Array (
                        "IBLOCK_ID" => 25,
                        "PROPERTY_SCHOOL_ID" => $schoolId
                    );

                    $arSelectedFields25 = Array();
                    $arSelectedFields25[] = "PROPERTY_BOOK_ID";

                    if (in_array("BOOK_COUNT", $fieldIDs)) array_push($arSelectedFields25, "PROPERTY_COUNT");
                    if (in_array("BOOK_WRITEOFF", $fieldIDs)) array_push($arSelectedFields25, "PROPERTY_WRITEOFF");

                    $invBooks = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields25);

                    while ($invBook = $invBooks->Fetch()) {
                        $arFilter = Array (
                            "IBLOCK_ID" => 24,
                            "ID" => $invBook["PROPERTY_BOOK_ID_VALUE"]
                        );

                        $arSelectedFields24 = Array();
                        if (in_array("BOOK_NAME", $fieldIDs)) array_push($arSelectedFields24, "NAME");
                        if (in_array("CLASS", $fieldIDs)) array_push($arSelectedFields24, "PROPERTY_CLASS");
                        if (in_array("BOOK_PRICE", $fieldIDs)) array_push($arSelectedFields24, "PROPERTY_PRICE");
                        if (in_array("IZD", $fieldIDs)) array_push($arSelectedFields24, "PROPERTY_IZD_ID");
                        if (in_array("BOOK_AUTHOR", $fieldIDs)) array_push($arSelectedFields24, "PROPERTY_AUTHOR");

                        $invBooksInfo = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields24);

                        while ($invBookInfo = $invBooksInfo->Fetch()) {
                            $invBookInfo["PROPERTY_MUNICIPALITY"] = str_replace(".", "", $munName);
                            $invBookInfo["PROPERTY_SCHOOL"] = get_school_name_by_id($schoolId);
                            $invBookInfo["PROPERTY_BOOK_WRITEOFF"] = $invBook["PROPERTY_WRITEOFF_VALUE"] === null ? 0 : $invBook["PROPERTY_WRITEOFF_VALUE"];
                            $invBookInfo["PROPERTY_COUNT"] = $invBook["PROPERTY_COUNT_VALUE"];
                            $invBookInfo["PROPERTY_IZD"] = get_izd_name($invBookInfo["PROPERTY_IZD_ID_VALUE"]);
                            $invBookInfo["PROPERTY_BOOK_NAME"] = $invBookInfo["NAME"];
                            $invBookInfo["PROPERTY_BOOK_COUNT"] = $invBook["PROPERTY_COUNT_VALUE"];
                            $invBookInfo["PROPERTY_BOOK_PRICE"] = $invBookInfo["PROPERTY_PRICE_VALUE"];
                            $invBookInfo["PROPERTY_CLASS"] = $invBookInfo["PROPERTY_CLASS_VALUE"];
                            $invBookInfo["PROPERTY_BOOK_AUTHOR"] = $invBookInfo["PROPERTY_AUTHOR_VALUE"];
                            $invBookInfo["PROPERTY_BOOK_NAME"] = trim(str_replace($invBookInfo["PROPERTY_BOOK_AUTHOR"], "", $invBookInfo["NAME"]));
                            $invBookInfo["PROPERTY_SUBJECT"] = trim(stristr($invBookInfo["PROPERTY_BOOK_NAME"], ".", true));

                            $arFilter = Array(
                                "IBLOCK_ID" => 37,
                                "NAME" => $schoolId
                            );

                            $arOrder = Array(
                                "ID" => "DESC"
                            );

                            $arSelectedFields = Array(
                                "PROPERTY_259",
                                "PROPERTY_260",
                                "PROPERTY_261",
                                "PROPERTY_262",
                                "PROPERTY_263",
                                "PROPERTY_264",
                                "PROPERTY_265",
                                "PROPERTY_266",
                                "PROPERTY_267",
                                "PROPERTY_268",
                                "PROPERTY_269",
                            );

                            $arNavStartParams = Array(
                                "nTopCount" => 1
                            );

                            $classes = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavStartParams, $arSelectedFields)->Fetch();

                            if (!$classes) {
                                $classes["ID"] = $schoolId;
                                $classes["PROPERTY_259_VALUE"] = 0;
                                $classes["PROPERTY_260_VALUE"] = 0;
                                $classes["PROPERTY_261_VALUE"] = 0;
                                $classes["PROPERTY_262_VALUE"] = 0;
                                $classes["PROPERTY_263_VALUE"] = 0;
                                $classes["PROPERTY_264_VALUE"] = 0;
                                $classes["PROPERTY_265_VALUE"] = 0;
                                $classes["PROPERTY_266_VALUE"] = 0;
                                $classes["PROPERTY_266_VALUE"] = 0;
                                $classes["PROPERTY_267_VALUE"] = 0;
                                $classes["PROPERTY_268_VALUE"] = 0;
                                $classes["PROPERTY_269_VALUE"] = 0;
                            }

                            $invBookInfo["PROPERTY_PUPIL_COUNT"] = $classes["PROPERTY_" . (258 + $invBookInfo["PROPERTY_CLASS"]) . "_VALUE"];
                            if ($invBookInfo["PROPERTY_PUPIL_COUNT"] != 0)
                                $invBookInfo["PROPERTY_KOEF"] = round(($invBookInfo["PROPERTY_COUNT"] - $invBookInfo["PROPERTY_BOOK_WRITEOFF"])
                                    / $invBookInfo["PROPERTY_PUPIL_COUNT"], 2);
                            else $invBookInfo["PROPERTY_KOEF"] = 0;
                            $invBookInfo["PROPERTY_BOOK_NEED"] = $invBookInfo["PROPERTY_PUPIL_COUNT"] - ($invBookInfo["PROPERTY_COUNT"] - $invBookInfo["PROPERTY_BOOK_WRITEOFF"]);
                            $invBookInfo["PROPERTY_BOOK_NEED"] = $invBookInfo["PROPERTY_BOOK_NEED"] < 0 ? 0 : $invBookInfo["PROPERTY_BOOK_NEED"];
                            $invBookInfo["PROPERTY_COST"] = round($invBookInfo["PROPERTY_BOOK_NEED"] * $invBookInfo["PROPERTY_BOOK_PRICE"], 2);
                            $invBookInfo["PROPERTY_COST_10"] = round($invBookInfo["PROPERTY_COST"] * 1.1, 2);
                            $invBookInfo["PROPERTY_USE_ORDERS"] = "Инвентаризация";

                            $data[] = $invBookInfo;
                        }
                    }

                    if (in_array("USE_ORDERS", $fieldIDs)) {
                        $arFilter = Array(
                            "IBLOCK_ID" => 9,
                            "PROPERTY_SCHOOL_ID" => $schoolId,
                            "PROPERTY_STATUS" => "osclosed"
                        );

                        $arSelectedFields9 = Array();
                        $arSelectedFields9[] = "PROPERTY_BOOK";

                        array_push($arSelectedFields9, "PROPERTY_COUNT");
                        array_push($arSelectedFields9, "PROPERTY_PRICE");
                        array_push($arSelectedFields9, "PROPERTY_IZD_ID");

                        $orderBooks = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields9);

                        while ($orderBook = $orderBooks->Fetch()) {

                            $arFilter = Array(
                                "IBLOCK_ID" => 5,
                                "ID" => $orderBook["PROPERTY_BOOK_VALUE"]
                            );

                            $arSelectedFields5 = Array (
                                "NAME",
                                "PROPERTY_AUTHOR",
                                "PROPERTY_TITLE",
                                "PROPERTY_CLASS"
                            );

                            $orderBooksInfo = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields5);

                            while ($orderBookInfo = $orderBooksInfo->Fetch()) {
                                $orderBookInfo["PROPERTY_MUNICIPALITY"] = str_replace(".", "", $munName);
                                $orderBookInfo["PROPERTY_SCHOOL"] = get_school_name_by_id($schoolId);
                                $orderBookInfo["PROPERTY_COUNT"] = $orderBook["PROPERTY_COUNT_VALUE"];
                                $orderBookInfo["PROPERTY_IZD"] = get_izd_name($orderBook["PROPERTY_IZD_ID_VALUE"]);
                                $orderBookInfo["PROPERTY_SUBJECT"] = $orderBookInfo["PROPERTY_TITLE_VALUE"];
                                $orderBookInfo["PROPERTY_BOOK_COUNT"] = $orderBook["PROPERTY_COUNT_VALUE"];
                                $orderBookInfo["PROPERTY_BOOK_PRICE"] = $orderBook["PROPERTY_PRICE_VALUE"];
                                $orderBookInfo["PROPERTY_CLASS"] = $orderBookInfo["PROPERTY_CLASS_VALUE"];
                                $orderBookInfo["PROPERTY_BOOK_AUTHOR"] = $orderBookInfo["PROPERTY_AUTHOR_VALUE"];
                                $orderBookInfo["PROPERTY_BOOK_NAME"] = trim(str_replace($orderBookInfo["PROPERTY_BOOK_AUTHOR"], "", $orderBookInfo["NAME"]));

                                $arFilter = Array(
                                    "IBLOCK_ID" => 37,
                                    "NAME" => $schoolId
                                );

                                $arOrder = Array(
                                    "ID" => "DESC"
                                );

                                $arSelectedFields = Array(
                                    "PROPERTY_259",
                                    "PROPERTY_260",
                                    "PROPERTY_261",
                                    "PROPERTY_262",
                                    "PROPERTY_263",
                                    "PROPERTY_264",
                                    "PROPERTY_265",
                                    "PROPERTY_266",
                                    "PROPERTY_267",
                                    "PROPERTY_268",
                                    "PROPERTY_269",
                                );

                                $arNavStartParams = Array(
                                    "nTopCount" => 1
                                );

                                $classes = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavStartParams, $arSelectedFields)->Fetch();

                                $orderBookInfo["PROPERTY_PUPIL_COUNT"] = $classes["PROPERTY_" . (258 + $orderBookInfo["PROPERTY_CLASS"]) . "_VALUE"];
                                $orderBookInfo["PROPERTY_KOEF"] = round($orderBookInfo["PROPERTY_COUNT"] / $orderBookInfo["PROPERTY_PUPIL_COUNT"], 2);
                                $orderBookInfo["PROPERTY_BOOK_NEED"] = $orderBookInfo["PROPERTY_PUPIL_COUNT"] - $orderBookInfo["PROPERTY_COUNT"];
                                $orderBookInfo["PROPERTY_BOOK_NEED"] = $orderBookInfo["PROPERTY_BOOK_NEED"] < 0 ? 0 : $orderBookInfo["PROPERTY_BOOK_NEED"];
                                $orderBookInfo["PROPERTY_COST"] = round($orderBookInfo["PROPERTY_BOOK_NEED"] * $orderBookInfo["PROPERTY_BOOK_PRICE"], 2);
                                $orderBookInfo["PROPERTY_COST_10"] = round($orderBookInfo["PROPERTY_COST"] * 1.1, 2);
                                $orderBookInfo["PROPERTY_BOOK_WRITEOFF"] = 0;
                                $orderBookInfo["PROPERTY_USE_ORDERS"] = "Заказы";

                                preg_match_all('|\d+|', $orderBookInfo["PROPERTY_BOOK_NAME"], $matches);

                                $str = $orderBookInfo["PROPERTY_SUBJECT"];
                                $str2 = "";

                                for ($i = 0; $i < strlen($str); $i++) {
                                    if (!intval($str[$i]))
                                        $str2 .= $str[$i];
                                    else
                                        break;
                                }

                                $orderBookInfo["PROPERTY_SUBJECT"] = $str2;

                                if (strpos($orderBookInfo["PROPERTY_SUBJECT"], "("))
                                    $orderBookInfo["PROPERTY_SUBJECT"] = trim(stristr($orderBookInfo["PROPERTY_SUBJECT"], "(", true));

                                $data[] = $orderBookInfo;
                            }
                        }
                    }
                }
            }

            echo json_encode($data);
            die();
            break;
        case "rpUMK":
            $iblockId = 0;
            break;
        case "rpEmpty":
            $iblockId = 0;
            break;
    }
    echo json_encode($data2);
}