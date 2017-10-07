<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	'PARAMETERS' => array(
		'CACHE_TIME'  =>  array('DEFAULT'=>3600),
		'ERROR_LOG' => array(
			'NAME' => 'Выгружать журнал ошибок',
			'TYPE' => 'STRING',
			'MULTIPLE' => 'N',
			'PARENT' => 'BASE',
			'DEFAULT' => 'N',
		),
	),
);
?>
