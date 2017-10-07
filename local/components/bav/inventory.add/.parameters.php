<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	'PARAMETERS' => array(
		'MAX_RESULT' => array(
			'NAME' => 'Максимальное количество результатов в поиске',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'PARENT' => 'BASE',
			'DEFAULT' => '10',
			),
		'USE_PATH' => array(
			'NAME' => 'Путь к разделу инвентаризации',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'PARENT' => 'BASE',
			'DEFAULT' => '',
			),
		'CACHE_TIME'  =>  array('DEFAULT'=>3600),
	),
);
?>
