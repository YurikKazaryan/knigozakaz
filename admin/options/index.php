<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки АИС");
if (!is_user_in_group(6) && !$USER->IsAdmin()) LocalRedirect('/');

// Обработка параметров
if ($_POST['BTN'] == 'SAVE' && $_POST['MODE'] == 'FORM1') {
	$arOptions = getOptions();
	foreach ($arOptions as $key => $value) {
		CIBlockElement::SetPropertyValueCode($value['ID'], ($value['TYPE'] == 'ot_array' ? 'OPTION_ARRAY' : 'OPTION_ACTIVE'), $_POST[$key]);
	}
}

// Подготовка данных для формы
$arOptions = getOptions();
$arIzd = getIzdList();

?>
<div class="row"><div class="col-xs-12 section-title"><h1>Настройки АИС</h1></div></div>
<br>
<form class="form" action ="" method="POST">
	<div class="panel panel-default">
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-5">
					<div class="form-group">
						<label><?=$arOptions['SHOW_CAT_PRICE']['TITLE']?></label>
						<select class="form-control" name="SHOW_CAT_PRICE">
							<option value="Y" <?if ($arOptions['SHOW_CAT_PRICE']['VALUE']):?>selected<?endif;?>>Показывать</option>
							<option value="N" <?if (!$arOptions['SHOW_CAT_PRICE']['VALUE']):?>selected<?endif;?>>Скрывать</option>
						</select>
					</div>
					<div class="form-group">
						<label><?=$arOptions['SHOW_ORDER_PRICE']['TITLE']?></label>
						<select class="form-control" name="SHOW_ORDER_PRICE">
							<option value="Y" <?if ($arOptions['SHOW_ORDER_PRICE']['VALUE']):?>selected<?endif;?>>Показывать</option>
							<option value="N" <?if (!$arOptions['SHOW_ORDER_PRICE']['VALUE']):?>selected<?endif;?>>Скрывать</option>
						</select>
					</div>
					<div class="form-group">
						<label><?=$arOptions['SHOW_CATALOG_NOUSER']['TITLE']?></label>
						<select class="form-control" name="SHOW_CATALOG_NOUSER">
							<option value="Y" <?if ($arOptions['SHOW_CATALOG_NOUSER']['VALUE']):?>selected<?endif;?>>Показывать</option>
							<option value="N" <?if (!$arOptions['SHOW_CATALOG_NOUSER']['VALUE']):?>selected<?endif;?>>Скрывать</option>
						</select>
					</div>
				</div>
				<div class="col-xs-7">
					<label><?=$arOptions['SHOW_CAT_PRICE_EX']['TITLE']?></label>
					<select class="form-control" name="SHOW_CAT_PRICE_EX[]" multiple size="<?=count($arIzd)?>">
						<?foreach ($arIzd as $key => $value):?>
							<option value="<?=$key?>" <?if (in_array($key,$arOptions['SHOW_CAT_PRICE_EX']['VALUE'])):?>selected<?endif;?>><?=$value?></option>
						<?endforeach;?>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 text-right">
					<br>
					<input type="hidden" name="MODE" value="FORM1">
					<button type="submit" class="btn btn-primary" name="BTN" value="SAVE">Сохранить</button>
					<button type="button" class="btn btn-default" onClick="document.location.href='/admin/options/'">Отменить</button>
				</div>
			</div>
		</div>
	</div>
</form>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>