<?
/********************************************************************
* Возвращает количество заказов для обработки и список их ID
*
* Параметры (передаются через POST)
*    IZD_ID - ID издательства
*    MUN_ID - ID муниципалитета
********************************************************************/
// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

$izdID = intval($_POST['IZD_ID']);
$munID = intval($_POST['MUN_ID']);

$result = array('error' => 1, 'count' => 0, 'orders' => array());

if($izdID && $munID && CModule::IncludeModule('iblock')) {

	// Получаем список ID школ указанного муниципалитета
	$arSchools = get_schoolID_by_mun($munID);

	$arPeriod = getWorkPeriod();

	// Считаем количество заказов
	$res =  CIBlockElement::GetList(
		false,
		array(
			'IBLOCK_ID' => IB_ORDERS_LIST,
			'PROPERTY_REGION' => getRegionFilter(),
			'PROPERTY_PERIOD' => $arPeriod['ID'],
			'PROPERTY_SCHOOL_ID' => $arSchools,
			'PROPERTY_IZD_ID' => $izdID,
			'!PROPERTY_STATUS' => 'osrepready'
		),
		false, false,
		array('IBLOCK_ID', 'ID')
	);
	
	while ($arFields = $res->Fetch()) {
		$result['count']++;
		$result['orders'][] = $arFields['ID'];
	}
}

// Отдаем результат
echo json_encode($result);

// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>