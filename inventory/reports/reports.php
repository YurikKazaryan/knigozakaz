<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require($_SERVER["DOCUMENT_ROOT"] . "/include/PHPExcel/PHPExcel.php");

$APPLICATION->SetTitle("Инвентаризация - Отчеты");

if (!$USER->IsAuthorized()) LocalRedirect('/auth/');

//Get School list by user

if (in_array(7, $USER->GetUserGroupArray())
    || in_array(6, $USER->GetUserGroupArray())
    || in_array(9, $USER->GetUserGroupArray())
    || in_array(1, $USER->GetUserGroupArray())) {

    //echo "<pre>";

    $municipalityId = get_munID_list($USER->GetID());

    foreach ($municipalityId as $key => $munID) {

        $schools = get_schoolID_by_mun2($munID);

        $data = Array();
        $details = Array();
        $classes_region = Array();

//Количество учеников по классам в регионе
        foreach ($schools as $key => $schoolId) {
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

            $classes_region[1] += $classes["PROPERTY_259_VALUE"];
            $classes_region[2] += $classes["PROPERTY_260_VALUE"];
            $classes_region[3] += $classes["PROPERTY_261_VALUE"];
            $classes_region[4] += $classes["PROPERTY_262_VALUE"];
            $classes_region[5] += $classes["PROPERTY_263_VALUE"];
            $classes_region[6] += $classes["PROPERTY_264_VALUE"];
            $classes_region[7] += $classes["PROPERTY_265_VALUE"];
            $classes_region[8] += $classes["PROPERTY_266_VALUE"];
            $classes_region[9] += $classes["PROPERTY_267_VALUE"];
            $classes_region[10] += $classes["PROPERTY_268_VALUE"];
            $classes_region[11] += $classes["PROPERTY_269_VALUE"];
        }

        $details = Array();
//Данные книг в инвентаризации
        foreach ($schools as $key => $schoolId) {
            $arFilter = Array(
                "IBLOCK_ID" => 25,
                "PROPERTY_175" => $schoolId
            );

            $arSelectedFields = Array(
                "ID",
                "NAME",
            );

            $books = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelectedFields);

            while ($book = $books->Fetch())
                $details[] = $book["NAME"];
        }

//$details - ID книг в инвентаризации
        $details = array_unique($details);

        foreach ($details as $book_id) {
            $arFilter = Array(
                "IBLOCK_ID" => 25,
                "PROPERTY_176" => $book_id
            );

            $arSelectedFields = Array(
                "ID",
                "NAME",
                "PROPERTY_271",
                "PROPERTY_178"
            );

            $cnts = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelectedFields);

            $bdetails["COUNT"] = 0;
            $bdetails["DELETE"] = 0;

            while ($cnt = $cnts->Fetch()) {
                $bdetails["COUNT"] += $cnt["PROPERTY_178_VALUE"];
                $bdetails["DELETE"] += $cnt["PROPERTY_271_VALUE"];
            }

            $arFilter = Array(
                "IBLOCK_ID" => 24,
                "ID" => $book_id
            );

            $arSelectedFields = Array(
                "ID",
                "NAME",
                "PROPERTY_166",
                "PROPERTY_167",
                "PROPERTY_168",
                "PROPERTY_185",
                "PROPERTY_270"
            );

            $books_props = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelectedFields);

            while ($books_prop = $books_props->Fetch()) {
                $bdetails["CLASS"] = $books_prop["PROPERTY_168_VALUE"] ? $books_prop["PROPERTY_168_VALUE"] :
                    (int)substr($books_prop["NAME"], strpos($books_prop["NAME"], " кл") - 2, 3);
                $bdetails["CLASS"] = preg_replace("~[^0-9-]+~","", $bdetails["CLASS"]);
                $bdetails["PUPIL"] = 0;
                if (strpos($bdetails["CLASS"], "-")) {
                    $com_classes = explode("-", $bdetails["CLASS"]);
                    foreach ($com_classes as $cl)
                        $bdetails["PUPIL"] += $classes_region[intval($cl)];
                } else {
                    $bdetails["PUPIL"] = $classes_region[$bdetails["CLASS"]];
                }
                $bdetails["AUTHOR"] = preg_replace("/  +/", " ", $books_prop["PROPERTY_167_VALUE"]);
                $bdetails["PRICE"] = $books_prop["PROPERTY_270_VALUE"];
                //$details["CODE_FP"] = $books_prop["PROPERTY_166_VALUE"];

                $arFilter = Array(
                    "IBLOCK_ID" => 5,
                    "ID" => $books_prop["PROPERTY_185_VALUE"]
                );

                $arSelect = Array(
                    "NAME"
                );

                $izd_name = CIBlockSection::GetList(Array("SORT" => "ASC"), $arFilter, false, $arSelect, false)->Fetch();
                $bdetails["IZD_NAME"] = str_replace(array("\"", "«", "»"), '', $izd_name["NAME"]);
                $bdetails["BOOK_NAME"] = str_replace(array("\"", "«", "»"), '', $books_prop["NAME"]);
                $bdetails["BOOK_NAME"] = preg_replace("/  +/", " ", $bdetails["BOOK_NAME"]);
                $data[$book_id][] = $bdetails;
            }
        }

//Данные книг по заказам
        $books = Array();

        foreach ($schools as $key1 => $school_id) {
            $arFilter = Array(
                "IBLOCK_ID" => 9,
                "PROPERTY_25" => $school_id,
                "PROPERTY_24" => "osrecieved"
            );

            $arSelectedFields = Array(
                "ID",
                "PROPERTY_249"
            );

            $orders = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelectedFields);

            while ($order = $orders->Fetch())
                $books[] = $order["PROPERTY_249_VALUE"];
        }

        foreach ($books as $key1 => $book_id1) {
            $arFilter = Array(
                "IBLOCK_ID" => 5,
                "ID" => $book_id1,
            );

            $arSelectedFields = Array(
                "ID",
                "NAME",
                "PROPERTY_10",
                "PROPERTY_23",
                "PROPERTY_232"
            );

            $books_props = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelectedFields)->Fetch();

            $cdetails["CLASS"] = $books_props["PROPERTY_23_VALUE"];
            if (strpos($cdetails["CLASS"], "-")) {
                $com_classes = explode("-", $cdetails["CLASS"]);
                foreach ($com_classes as $cl)
                    $cdetails["PUPIL"] += $classes_region[intval($cl)];
            } else {
                $cdetails["PUPIL"] = $classes_region[$cdetails["CLASS"]];
            }
            $cdetails["AUTHOR"] = preg_replace("/  +/", " ", $books_props["PROPERTY_10_VALUE"]);
            $cdetails["BOOK_NAME"] = $books_props["PROPERTY_232_VALUE"];

            $arFilter = Array(
                "IBLOCK_ID" => 9,
                "PROPERTY_249" => $book_id1,
            );

            $arSelectedFields = Array(
                "ID",
                "NAME",
                "PROPERTY_23",
                "PROPERTY_34",
                "PROPERTY_33",
                "PROPERTY_76"
            );

            $books_props = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, false, $arSelectedFields)->Fetch();

            $cdetails["COUNT"] = $books_props["PROPERTY_34_VALUE"];
            $cdetails["PRICE"] = $books_props["PROPERTY_33_VALUE"];
            $cdetails["DELETE"] = 0;

            $arFilter = Array(
                "IBLOCK_ID" => 5,
                "ID" => $books_props["PROPERTY_76_VALUE"]
            );

            $arSelect = Array(
                "NAME"
            );

            $izd_name = CIBlockSection::GetList(Array("SORT" => "ASC"), $arFilter, false, $arSelect, false)->Fetch();
            $cdetails["IZD_NAME"] = str_replace(array("\"", "«", "»"), '', $izd_name["NAME"]);

            $data[$book_id1][] = $cdetails;
        }

        //print_r($data);

        foreach ($data as $book_id => $inv_info) {
            $pData[$munID][$inv_info[0]["IZD_NAME"]][] = $inv_info[0];
        }
    }
}

if ($pData) {
    $scheme_name = "./scheme/mscheme.xlsx";

    $reportFile = PHPExcel_IOFactory::load($scheme_name);
    $reportFile->setActiveSheetIndex(0);

    $startRow = 4;

    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, 2, date("d.m.Y"));

    $kk = 0;

    foreach ($pData as $MUN_ID => $SCHOOLS) {
        if ((count($SCHOOLS) == 1) && !in_array(1, $USER->GetUserGroupArray())) {
            $reportFile->getActiveSheet()->setCellValueByColumnAndRow(1, 2, get_obl_name($MUN_ID));
        } else {
            $reportFile->getActiveSheet()->setCellValueByColumnAndRow(1, 2, get_obl_name(getUserRegion()));
        }
        $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, get_obl_name($MUN_ID));
        $reportFile->getActiveSheet()->mergeCells("A" . $startRow . ":H" . $startRow);
        $reportFile->getActiveSheet()->getStyle("A" . $startRow)->getFont()->setBold(true);
        $startRow++;
        foreach ($SCHOOLS as $IZD_NAME => $BOOKS) {
            $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, $IZD_NAME);
            $reportFile->getActiveSheet()->mergeCells("A" . $startRow . ":H" . $startRow);
            $reportFile->getActiveSheet()->getStyle("A" . $startRow)->getFont()->setBold(true);
            $reportFile->getActiveSheet()->getStyle("A" . $startRow)->getFont()->setItalic(true);
            $startRow++;
            foreach ($BOOKS as $bookId => $theBook) {
                if ($theBook["AUTHOR"])
                    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, $theBook["AUTHOR"] .
                        ". " . $theBook["BOOK_NAME"]);
                else
                    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, $theBook["BOOK_NAME"]);
                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(1, $startRow, $theBook["PRICE"]);
                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(2, $startRow, $theBook["COUNT"]);
                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(3, $startRow, $theBook["PUPIL"]);
                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, $theBook["DELETE"]);

                if ($theBook["PUPIL"] != 0)
                    $k = round(($theBook["COUNT"] - $theBook["DELETE"]) / $theBook["PUPIL"], 2);
                else
                    $k = 0;

                $need = $theBook["PUPIL"] - ($theBook["COUNT"] - $theBook["DELETE"]);
                $need = $need < 0 ? 0 : $need;
                $cost = $k > 1 ? 0 : round($need * $theBook["PRICE"], 2);

                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(5, $startRow, $k);
                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(6, $startRow, $need);
                $reportFile->getActiveSheet()->setCellValueByColumnAndRow(7, $startRow, $cost);

                $startRow++;

                $t1 += $theBook["COUNT"];
                $t2 += $theBook["PUPIL"];
                $t3 += $theBook["DELETE"];
                $t4 += $k;
                $t5 += $need;
                $t6 += $cost;
                $kk++;
            }
        }
    }

    $startRow++;

    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(0, $startRow, "Всего: ");
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(2, $startRow, $t1);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(3, $startRow, $t2);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, $t3);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(5, $startRow, round($t4 / $kk, 2));
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(6, $startRow, $t5);
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(7, $startRow, $t6);

    $reportFile->getActiveSheet()->getStyle("A" . $startRow . ":H" . $startRow)->getFont()->setBold(true);

    $reportFile->getActiveSheet()->getStyle("A4:H" . $startRow)->getBorders()->getAllBorders()
        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

    $startRow += 5;

    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, "Средний коэффициент книгообеспеченности :");
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(8, $startRow, round($t4 / $kk, 2));
    $startRow++;

    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, "Всего потребность учебников (шт.) :");
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(8, $startRow, $t5);
    $startRow++;

    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, "Необходимое финансирование на закупку учебников (руб.):");
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(8, $startRow, $t6);
    $startRow++;

    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(4, $startRow, "С учётом среднего коэффициента удорожания 1,1 (10%) в год (руб.):");
    $reportFile->getActiveSheet()->setCellValueByColumnAndRow(8, $startRow, round($t6 * 1.1, 2));
    $startRow++;

    $objWriter = new PHPExcel_Writer_Excel2007($reportFile);
    $fileName = "./userreports/" . $USER->GetID() . ".xlsx";

    if (file_exists($fileName)) {
        unlink($fileName);
        $objWriter->save($fileName);
    } else
        $objWriter->save($fileName);
}
?>
<? if ($pData) {
    ?>
    <div class="col-xs-12">
        <a class="btn btn-success center-block" style="width: 200px" href="<?echo $fileName;?>">Анализ учебных фондов</a></li>
    </div>
<?} else {?>
    <div class="panel panel-warning">
        <div class="panel panel-heading">Сообщение системы</div>
        <div class="panel panel-body">
            Отсутствуют данные инвентаризации для вашего муниципалитета или Вы не имеете доступ к данному функционалу!
        </div>
    </div>
<?}?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>