<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="books-list">

	<h1><?=$arResult['IZD_NAME']?></h1>

	<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><?endif;?>

	<?foreach($arResult["ITEMS"] as $arItem):?>
		<div class="row row-book">
			<div class="col-xs-10 book-info" onClick="showBookInfo(<?=$arItem['ID']?>)" title="Показать подробную информацию">
				<div class="row"><div class="col-xs-12 book-name">
					<?=$arItem['PROPERTIES']['TITLE']['VALUE']?>
				</div></div>
				<?if ($arItem['PROPERTIES']['AUTHOR']['VALUE']):?>
					<div class="row"><div class="col-xs-12 book-info">
						<b>Автор:</b> <?=$arItem['PROPERTIES']['AUTHOR']['VALUE']?>
					</div></div>
				<?endif;?>
				<div class="row"><div class="col-xs-12 book-info">
					<?if ($arItem['PROPERTIES']['CLASS']['VALUE']):?><b>Класс:</b> <?=$arItem['PROPERTIES']['CLASS']['VALUE']?>&nbsp;&nbsp;<?endif;?>
					<?if ($arItem['PROPERTIES']['YEAR']['VALUE']):?><b>Год изд.:</b> <?=$arItem['PROPERTIES']['YEAR']['VALUE']?>&nbsp;&nbsp;<?endif;?>
					<?if ($arItem['PROPERTIES']['FP_CODE']['VALUE'] && strtoupper($arItem['PROPERTIES']['FP_CODE']['VALUE']) != 'НЕТ'):?><b>Код по ФП:</b> <?=$arItem['PROPERTIES']['FP_CODE']['VALUE']?><?endif;?>
				</div></div>
				<?if (!$arParams['PARENT_SECTION']):?>
					<div class="row"><div class="col-xs-12 book-info">
						<b>Издательство:</b> <?=$arItem['IZD_NAME']?>
					</div></div>
				<?endif;?>
			</div>
			<div class="col-xs-2 text-center">
				<?if ($arResult['SCHOOL_ADMIN']):?>

					<?if ($arResult['REPORT_MODE']):?>

						<div class="report_button_block">

							<?if (is_report_enabled()):?>
								<div id="del_report_<?=$arItem['ID']?>" <?if (!$arItem['REPORT_DELETE']):?>hidden<?endif;?>>
<!--										<b><?=$arItem['PRICE']?></b> <br> -->
									<small>В отчёте<br><span id="count_report_<?=$arItem['ID']?>"><?=$arItem['REPORT_DELETE_COUNT']?></span><br></small>
									<a class="btn btn-danger btn-xs" href="javascript:del_from_cart(<?=$arItem['ID']?>, 'REPORT')" onClick="return window.confirm('Удалить из отчёта \'<?=$arItem['NAME']?>\' в количестве <?=$arItem['REPORT_DELETE_COUNT']?> на сумму <?=$arItem['REPORT_DELETE_SUM']?>?')">
										<span class="glyphicon glyphicon-stats"></span> Удалить
									</a>
								</div>

								<div id="add_report_<?=$arItem['ID']?>" <?if ($arItem['REPORT_DELETE']):?>hidden<?endif;?>>
<!--										<b><?=$arItem['PRICE']?></b> -->
									<div class="books-counter"><input type="text" value="1" size="3" id="report_counter_<?=$arItem['ID']?>"></div>
									<a class="btn btn-primary btn-xs" href="javascript:add_to_cart(<?=$arItem['ID']?>, 'REPORT')"><span class="glyphicon glyphicon-stats"></span> В отчёт</a>
								</div>
							<?else:?>
									<b><?=$arItem['PRICE']?></b>
							<?endif;?>

						</div>

					<?else:?>

						<div class="order_button_block">
							<div id="del_book_<?=$arItem['ID']?>" <?if (!$arItem['DELETE']):?>hidden<?endif;?>>
								<b><?=$arItem['PRICE']?></b>
								<small><br>В корзине<br><span id="count_book_<?=$arItem['ID']?>"><?=$arItem['DELETE_COUNT']?></span><br></small>
								<a class="btn btn-danger btn-xs" href="javascript:del_from_cart(<?=$arItem['ID']?>, 'CART')" onClick="return window.confirm('Удалить из корзины \'<?=$arItem['NAME']?>\' в количестве <?=$arItem['DELETE_COUNT']?> на сумму <?=$arItem['DELETE_SUM']?>?')">
									<span class="glyphicon glyphicon-shopping-cart"></span> Удалить
								</a>
							</div>
							<div id="add_book_<?=$arItem['ID']?>" <?if ($arItem['DELETE']):?>hidden<?endif;?>>
								<b><?=$arItem['PRICE']?></b>
								<div class="books-counter"><input type="text" value="1" size="3" id="book_counter_<?=$arItem['ID']?>"></div>
								<a class="btn btn-primary btn-xs" href="javascript:void(0)" onClick="add_to_cart(<?=$arItem['ID']?>, 'CART')"><span class="glyphicon glyphicon-shopping-cart"></span> В корзину</a>
							</div>
						</div>

					<?endif;?>

				<?else:?>

					<?if ($arItem['SHOW_PRICE']):?>
						<?=$arItem['PRICE']?>
					<?endif;?>

				<?endif;?>
			</div>
		</div>
	<?endforeach;?>

	<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><?=$arResult["NAV_STRING"]?><?endif;?>

	<?// ***** Модальные окна ***** ?>
		<div class="modal fade" id="book_show" tabindex="-1" role="dialog" aria-labelledby="book_showLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="book_showLabel">Подробная информация</h4>
					</div>
					<div class="modal-body" id="book_show_body"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
					</div>
				</div>
			</div>
		</div>
	<?// ***** Модальные окна ***** ?>

</div>
