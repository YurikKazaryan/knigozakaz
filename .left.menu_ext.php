<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$aMenuLinksExt=$APPLICATION->IncludeComponent("bitrix:menu.sections", "", array(
	"IS_SEF" => "Y",
	"SEF_BASE_URL" => "",
	"SECTION_PAGE_URL" => "/books/#SECTION_ID#/",
	"DETAIL_PAGE_URL" => "/books/#SECTION_ID#/#ELEMENT_ID#",
	"IBLOCK_TYPE" => "books",
	"IBLOCK_ID" => "5",
	"DEPTH_LEVEL" => "1",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000"
	),
	false
);

array_unshift($aMenuLinksExt, array('-', '/', array('/books/'), array('FROM_IBLOCK' => 1, 'IS_PARENT' => '', 'DEPTH_LEVEL' => 1)));
array_unshift($aMenuLinksExt, array('Все', '/books/', array('/books/'), array('FROM_IBLOCK' => 1, 'IS_PARENT' => '', 'DEPTH_LEVEL' => 1)));

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>