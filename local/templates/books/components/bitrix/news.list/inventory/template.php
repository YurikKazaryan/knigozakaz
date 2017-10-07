<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="inv-list">

    <div class="panel panel-default filter-form">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon title">Название учебника:</div><input class="form-control" type="text" name="INV_FILTER_BOOKNAME"  value="">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon title">Автор:</div><input class="form-control" type="text" name="INV_FILTER_AUTHOR" value="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row filter-line">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon title">Издательство:</div><input class="form-control" type="text" name="INV_FILTER_IZD" value="">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon title">Класс:</div><input class="form-control" type="text" name="INV_FILTER_CLASS" value="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row filter-line">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon title">Предмет:</div><input class="form-control" type="text" name="INV_FILTER_SUBJECT" value="">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="form-group">
                        <button type="button" class="btn btn-sm btn-success" name="SET_FILTER" value="SET_FILTER"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span> Фильтр </button>
                        <button type="button" class="btn btn-sm btn-danger" name="SET_FILTER" value="DEL_FILTER"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Отмена</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<?if($arParams["DISPLAY_TOP_PAGER"]):?>
		<div class="row"><div class="col-xs-12"><?=$arResult["NAV_STRING"]?></div></div>
	<?endif;?>
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Данные об учебном пособии</th>
				<th width="60">Год<br>приобр.</th>
				<th width="60">Кол-во</th>
				<!--<th width="140">Использование</th>-->
				<th>Прим.</th>
				<th width="90">Опции</th>
			</tr>
		</thead>

		<tbody>
            <? foreach ($arResult["ITEMS"] as $arItem) {
                $props['YEAR_PURCHASE'] = $arItem['PROPERTIES']['YEAR_PURCHASE']['VALUE'];
                $props['COUNT'] = $arItem['PROPERTIES']['COUNT']['VALUE'];
                $props['EDIT_LINK'] = $arItem['EDIT_LINK'];
                $props['DELETE_LINK'] = $arItem['DELETE_LINK'];
                $props['ID'] = $arItem['ID'];
                $props['IBLOCK_ID'] = $arItem["IBLOCK_ID"];
                $props['REM'] = $arItem['PROPERTIES']['REM']['VALUE']['TEXT'];

                $tmp[$arItem['PROPERTIES']['BOOK_ID']['VALUE']][] = $props;
            };
			foreach($tmp as $book_id => $items):?>
                <tr class="news-item clickable" id="<?=$book_id?>" style="cursor: pointer">
                    <td class="text-left pointed" colspan="4">
                        <?=$arResult['BOOKS'][$book_id]['FULL_NAME']?>
                    </td>
                    <td class="text-center" style="vertical-align: middle" id="total_count<?=$book_id?>">
                    </td>
                </tr>
                <?foreach ($items as $id => $arItem):?>
				<?
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				?>
                    <tr class="hidden" id="show<?=$book_id?>">
                        <td>
                            &nbsp;
                        </td>
                        <td class="text-center">
                            <?=$arItem['YEAR_PURCHASE']?>
                        </td>
                        <td class="text-center">
                            <?=$arItem['COUNT']?>
                        </td>
                        <td>
                            <?if ($arItem['REM']):?>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="left" title="<?=$arItem['REM']?>">
                                    <span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
                                </button>
                            <?endif;?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" title="Редактировать учебник" onClick="edit_inv_book(<?=$arItem['ID']?>,<?=$book_id?>)"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                            <button type="button" class="btn btn-sm btn-danger" title="Удалить учебник" onClick="del_inv_book(<?=$arItem['ID']?>,<?=$book_id?>)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
                        </td>
                    </tr>
                <?endforeach;?>
			<?endforeach;?>
		</tbody>
	</table>

	<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
		<div class="row"><div class="col-xs-12"><?=$arResult["NAV_STRING"]?></div></div>
	<?endif;?>

	<?//******* Модальное окно подтверждения удаления книги из инвентаризации *******?>
	<div class="modal fade" id="delInvBookModal" tabindex="-1" role="dialog" aria-labelledby="delInvBookModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="delInvBookModalLabel">Подтверждение удаления учебника</h4>
				</div>
				<div class="modal-body">
					<div style="text-align:center; font-weight:bold; padding-bottom:10px;">Подтвердите удаление из инвентаризации учебника</div>
					<div id="delInvBookTitle"></div>
				</div>
				<div class="modal-footer">
					<form method="POST" action="/inventory/">
						<input type="hidden" name="BOOK_ID" id="delInvBookID" value="">
						<button type="submit" class="btn btn-danger" name="BUTTON" value="DEL">Удалить</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<?// Модальное окно редактирования учебника в инвентаризации ?>
	<div class="modal fade" id="editBookModal" tabindex="-1" role="dialog" aria-labelledby="editBookModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form-inline" method="POST" action="<?=$arResult['USE_PATH']?>">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="editBookModalLabel">Редактирование учебника в инвентаризации</h4>
					</div>
					<div class="modal-body">
						<div class="row"><div class="col-xs-12" id="editBookModalTitle"></div></div><hr>
						<div class="form-group form-group-first">
							<label>Год приобретения&nbsp;</label>
							<input class="form-control" name="YEAR_PURCHASE" id="editYearPurchase">
							<div class="alert alert-danger" hidden id="editYearPurchaseAlert">
								Нужно указать 4-значный год приобретения! Например: 2010
							</div>
						</div>
						<div class="form-group">
							<label>Количество&nbsp;</label>
							<input class="form-control" name="COUNT" id="editCountPurchase">
							<div class="alert alert-danger" hidden id="editCountPurchaseAlert">
								Количество должно быть больше 0!
							</div>
						</div>
						<!--<div class="form-group">
							<label><?=$arResult['CURRENT_PERIOD']?>: используется?&nbsp;</label>
							<select class="form-control" name="CURRENT_USE" id="editUseCurrPurchase">
								<option value="Y">Да</option>
								<option value="N" selected>Нет</option>
							</select>
						</div>
						<div class="form-group">
							<label>Будущий учебный год: планируется использование?&nbsp;</label>
							<select class="form-control" name="NEXT_USE" id="editUseNextPurchase">
								<option value="Y">Да</option>
								<option value="N" selected>Нет</option>
							</select>
						</div>-->
						<br><label>Примечания</label><br>
						<textarea class="form-control" style="width:100%" name="REMARKS" id="editRemPurchase"></textarea>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="BOOK_ID" value="" id="editBookIDPurchase">
						<input type="hidden" name="INV_ID" value="" id="editInvIDPurchase">
						<button type="submit" class="btn btn-primary" id="editAddButtonPurchase" disabled name="BUTTON" value="EDIT_SAVE">Сохранить</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
					</div>
				</form>
			</div>
		</div>
	</div>

</div>
