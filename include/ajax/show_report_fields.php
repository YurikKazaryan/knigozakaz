<?php

/**
 * Created by PhpStorm.
 * User: revil
 * Date: 07.09.17
 * Time: 22:15
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

if (CModule::IncludeModule("iblock")) {
    $arFilter = Array (
        "CHECK_PERMISSIONS" => "N"
    );

    switch ($_POST["rpType"]) {
        case "rpOrders":
            /*$propertiesArray = Array("_10", "_232", "_23", "_15", "_19", "_22", "_74");
            $properties = CIBlock::GetProperties(5, Array("NAME" => "ASC"), $arFilter);
            while ($property = $properties->Fetch())
                if (in_array("_" . $property["ID"], $propertiesArray))
                    $data["_" . $property["ID"]] = "Учебник: " . $property["NAME"];
            $data["_232"] = "Учебник: Название";*/

            $data["_10"] = "Учебник: Автор";
            $data["_232"] = "Учебник: Название";
            $data["_23"] = "Учебник: Класс";
            $data["_76"] = "Учебник: Издательство";
            $data["_15"] = "Учебник: Код ФП";
            $data["_19"] = "Учебник: Линия УМК";
            $data["_22"] = "Учебник: Система";
            $data["_19"] = "Учебник: Стандарт";

            $data["_25"] = "Заказ: Школа";
            $data["_24"] = "Заказ: Статус заказа";
            $data["_33"] = "Заказ: Цена";
            $data["_34"] = "Заказ: Количество";
            $data["_SZ"] = "Заказ: Сумма заказа";
            $data["_250"] = "Заказ: Район";
            $data["_257"] = "Заказ: НДС";

            /*$propertiesArray = Array("_24", "_25", "_33", "_34", "_76", "_250", "_257");
            $properties = CIBlock::GetProperties(9, Array("NAME" => "ASC"), $arFilter);
            while ($property = $properties->Fetch())
                if (in_array("_" . $property["ID"], $propertiesArray))
                    $data["_" . $property["ID"]] = "Заказ: " . $property["NAME"];
            $data["_24"] = "Заказ: Статус заказа";
            $data["_250"] = "Заказ: Район";*/
            //$data["_76"] = "Учебник: Издательство";

            //$data[0] = "Список полей для заказов";
            break;
        case "rpInventory":
            $propertiesArray = Array(174, 175, 177, 178, 271);
            $properties = CIBlock::GetProperties(25, Array("NAME" => "ASC"), $arFilter);
            while ($property = $properties->Fetch())
                if (in_array($property["ID"], $propertiesArray))
                    $data[$property["ID"]] = "Инвентаризация: " . $property["NAME"];
            $data[178] = "Инвентаризация: Количество приобретённых фондов";
            $data[174] = "Инвентаризация: Район";
            $data[175] = "Инвентаризация: Школа";

            $propertiesArray = Array(166, 167, 168, 169, 170, 171, 172, 173, 185, 270);
            $properties = CIBlock::GetProperties(24, Array("NAME" => "ASC"), $arFilter);
            while ($property = $properties->Fetch())
                if (in_array($property["ID"], $propertiesArray))
                    $data[$property["ID"]] = "Учебник: " . $property["NAME"];

            break;
        case "rpSvod":
            $data["MUNICIPALITY"] = "Район";
            $data["SCHOOL"] = "Образовательная организация";
            $data["IZD"] = "Издательство";
            $data["BOOK_AUTHOR"] = "Автор учебника";
            $data["BOOK_NAME"] = "Название учебника";
            $data["CLASS"] = "Класс";
            $data["SUBJECT"] = "Предмет";
            $data["BOOK_PRICE"] = "Цена учебника";
            $data["BOOK_COUNT"] = "Наличие учебников в шк. библиотеке";
            $data["PUPIL_COUNT"] = "Количество учеников, исп. учебник";
            $data["BOOK_WRITEOFF"] = "Количество списанных фондов";
            $data["KOEF"] = "Коэфициенты книгообеспеченности";
            $data["BOOK_NEED"] = "Потребность в фондах на след. уч. год";
            $data["COST"] = "Сумма, необходимая на закупку";
            $data["COST_10"] = "Сумма, необходимая на закупку (с учетом удорожания)";
            $data["USE_ORDERS"] = "Добавить в отчёт заказы";
            break;
        case "rpUMK":
            $propertiesArray = Array(10, 11, 15, 19, 22, 23, 74, 232);
            $properties = CIBlock::GetProperties(5, Array("NAME" => "ASC"), $arFilter);
            while ($property = $properties->Fetch())
                if (in_array($property["ID"], $propertiesArray))
                    $data[$property["ID"]] = "Учебник: " . $property["NAME"];
            $data[232] = "Учебник: Название";
            break;
        case "rpEmpty":
            $iblockId = 0;
            break;
    }
    echo json_encode($data);
}