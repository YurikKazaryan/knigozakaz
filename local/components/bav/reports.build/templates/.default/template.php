<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 07.09.17
 * Time: 20:55
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Конструктор отчётов");
$APPLICATION->AddHeadScript("/include/jqGrid/src/jquery.jqGrid.js");
$APPLICATION->AddHeadScript("/include/jqGrid/js/i18n/grid.locale-ru.js");
$APPLICATION->AddHeadScript("/include/jszip/jszip.min.js");
$APPLICATION->SetAdditionalCSS("/include/jqGrid/css/ui.jqgrid.css");
$APPLICATION->SetAdditionalCSS("/include/jquery-ui/jquery-ui.min.css");
$APPLICATION->AddHeadScript("/include/jquery-ui/jquery-ui.min.js");
$APPLICATION->SetAdditionalCSS("/include/jqGrid/css/ui.jqgrid-bootstrap.css");
$APPLICATION->SetAdditionalCSS("/include/jqGrid/css/ui.jqgrid-bootstrap-ui.css");

$arPeriod = getPeriodList();
$arWorkPeriod = getWorkPeriod();
?>
<div class="build-step1">
    <div class="panel panel-info">
        <div class="panel-heading">
            Шаг 1. Выберите тип отчета
        </div>
        <div class="panel-body">
            <ol class="list-group">
                <li class="list-group-item" id="reportType" value="rpOrders" style="cursor: pointer">Отчёт по заказам</li>
                <li class="list-group-item" id="reportType" value="rpInventory" style="cursor: pointer">Отчёт по инвентаризации</li>
                <li class="list-group-item" id="reportType" value="rpSvod" style="cursor: pointer">Отчёт по книгообеспеченности</li>
                <li class="list-group-item" id="reportType" value="rpUMK" style="cursor: pointer">Анализ УМК</li>
                <li class="list-group-item" id="reportType" value="rpEmpty" style="cursor: pointer">Перечень "Пустых" школ</li>
            </ol>
        </div>
    </div>
</div>
<div class="build-step2 hidden">
    <input type="hidden" name="rpType" value="" />
    <div class="panel panel-info">
        <div class="panel-heading">
            Шаг 2. Выберите поля для отображения
        </div>
        <div class="panel-body">
            <div class="form-group" id="workPeriod" hidden>
                <label>Отчётный период</label>
                <select class="form-control report-control" name="PERIOD_EMPTY" id="period_empty">
                    <?foreach ($arPeriod as $key => $value):?>
                        <option value="<?=$key?>" <?if ($key == $arWorkPeriod['ID']):?>selected<?endif;?>><?=$value['NAME']?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>
        <div class="center-block">
            <input type="button" name="selectAll" class="btn btn-success" value="Выделить все" id="selectAllFields"/>
            <input type="button" name="step3" class="btn btn-primary" value="Далее"/>
        </div>
    </div>
</div>
<div class="build-step3 hidden">
    <table id="dataTable"></table>
    <div id="jqGridPager"></div>
    <button id="export" class="btn btn-default">Экспорт в Excel</button>
    <div id="response"></div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>