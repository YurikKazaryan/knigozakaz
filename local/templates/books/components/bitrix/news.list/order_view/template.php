<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<div class="books-list">

	<h1>Просмотр заказа №<?=getOrderNum($_GET['order_id'])?></h1>
	<div class="basket-subtitle">(<?=$arResult['SCHOOL_NAME']?>, <?=$arResult['MUN_NAME']?>)</div>

	<div class="row">
		<div class="col-md-12">

			<?if (count($arResult['ITEMS']) > 0):?>

				<?if ($arResult['back_url']):?>
					<div class="text-right">
						<button type="button" class="btn btn-success btn-sm" onClick="window.open('/include/PHPExcel_ajax/download_order.php?ORDER_ID=<?=$_GET['order_id']?>')"><span class="glyphicon glyphicon-download-alt"></span> &nbsp;Скачать в формате Excel</button>
						<a href="<?=$arResult['back_url']?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-ok"></span> &nbsp;Закрыть</a>
					</div>
				<?endif;?>

				<?foreach($arResult["BASKET"] as $arIzdat):?>

					<h4>Издательство <?=$arIzdat['NAME']?></h4>

					<table class="table table-striped">

						<thead>
							<tr>
								<th>Учебник</th>
								<th class="col-price">Цена</th>
								<th class="col-count">Кол-во</th>
								<th class="col-summa">Сумма</th>
							</tr>
						</thead>

						<tbody>

							<?foreach($arIzdat["BOOKS"] as $arBook):?>
								<tr>
									<td>
										<span class="book-name">
											<?if ($arBook['AUTHOR']):?>
												<?=$arBook['AUTHOR']?><br>
											<?endif;?>
											<?=$arBook['TITLE']?>
										</span><br>
										<span class="book-info">
											<?if ($arBook['CLASS']):?>Класс: <?=$arBook['CLASS']?><?endif;?>
											<?if ($arBook['FP_CODE']):?>&nbsp;&nbsp;ФП: <?=$arBook['FP_CODE']?><?endif;?>
											<?if ($arBook['CODE_1C']):?>&nbsp;&nbsp;Код 1С: <?=$arBook['CODE_1C']?><?endif;?>
										</span>
									</td>
									<td class="text-center"><?=$arBook['PRICE']?></td>
									<td class="text-center"><?=$arBook['PROPERTIES']['COUNT']['VALUE']?></td>
									<td class="text-right"><?=$arBook['SUM']?></td>
								</tr>
							<?endforeach;?>

							<tfoot>
								<th colspan="2" class="text-right">ИТОГО:</th>
								<th class="text-center"><?=$arIzdat['COUNT']?></th>
								<th class="text-right"><?=$arIzdat['SUM']?></th>
								<th>&nbsp;</th>
							</tfoot>

						</tbody>

					</table>

				<?endforeach;?>

				<div class="text-right">
					<button type="button" class="btn btn-success btn-sm" onClick="window.open('/include/PHPExcel_ajax/download_order.php?ORDER_ID=<?=$_GET['order_id']?>')"><span class="glyphicon glyphicon-download-alt"></span> &nbsp;Скачать в формате Excel</button>
					<a href="/orders/<?=$arResult['BACK_URL']?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-ok"></span> &nbsp;Закрыть</a>
				</div>

			<?else:?>
				<div class="panel panel-info">
					<div class="panel-body">
						<h4>По данному заказу нет информации.</h4>
					</div>
				</div>
			<?endif;?>

			<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
		</div>
	</div>
</div>