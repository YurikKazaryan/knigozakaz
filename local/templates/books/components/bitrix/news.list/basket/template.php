<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<div class="books-list">

	<h1>Корзина</h1>

	<?if (!$arResult['IS_OPER']):?>
		<div class="basket-subtitle"><?=get_school_name_by_id($_GET['sch_id'])?></div>
	<?endif;?>

	<div class="row">
		<div class="col-md-12">

			<?if ($_GET['back']):?>
				<div class="text-right">
					<a href="<?=$_GET['back']?>" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Закрыть</a>
				</div>
			<?endif;?>

			<?if (count($arResult['ITEMS']) > 0):?>

				<form role="form" name="basket-form" action="/orders/basket/" method="POST">

					<?if ($arResult['IS_OPER']):?>
						<div class="text-right">
							<button type="submit" class="btn btn-primary btn-sm" name="btnAction" value="SAVE" id="btnSave1" disabled><span class="glyphicon glyphicon-ok"></span> Сохранить</button>
							<button type="cancel" class="btn btn-primary btn-sm" name="btnAction" value="CANCEL" id="btnCancel1" disabled><span class="glyphicon glyphicon-remove"></span> Отменить</button>
							<button type="submit" class="btn btn-danger btn-sm" name="btnAction" value="ORDER" id="btnOrder1" <?=$arResult['ORDER_DISABLED']?>><span class="glyphicon glyphicon-flag"></span> Сформировать заказ</button>
							<button type="button" class="btn btn-success btn-sm" id="btnDownload1" onClick="window.open('/include/PHPExcel_ajax/download_basket.php?ID=<?=$arResult['SCHOOL_ID'];?>');" <?=$arResult['ORDER_DISABLED']?>><span class="glyphicon glyphicon-download-alt"></span> Скачать в формате Excel</button>
						</div>
					<?endif;?>

					<?foreach($arResult["BASKET"] as $arIzdat):?>

						<hr><h4><?=$arIzdat['NAME']?></h4>

						<?if (!$arIzdat['CAN_ORDER']):?>
							<div class="alert alert-danger">
								<img src="<?=SITE_TEMPLATE_PATH.'/images/warning-48.png'?>" width="48" height="42" style="float:left;margin-right:5px;">
								<b>Внимание!</b> Предыдущий заказ по издательству <?=$arIzdat['NAME']?> еще находится в обработке.
								Для внесения изменений в существующий заказ <a href="/info/contacts/" target="_blank">свяжитесь с оператором</a>.
							</div>
						<?endif;?>

						<table class="table table-striped">

							<thead>
								<tr>
									<th>Учебник</th>
									<th class="col-price">Цена</th>
									<th class="col-count">Кол-во</th>
									<th class="col-summa">Сумма</th>
									<?if ($arResult['IS_OPER']):?>
										<th class="col-remove"><span class="glyphicon glyphicon-remove" aria-hidden="true" title="Отметить для удаления из корзины"></span> </th>
									<?endif;?>
								</tr>
							</thead>

							<tbody>

								<?foreach($arIzdat["BOOKS"] as $arBook):?>

									<tr>
										<td>
											<span class="book-name"><?=$arBook['AUTHOR']?><br><?=$arBook['TITLE']?></span><br>
											<span class="book-info">
												<?if ($arBook['CLASS']):?>Класс: <?=$arBook['CLASS']?><?endif;?>
												<?if ($arBook['FP_CODE']):?>&nbsp;&nbsp;ФП: <?=$arBook['FP_CODE']?><?endif;?>
											</span>
										</td>
										<td class="text-center"><?=$arBook['PRICE']?></td>
										<td class="text-center">
											<input type="text" class="form-control" name="COUNT[<?=$arBook['ID']?>][]" id="book_<?=$arBook['ID']?>" value="<?=$arBook['PROPERTIES']['COUNT']['VALUE']?>" onchange="javascript:save_enable();" <?if (!$arResult['IS_OPER']):?>disabled<?endif;?>>
										</td>
										<td class="text-right"><?=$arBook['SUM']?></td>
										<?if ($arResult['IS_OPER']):?>
											<td class="text-center"><input type="checkbox" name="REMOVE[]" value="<?=$arBook['ID']?>" title="Удалить из корзины" onchange="javascript:save_enable();"></td>
										<?endif;?>
									</tr>


								<?endforeach;?>

								<tfoot>
									<th colspan="2" class="text-right">ИТОГО:</th>
									<th class="text-center"><?=$arIzdat['COUNT']?></th>
									<th class="text-right"><?=$arIzdat['SUM']?></th>
									<th colspan="2">&nbsp;</th>
								</tfoot>

							</tbody>

						</table>


					<?endforeach;?>

					<div class="panel panel-info">
						<div class="panel-body">
							<h4>Итого в корзине учебников на сумму: <b><?=$arResult['BASKET_SUM']?></b></h4>
						</div>
					</div>

					<?if ($arResult['IS_OPER']):?>
						<div class="text-right">
							<button type="submit" class="btn btn-primary btn-sm" name="btnAction" value="SAVE" id="btnSave2" disabled><span class="glyphicon glyphicon-ok"></span> Сохранить</button>
							<button type="cancel" class="btn btn-primary btn-sm" name="btnAction" value="CANCEL" id="btnCancel2" disabled><span class="glyphicon glyphicon-remove"></span> Отменить</button>
							<button type="submit" class="btn btn-danger btn-sm" name="btnAction" value="ORDER" id="btnOrder2" <?=$arResult['ORDER_DISABLED']?>><span class="glyphicon glyphicon-flag"></span> Сформировать заказ</button>
							<button type="button" class="btn btn-success btn-sm" id="btnDownload2" onClick="window.open('/include/PHPExcel_ajax/download_basket.php?ID=<?=$arResult['SCHOOL_ID'];?>');" <?=$arResult['ORDER_DISABLED']?>><span class="glyphicon glyphicon-download-alt"></span> Скачать в формате Excel</button>
						</div>
					<?endif;?>

				</form>

				<?if ($_GET['back']):?>
					<div class="text-right">
						<a href="<?=$_GET['back']?>" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Закрыть</a>
					</div>
				<?endif;?>

			<?else:?>
				<div class="panel panel-info">
					<div class="panel-body">
						<h4>Ваша корзина пуста. Пройдите в <a href="/books/">каталог</a> и выберите нужные учебники.</h4>
					</div>
				</div>
			<?endif;?>

			<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
		</div>
	</div>
</div>