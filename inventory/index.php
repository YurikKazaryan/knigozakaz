<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Инвентаризация");

if (!$USER->IsAuthorized()) LocalRedirect('/auth/');

if (in_array(1, $USER->GetUserGroupArray())) {
    refreshInventoryCatalog();
    ?>
    <div class="panel panel-warning">
        <div class="panel panel-heading">Сообщение системы</div>
        <div class="panel panel-body">Просматривать информацию о состоянии инвентаризации могут только администраторы школ!</div>
    </div>
    <?
} elseif (in_array(8, $USER->GetUserGroupArray())) {

    $schoolID = get_schoolID($USER->GetID());

    $arInventoryFilter = array(
        'PROPERTY_SCHOOL_ID' => $schoolID
    );

// Обрабатываем сохранение нового учебника
    if ($_POST['BUTTON'] == 'SAVE') {

        $bookID = intval($_POST['BOOK_ID']);
        if ($bookID) {
            for ($i = 1; $i <= intval($_POST['BOOK_YC_COUNT']); $i++) {

                $arTemp = getWorkPeriod();

                // Ищем инвентаризацию с таким ID у этой школы. Если есть - суммируем
                $res = CIBlockElement::GetList(
                    false,
                    array('IBLOCK_ID' => 25, 'PROPERTY_SCHOOL_ID' => $schoolID, 'PROPERTY_BOOK_ID' => $bookID,
                        'PROPERTY_YEAR_PURCHASE' =>  $_POST['YEAR_PURCHASE' . $i]),
                    false, false,
                    array('IBLOCK_ID', 'ID', 'PROPERTY_COUNT')
                );

                if ($arFields = $res->GetNext()) {    // Нашли такой учебник - добавляем количество и обновляем использование
                    CIBlockElement::SetPropertyValuesEx(
                        $arFields['ID'],
                        25,
                        array(
                            'COUNT' => intval($arFields['PROPERTY_COUNT_VALUE']) + intval($_POST['COUNT' . $i]),
                            'USE_IN_CLASS' => implode(",", $_POST["usedInClass"])
                            //'USE_' . $arTemp['ID'] => ($_POST['CURRENT_USE'] == 'Y' ? 'Y' : 'N'),
                            //'USE_NEXT' => ($_POST['NEXT_USE'] == 'Y' ? 'Y' : 'N')
                        )
                    );
                } else {    // Такого учебника нет - создаем
                    //print_r($_POST);

                    $arNew = Array(
                        'MODIFIED_BY' => $USER->GetID(),
                        'IBLOCK_SECTION_ID' => false,
                        'IBLOCK_ID' => 25,
                        'NAME' => $bookID,
                        'ACTIVE' => 'Y',
                        'PROPERTY_VALUES' => array(
                            'REGION_ID' => getUserRegion(),
                            'SCHOOL_ID' => $schoolID,
                            'BOOK_ID' => $bookID,
                            'YEAR_PURCHASE' => $_POST['YEAR_PURCHASE' . $i],
                            'COUNT' => intval($_POST['COUNT' . $i]),
                            'REM' => trim($_POST['REMARKS']),
                            'USE_IN_CLASS' => implode(",", $_POST["usedInClass"])
                            //'USE_' . $arTemp['ID'] => ($_POST['CURRENT_USE'] == 'Y' ? 'Y' : 'N'),
                            //'USE_NEXT' => ($_POST['NEXT_USE'] == 'Y' ? 'Y' : 'N')
                        )
                    );
                    $el = new CIBlockElement;
                    $newID = $el->Add($arNew);
                }
            }
        }
       // LocalRedirect('/inventory/');
    } elseif ($_POST['BUTTON'] == 'DEL') {
        $userSchool = get_schoolID($USER->GetID());
        $invSchool = getInvInfo($_POST['BOOK_ID'], 'SCHOOL_ID');
        if ($userSchool == $invSchool) {
            CIBlockElement::Delete($_POST['BOOK_ID']);
        }
        LocalRedirect('/inventory/');
    } // Сохранение изменений
    elseif ($_POST['BUTTON'] == 'EDIT_SAVE') {
        $arTemp = getWorkPeriod();
        $userSchool = get_schoolID($USER->GetID());
        $invSchool = getInvInfo($_POST['INV_ID'], 'SCHOOL_ID');
        if ($userSchool == $invSchool) {
            CIBlockElement::SetPropertyValuesEx(
                $_POST['INV_ID'],
                25,
                array(
                    'YEAR_PURCHASE' => $_POST['YEAR_PURCHASE'],
                    'COUNT' => $_POST['COUNT'],
                    'REM' => $_POST['REMARKS'],
                    'USE_IN_CLASS' => implode(',', $_POST['usedInClass'])
                    //'USE_NEXT' => $_POST['NEXT_USE'],
                    //'USE_' . $arTemp['ID'] => $_POST['CURRENT_USE']
                )
            );
        }
        LocalRedirect('/inventory/');
    }
    ?>

    <div class="row">
        <div class="col-xs-12 text-right" style="padding-bottom:10px">
            <button type="button" class="btn btn-primary" onClick="document.location.href='/inventory/add/'">Добавить
                учебник
            </button>
        </div>
    </div>

    <? $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "inventory",
        array(
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "ADD_SECTIONS_CHAIN" => "Y",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_ADDITIONAL" => "",
            "AJAX_OPTION_HISTORY" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "CACHE_FILTER" => "N",
            "CACHE_GROUPS" => "Y",
            "CACHE_TIME" => "36000000",
            "CACHE_TYPE" => "A",
            "CHECK_DATES" => "Y",
            "COMPONENT_TEMPLATE" => "inventory",
            "DETAIL_URL" => "",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "DISPLAY_DATE" => "Y",
            "DISPLAY_NAME" => "Y",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "FIELD_CODE" => array(
                0 => "",
                1 => "",
            ),
            "FILTER_NAME" => "arInventoryFilter",
            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
            "IBLOCK_ID" => "25",
            "IBLOCK_TYPE" => "inventory",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
            "INCLUDE_SUBSECTIONS" => "Y",
            "MESSAGE_404" => "",
            "NEWS_COUNT" => "20",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
            "PAGER_SHOW_ALL" => "N",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_TEMPLATE" => ".default",
            "PAGER_TITLE" => "Новости",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "PREVIEW_TRUNCATE_LEN" => "",
            "PROPERTY_CODE" => array(
                0 => "YEAR_PURCHASE",
                1 => "COUNT",
                2 => "REM",
                3 => "BOOK_ID.NAME",
                //3 => "USE_63890",
                //4 => "USE_NEXT",
                5 => "",
            ),
            "SET_BROWSER_TITLE" => "Y",
            "SET_LAST_MODIFIED" => "N",
            "SET_META_DESCRIPTION" => "Y",
            "SET_META_KEYWORDS" => "Y",
            "SET_STATUS_404" => "N",
            "SET_TITLE" => "Y",
            "SHOW_404" => "N",
            "SORT_BY1" => "NAME",
            "SORT_BY2" => "SORT",
            "SORT_ORDER1" => "DESC",
            "SORT_ORDER2" => "ASC"
        ),
        false
    );
} else { ?>
    <div class="panel panel-warning">
        <div class="panel panel-heading">Сообщение системы</div>
        <div class="panel panel-body">Просматривать информацию о состоянии инвентаризации могут только администраторы школ!</div>
    </div>
<?}?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>