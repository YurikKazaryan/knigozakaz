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
$APPLICATION->SetTitle('Список для отчёта');
?>

<div class="row books-list text-center">
	<div class="col-md-12">
		<h2>
			Список для отчёта
			<?if (!$arResult['IS_OPER']):?>
				<br>(<?=get_school_name_by_id($_GET['sch_id'])?>)
			<?endif;?>
		</h2>
		<br>
	</div>
</div>

<div class="row books-list">
	<div class="col-md-12">

		<?if ($_GET['back']):?>
			<div class="text-right">
				<a href="<?=$_GET['back']?>" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Закрыть</a>
			</div>
		<?endif;?>

		<?if (count($arResult['ITEMS']) > 0):?>

			<form role="form" name="basket-form" action="/reports/basket/" method="POST">

				<?if ($arResult['IS_OPER']):?>
					<div class="text-right">
						<button type="submit" class="btn btn-primary" name="btnAction" value="SAVE" id="btnSave1" disabled><span class="glyphicon glyphicon-ok"></span> Сохранить</button>
						<button type="cancel" class="btn btn-primary" name="btnAction" value="CANCEL" id="btnCancel1" disabled><span class="glyphicon glyphicon-remove"></span> Отменить</button>
						<button type="submit" class="btn btn-danger" name="btnAction" value="REPORT" id="btnOrder1"><span class="glyphicon glyphicon-flag"></span> Передать в отчёт</button>
					</div>
				<?endif;?>


				<?foreach($arResult["BASKET"] as $arIzdat):?>

					<h4><?=$arIzdat['NAME']?></h4>

					<table class="table table-striped">

						<thead>
							<tr>
								<th>Учебник</th>
								<th class="col-price">Цена,<br>руб.</th>
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
										<span class="book-name"><?=$arBook['PROPERTIES']['AUTHOR']['VALUE']?><br><?=$arBook['NAME']?></span>
									</td>
									<td class="text-center">
										<input type="text" class="form-control" name="PRICE[<?=$arBook['ID']?>][]" id="book_price_<?=$arBook['ID']?>" value="<?=$arBook['PRICE']?>" onchange="javascript:save_enable();" <?if (!$arResult['IS_OPER']):?>disabled<?endif;?>>
									</td>
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
						<h4>Итого в отчёте учебников на сумму: <b><?=$arResult['BASKET_SUM']?></b></h4>
					</div>
				</div>

				<?if ($arResult['IS_OPER']):?>
					<div class="text-right">
						<button type="submit" class="btn btn-primary" name="btnAction" value="SAVE" id="btnSave2" disabled><span class="glyphicon glyphicon-ok"></span> Сохранить</button>
						<button type="cancel" class="btn btn-primary" name="btnAction" value="CANCEL" id="btnCancel2" disabled><span class="glyphicon glyphicon-remove"></span> Отменить</button>
						<button type="submit" class="btn btn-danger" name="btnAction" value="REPORT" id="btnOrder2"><span class="glyphicon glyphicon-flag"></span> Передать в отчет</button>
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
					<h4>Ваша отчёт пуст. Пройдите в <a href="/books/">каталог</a> и выберите нужные учебники.</h4>
				</div>
			</div>
		<?endif;?>

		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
	</div>
</div>
