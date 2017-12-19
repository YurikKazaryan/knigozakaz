<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 22.08.17
 * Time: 0:05
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$class = trim($_POST["CLASS"]);
$letter = trim($_POST["LETTER"]);
$pupilCount = trim($_POST["PUPILCOUNT"]);

if (CModule::IncludeModule("iblock")) {
    if (!isset($_POST["MODE"])) {
        $findStr = $class . $letter;

        $arFilter = array(
            "IBLOCK_ID" => 37,
            "NAME" => get_schoolID($USER->GetID()),
            "PROPERTY_272" => "%" . $findStr . "%"
        );

        $arSelectedFields = array(
            "PROPERTY_272"
        );

        $rowCount = CIBlockElement::GetList(false, $arFilter, false, false, $arSelectedFields)->SelectedRowsCount();

        if ($rowCount == 0) {
            $arNew = array(
                "MODIFIED_BY" => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => 37,
                "NAME" => get_schoolID($USER->GetID()),
                "ACTIVE" => "Y",
                "PROPERTY_VALUES" => array(
                    "PUPIL_COUNT_INFO" => $class . $letter . ":" . $pupilCount
                )
            );

            $el = new CIBlockElement();
            $newID = $el->Add($arNew);

            if ($newID)
                print("OK");
            else
                print("NO");
        } else {
            print("EXIST");
        }
    } else {
        $id = $_POST["ID"];

        CIBlockElement::SetPropertyValuesEx($id, 37, array("PUPIL_COUNT_INFO" => $class . $letter . ":" . $pupilCount));

        echo "UP";
    }
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");