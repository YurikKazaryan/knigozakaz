<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<div class="inventory-add">

	<div class="row"><div class="col-xs-12 add-title">Добавление учебника в инвентаризацию</div></div>

	<div class="panel panel-info">
		<div class="panel-heading">Поиск учебника в каталоге</div>
		<div class="panel-body">
			<div class="add-instruction">
				Для поиска укажите (полностью или частично) фамилию автора или название учебника.<br>Регистр букв не имеет значения.
			</div>
			<input class="form-control add-control" type="text" id="findStr" placeholder="Образец для поиска">
			<div class="row">
				<div class="col-xs-12 add-find-button">
					<button class="btn btn-md btn-primary add-control" type="button" onClick="add_find(<?=$arResult['MAX_RESULT']?>)" id="addFindButton">Найти</button>
					<button class="btn btn-md btn-default add-control" type="button" onClick="location.href='<?=$arResult['USE_PATH']?>'">Отменить</button>
				</div>
			</div>
		</div>
	</div>

	<div class="panel panel-info" id="addFindResult" hidden>
		<div class="panel-heading">Результат поиска</div>
		<div class="panel-body add-find-res-body">

			<div class="row add-find-res" hidden id="addFindRes1"><div class="col-xs-12">
				По указанному образцу найдено книг: <span id="addFindCount"></span>.<br>
			</div></div>

			<div class="row add-find-res" hidden id="addFindRes2"><div class="col-xs-12">
				По указанному образцу ничего не найдено...
			</div></div>

			<div class="row add-find-res" hidden id="addFindRes3"><div class="col-xs-12">
				По указанному образцу найдено слишком много совпадений. Уточните образец для поиска.
			</div></div>

			<div class="row add-find-res" hidden id="addFindRes4"><div class="col-xs-12">
				Для продолжения работы нажмите кнопку «Выбрать» напротив нужного учебника, задайте другой образец поиска, или нажмите кнопку «Добавить учебник», <b>если нужного учебника нет в каталоге</b>.
			</div></div>
			<div class="row"><div class="col-xs-12" id="addFindResultBody"></div></div>
		</div>
	</div>

	<div class="row"><div class="col-xs-12 text-right">
		<button type="button" class="btn btn-primary btn-sm" onClick="addNewBookShow()">Добавить учебник</button>
	</div></div>

	<div class="panel panel-info" id="addNewBook" hidden style="margin-top:10px;">
		<div class="panel-heading">Добавление учебника в каталог</div>
		<div class="panel-body">
			<form class="form">

				<div class="form-group">
					<label>Авторы</label>
					<input class="form-control new-book-field" name="NEW_BOOK_AUTHOR" id="newBookAuthor">
				</div>

				<div class="form-group">
					<label>Название (*)</label>
					<input class="form-control new-book-field" name="NEW_BOOK_TITLE" id="newBookTitle">
					<div class="alert alert-danger" hidden id="newBookTitleAlert">
						Название учебника не может быть менее 10 символов!
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>Издательство (*)</label>
							<select class="form-control" name="NEW_BOOK_IZD" id="newBookIzd">
								<option value="0">- Не выбрано -</option>
								<?foreach ($arResult['IZD_LIST'] as $key => $value):?>
									<option value="<?=$key?>"><?=$value?></option>
								<?endforeach;?>
							</select>
							<div class="alert alert-danger" hidden id="newBookIzdAlert">
								Выберите издательство!
							</div>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<label>Код ФП</label>
							<input class="form-control new-book-field" name="NEW_BOOK_FP_CODE" id="newBookFPCode">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>Год издания (*)</label>
							<input class="form-control new-book-field" name="NEW_BOOK_YEAR" id="newBookYear">
							<div class="alert alert-danger" hidden id="newBookYearAlert">
								Нужно указать 4-значный год приобретения!
							</div>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<label>Класс (*)</label>
							<input class="form-control new-book-field" name="NEW_BOOK_CLASS" id="newBookClass">
							<div class="alert alert-danger" hidden id="newBookClassAlert">
								Укажите класс!
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>Линия УМК</label>
							<input class="form-control new-book-field" name="NEW_BOOK_UMK" id="newBookUmk">
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group">
							<label>Система</label>
							<input class="form-control new-book-field" name="NEW_BOOK_SYSTEM" id="newBookSystem">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label>Электронная форма учебника</label>
							<select name="EFU" id="efu">
								<option value="N">Нет</option>
								<option value="Y">Да</option>
							</select>
						</div>
					</div>
				</div>

				<div class="row"><div class="col-xs-12 text-right">
					<button type="button" class="btn btn-primary" disabled id="newBookButton" onClick="add_new_book()">Добавить</button>
					<button type="button" class="btn btn-default" onClick="cancel_new_book()">Отменить</button>
				</div></div>


			</form>
		</div>
	</div>

	<?// Окно добавления учебника в инвентаризацию ?>
	<div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form-inline" method="POST" action="<?=$arResult['USE_PATH']?>">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addBookModalLabel">Добавление учебника в инвентаризацию</h4>
					</div>
					<div class="modal-body">
						<div class="row"><div class="col-xs-12" id="addBookModalTitle"></div></div><hr>
						<div class="form-group form-group-first">
							<label>Год приобретения&nbsp;</label>
							<input class="form-control" name="YEAR_PURCHASE1" id="yearPurchase">
							<div class="alert alert-danger" hidden id="yearPurchaseAlert">
								Нужно указать 4-значный год приобретения! Например: 2010
							</div>
						</div>
						<div class="form-group">
							<label>Количество&nbsp;</label>
							<input class="form-control" name="COUNT1" id="countPurchase">
							<div class="alert alert-danger" hidden id="countPurchaseAlert">
								Количество должно быть больше 0!
							</div>
						</div>
                        <br />
                        <div class="form-group">
                            <label>Используется в классах:</label>
                            <div id="checkboxGroup"></div>
                            <div class="alert alert-danger" hidden id="classuseAlert">
                                Укажите в каком классе используется учебник!
                            </div>
                        </div>
						<br><label>Примечания</label><br>
						<textarea class="form-control" style="width:100%" name="REMARKS"></textarea>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="BOOK_ID" value="" id="bookIDPurchase">
						<input type="hidden" name="BOOK_YC_COUNT" value="1" id="bookIDPurchase_YC">
						<button type="submit" class="btn btn-primary" id="addButtonPurchase" disabled name="BUTTON" value="SAVE">Добавить</button>
						<button type="button" class="btn btn-primary" id="addButtonNewYearCount" name="ADD" value="ADDYC">Добавить год / количесвто</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
					</div>
				</form>
			</div>
		</div>
	</div>

</div>