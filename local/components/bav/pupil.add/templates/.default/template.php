<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 21.08.17
 * Time: 11:15
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
?>
<div class="pupil">
    <div class="panel panel-info">
        <div class="panel-heading">Количество учеников по классам (РАЗДЕЛ В ДОРАБОТКЕ!)</div>
        <div class="panel-body">
            <table class="table table-striped table-hover" id="classTable">
                <thead>
                <tr>
                    <td>Класс</td>
                    <td>Количество учеников</td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <input type="button" class="btn btn-success" name="addPupil" value="Добавить данные" />
        </div>
    </div>
</div>

<div class="modal fade" id="addClassModal" tabindex="-1" role="dialog" aria-labelledby="addClassModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addBookModalLabel">Добавить класс</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Класс</label>
                        <input class="form-control" name="CLASS" id="classs">
                        <div class="alert alert-danger" hidden id="classAlert">
                            Укажите номер класса!
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Литера</label>
                        <input class="form-control" name="LETTER" id="letter">
                        <div class="alert alert-danger" hidden id="letterAlert">
                            Укажите литеру (одну!) класса!
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Количество учеников</label>
                        <input class="form-control" name="PUPILCOUNT" id="pupilCount">
                        <div class="alert alert-danger" hidden id="pupilCountAlert">
                            Укажите количество учеников в классе!
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <input type="button" class="btn btn-success" name="savePupil" value="Сохранить данные"/>
                <input type="button" class="btn btn-success" name="editPupil" hidden value="Сохранить данные"/>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addError" tabindex="-1" role="dialog" aria-labelledby="addErrorLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addBookModalLabel">Добавление информации об учениках</h4>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
            </form>
        </div>
    </div>
</div>