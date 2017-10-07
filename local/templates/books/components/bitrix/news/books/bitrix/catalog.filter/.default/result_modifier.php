<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	foreach ($arResult['ITEMS'] as $key => $value) {
		if ($value['NAME'] == 'Название') {
			$arResult['ITEMS'][$key]['NAME'] = 'Автор, название';
			$value['NAME'] = 'Введите часть названия или автора';
		}
		if ($value['HIDDEN'] != 1) $arResult['ITEMS'][$key]['INPUT'] =
			substr($value['INPUT'], 0, 6) .
			' class="form-control" placeholder="' . $value['NAME'] . '" ' .
			substr($value['INPUT'], 6);
	}

?>