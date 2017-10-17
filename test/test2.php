<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?

if (0 && $USER->IsAdmin()) {

	// Перекодировка учебников в заказах Вентаны (старый => новый)
	$arRecode = array(
		4278 => 4478,
		4277 => 4481,
		4336 => 4540,
		2486 => 5179,
		2166 => 4483,
		3212 => 4493,
		4319 => 4496,
		3224 => 4552,
		3470 => 5890,
		3506 => 5756,
		3514 => 5891,
		4324 => 4514,
		2171 => 4516,
		2490 => 702,
		2301 => 4623,
		2302 => 4624,
		3036 => 5638,
		3400 => 5042,
		3401 => 5639,
		4292 => 5969,
		2926 => 4589,
		2479 => 4598,
		2339 => 5211,
		2701 => 4619,
		2329 => 4613,
		2534 => 4621,
		3053 => 4446,
		2438 => 4447,
		2214 => 5992,
		3043 => 4341,
		2776 => 4639,
		3072 => 4640,
		2538 => 4584
	);

	$ar1CFilter = array();
	foreach ($arRecode as $key => $value) $ar1CFilter[] = $key;

	$res = CIBlockElement::GetList(
		array('PROPERTY_CODE_1C' => 'asc'),
		array('IBLOCK_ID' => 9, 'PROPERTY_IZD_ID' => 108, 'PROPERTY_PERIOD_ID' => 63890, 'PROPERTY_REGION_ID' => 56, 'PROPERTY_CODE_1C' => $ar1CFilter),
		false, false,
		array('IBLOCK_ID', 'ID', 'PROPERTY_CODE_1C')
	);

	while ($arFields = $res->GetNext()) {
		CIBlockElement::SetPropertyValuesEx($arFields['ID'], 9, array('CODE_1C' => $arRecode[$arFields['PROPERTY_CODE_1C_VALUE']]));
	}

	$res = CIBlockElement::GetList(
		false,
		array('IBLOCK_ID' => 5, 'PROPERTY_CODE_1C' => $ar1CFilter),
		false, false, 
		array('IBLOCK_ID', 'ID')
	);

	while ($arFields = $res->GetNext()) {
		CIBlockElement::SetPropertyValuesEx($arFields['ID'], 5, array('CODE_1C' => $arRecode[$arFields['PROPERTY_CODE_1C_VALUE']]));
	}

}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>