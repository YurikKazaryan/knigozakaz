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
$arIzd = getIzdList();
$arBooks = getBookListByParams(110, 0, 0);

$subjects = array(0 => "Все", 1 => "Алгебра", 2 => "Английский язык", 3 => "Астрономия", 4 => "Биология", 5 => "История",
    6 => "География", 7 => "Геометрия", 8 => "Естествознание", 9 => "Изобразительное искусство", 10 => "Информатика",
    11 => "Испанский язык", 12 => "Литература", 14 => "Литературное чтение", 15 => "Математика", 16 => "Мировая художественная культура",
    17 => "Музыка", 18 => "Немецкий язык", 19 => "Обществознание", 20 => "Окружающий мир", 21 => "Основы безопасности жизнедеятельности",
    22 => "Основы духовно-нравственной культуры", 23 => "Право", 24 => "Природоведение", 25 => "Родная литература",
    26 => "Родной язык", 27 => "Россия в мире", 28 => "Русский язык и литература", 29 => "Русский язык", 30 => "Технология",
    31 => "Физика", 32 => "Физическая культура", 33 => "Финский язык", 34 => "Французский язык", 35 => "Химия", 36 => "Черчение",
    37 => "Чтение", 38 => "Экология", 39 => "Экономика");
?>
<div class="build-step1">
    <div class="panel panel-info">
        <div class="panel-heading">
            Шаг 1. Выберите тип отчета
        </div>
        <div class="panel-body">
            <ol class="list-group">
                <? if (!in_array(8, $USER->GetUserGroupArray())) {?>
                    <li class="list-group-item" id="reportType" value="rpOrders" style="cursor: pointer">Отчёт по заказам (перечень)</li>
                    <li class="list-group-item" id="reportType" value="rpBudget" style="cursor: pointer">Отчёт по заказам (суммы)</li>
                    <!--<li class="list-group-item" id="reportType" value="rpInventory" style="cursor: pointer">Отчёт по инвентаризации</li>-->
                    <li class="list-group-item" id="reportType" value="rpSvod" style="cursor: pointer">Отчёт по книгообеспеченности</li>
                    <li class="list-group-item" id="reportType" value="rpUMK" style="cursor: pointer">Анализ УМК</li>
                    <li class="list-group-item" id="reportType" value="rpEmpty" style="cursor: pointer">Перечень "Пустых" школ</li>
                <?} else { ?>
                    <li class="list-group-item" id="reportType" value="rpSvod" style="cursor: pointer">Отчёт по книгообеспеченности</li>
                <?}?>
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
            <div class="form-group" id="typeEmpty" hidden>
                <label>Выберите тип отчета</label>
                <select class="form-control report-control" name="TYPE_EMPTY" id="type_empty">
                    <option value="TE_ZAKAZ">по заказам</option>
                    <option value="TE_INV">по инвентаризации</option>
                </select>
            </div>
            <div class="form-group" id="typeUMK" hidden>
                <label>Выберите издательство</label>
                <select class="form-control report-control" name="UMK_IZD" id="umk_izd">
                    <?foreach ( $arIzd as $izdId => $izdName) :?>
                        <option value="<?=$izdId?>"><?=$izdName?></option>
                    <?endforeach;?>
                </select>
                <label>Укажите класс</label>
                <input type="text" class="form-control report-control" name="UMK_CLASS" id="umk_class" value="0"/>
                <label>Выберите предмет</label>
                <select class="form-control report-control" name="UMK_SUBJ" id="umk_subj">
                    <?foreach ($subjects as $id => $subject) :?>
                        <option value="<?=$id?>"><?=$subject?></option>
                    <?endforeach;?>
                </select>
            </div>

            <div class="form-group" id="budgetIzd" hidden>
                <label>Выберите издательство</label>
                <select class="form-control report-control" name="BDG_IZD" id="bdg_izd">
                    <option value="*">Все</option>
                    <?foreach ( $arIzd as $izdId => $izdName) :?>
                        <option value="<?=$izdId?>"><?=htmlspecialchars($izdName)?></option>
                    <?endforeach;?>
                </select>
                <label>Сгруппировать</label>
                <select class="form-control report-control" name="BDG_GROUP" id="bdg_group">
                    <option value="1">По муниципальным образованиям</option>
                    <option value="2">По образовательным организациям</option>
                </select>
            </div>
        </div>
        <div class="center-block bx-help-center panel-footer">
            <input type="button" name="selectAll" class="btn btn-success" value="Выделить все" id="selectAllFields"/>
            <input type="button" name="step3" class="btn btn-primary" value="Сформировать"/>
        </div>
    </div>
</div>
<div class="build-step3 hide">
    <div class="panel panel-info">
        <div class="panel-heading">
            <span id="rpBudgetTitle"></span>
            <div class="form-group" id="umk_book_div" hidden>
                <label>Выберите учебник</label>
                <select class="form-control report-control" name="UMK_BOOK" id="umk_book">
                </select>
            </div>
        </div>
        <div class="panel-body">
            <table id="dataTable" class="table table-striped table-hover hide"></table>
            <table id="summTable" class="table table-striped table-hover hide"></table>
            <div id="jqGridPager" class="hide"></div>
            <div id="response" class="hide"></div>
        </div>
        <div class="panel-footer">
            <button id="export" class="btn btn-default hide">Экспорт в Excel</button>
        </div>
    </div>
</div>
<div class="modal fade" id="bookInfoModal" tabindex="-1" role="dialog" aria-labelledby="bookInfoModalLabel">
    <div class="modal-dialog modal-dialog1" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="bookInfoModalLabel">Подробная информация о заказах школы <span id="schoolName"></span></h4>
                </div>
                <div class="modal-body">
                    <table id="bookInfoTable" class="table table-striped table-hover"></table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="errorModalLabel">Ошибка в ответе от сервера</h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>