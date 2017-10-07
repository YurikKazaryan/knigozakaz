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

<div class="row books-list text-center">
	<div class="col-md-12">
		<h2>Просмотр отчёта № <?=$_GET['order_id']?><br>(<?=$arResult['SCHOOL_NAME']?>)</h2><br>
	</div>
</div>

<div class="row books-list">
	<div class="col-md-12">

		<?if (count($arResult['ITEMS']) > 0):?>

			<?if ($arResult['back_url']):?>
				<div class="text-right">
					<a href="<?=$arResult['back_url']?>" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Закрыть</a>
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
									<span class="book-name"><?=$arBook['PROPERTIES']['AUTHOR']['VALUE']?><br><?=html_entity_decode(html_entity_decode($arBook['NAME']))?></span>
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

			<?if ($arResult['back_url']):?>
				<div class="text-right">
					<a href="<?=$arResult['back_url']?>" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Закрыть</a>
				</div>
			<?endif;?>

		<?else:?>
			<div class="panel panel-info">
				<div class="panel-body">
					<h4>По данному отчёту нет информации.</h4>
				</div>
			</div>
		<?endif;?>

		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
	</div>
</div>