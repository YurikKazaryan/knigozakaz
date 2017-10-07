<?php
/**
 * Created by PhpStorm.
 * User: revil
 * Date: 21.08.17
 * Time: 11:12
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

CModule::IncludeModule('iblock');

if (($arParams['CACHE_TYPE'] == 'N') || $this->StartResultCache($arParams['CACHE_TIME'])) {

    $this->IncludeComponentTemplate();

}
