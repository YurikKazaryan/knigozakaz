<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 27.08.17
 * Time: 20:30
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
?>
<div class="writeoff">
    <div class="panel panel-info">
        <div class="panel panel-heading">Списание учебных фондов</div>
        <div class="panel panel-body" id="book-data">
            <form id="book_ids">
                <table class="table table-bordered table-responsive table-stripped">
                    <thead>
                    <tr>
                        <td>Данные об учебном пособии</td>
                        <td>Количество</td>
                        <td>Списание</td>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </form>
            <br>
            <input type="button" id="saveWriteOff" class="btn btn-primary" value="Сохранить" onclick="saveWriteOff()"/>
        </div>
    </div>
</div>
