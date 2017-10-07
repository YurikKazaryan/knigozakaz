<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/*************************
* Создание сводного отчета
* Входные параметры:
*   $group_by - Группировка отчета:
*                   IZD - по издательствам
*                   MUN - по муниципалитетам (по умолчанию)
* Школы выбираются в зависимости от прав текущего пользователя!
*************************/
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
global $DB;

function get_svod($group_by = 'MUN') {
	$result = false;
	global $USER;

	function get_mun_by_school($sch_id, $arMun) {
		$result = false;

		foreach ($arMun as $munID => $arSchool) {
			if (array_search($sch_id, $arSchool) !== false) {
				$result = $munID;
				break;
			}
		}
		return $result;
	}

	if (CModule::IncludeModule('iblock')) {

		// Вычисляем список ID школ которые могут войти в отчет по уровню текущего пользователя
		$arSchoolList = array();
		$arSchoolByMun = array();
		$arMunList = get_munID_list($USER->GetID());
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_MUN' => $arMunList), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_MUN'));
		while ($arFields = $res->GetNext()) {
			$arSchoolList[] = $arFields['ID'];
			$arSchoolByMun[$arFields['PROPERTY_MUN_VALUE']][] = $arFields['ID'];
		}

		if (count($arSchoolList) > 0) {

			// Статусы, которые идут в сводный отчет
			$arStatus = array('osrepready', 'osdocs', 'oscheck', 'osready', 'osaction', 'osclosed', 'oschecked', 'osconfirm');

			// Выбираем все записи для отчета
			$res = CIBlockElement::GetList(
				array('PROPERTY_FP_CODE' => 'asc'),
				array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_STATUS' => $arStatus, 'PROPERTY_SCHOOL_ID' => $arSchoolList),
				false, false,
				array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_FP_CODE', 'PROPERTY_STATUS', 'PROPERTY_COUNT', 'PROPERTY_IZD_ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_BOOK_ID', 'PROPERTY_PRICE')
			);

			while ($arFields = $res->GetNext()) {

				if (!$result) $result = array();	// Создаем массив отчета

				if ($group_by == 'MUN') {

					$mun_id = get_mun_by_school($arFields['PROPERTY_SCHOOL_ID_VALUE'], $arSchoolByMun);

					if (!is_array($result[$mun_id]))
						$result[$mun_id] = array(
							'NAME' => get_izd_name($mun_id),
							'COUNT' => 0,
							'SUM' => 0,
							'IZD' => array()
						);

					if (!is_array($result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]))
						$result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']] = array(
							'NAME' => get_izd_name($arFields['PROPERTY_IZD_ID_VALUE']),
							'COUNT' => 0,
							'SUM' => 0,
							'BOOKS' => array()
						);

					if (!is_array($result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']]))
						$result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']] = array(
							'FP_CODE' => $arFields['PROPERTY_FP_CODE_VALUE'],
							'NAME' => $arFields['NAME'],
							'COUNT' => 0,
							'SUM' => 0
						);

					// Суммируем в книгу
					$result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
					$result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

					// Суммируем в издательство
					$result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
					$result[$mun_id]['IZD'][$arFields['PROPERTY_IZD_ID_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

					// Суммируем в муниципалитет
					$result[$mun_id]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
					$result[$mun_id]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

				} else {

					if (!is_array($result[$arFields['PROPERTY_IZD_ID_VALUE']]))
						$result[$arFields['PROPERTY_IZD_ID_VALUE']] = array(
							'NAME' => get_izd_name($arFields['PROPERTY_IZD_ID_VALUE']),
							'COUNT' => 0,
							'SUM' => 0,
							'BOOKS' => array()
						);

					if (!is_array($result[$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']]))
						$result[$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']] = array(
							'FP_CODE' => $arFields['PROPERTY_FP_CODE_VALUE'],
							'NAME' => $arFields['NAME'],
							'COUNT' => 0,
							'SUM' => 0
						);

					// Суммируем данные по книге
					$result[$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
					$result[$arFields['PROPERTY_IZD_ID_VALUE']]['BOOKS'][$arFields['PROPERTY_BOOK_ID_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

					// Добавляем к данным по издательству
					$result[$arFields['PROPERTY_IZD_ID_VALUE']]['COUNT'] += $arFields['PROPERTY_COUNT_VALUE'];
					$result[$arFields['PROPERTY_IZD_ID_VALUE']]['SUM'] += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];

				}

			}

		} //if (count($arSchoolList) > 0) {
	}
	return $result;
}

/***********************************************************************
* Отображение исчезающего сообщения. Нужен скрипт при загрузке страницы
***********************************************************************/
function MyShowMessage($arMessage, $autoClose = true) {
	if ($arMessage) {
		if (is_array($arMessage)) {
			$type = ($arMessage['TYPE'] == 'ERROR' ? 'danger' : 'info');
			$mess = trim($arMessage['MESSAGE']);
		} else {
			$type = 'info';
			$mess = ($arMessage);
		}
		if (strlen($mess) > 0) echo '<div class="' . ($autoClose ? 'my-show-message-slide-up ' : '') . 'alert alert-' . $type . '" role="alert">' . $mess . '</div>';
	}
}

/*****************************************************
* Проверяет на совпадение пароль текущего пользователя
*****************************************************/
function test_user_pass($pass) {
	global $USER;
	$b_hash = $USER->GetParam("PASSWORD_HASH");
	$salt = substr($b_hash, 0, strlen($b_hash) - 32);
	$b_pass = substr($b_hash, -32);
	$u_hash = md5($salt . $pass);
	return ($u_hash == $b_pass);
}

/***************************************************
* Возвращает файл спецификации в виде строки
* Если второй параметр true - возвращает приложение
***************************************************/
function getSpecification($order_id, $dop = false) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		// Получаем файл спецификации
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $order_id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_SPEC', 'PROPERTY_SPEC2'));
		if ($arFields = $res->GetNext()) $file = ($dop ? $arFields['PROPERTY_SPEC2_VALUE'] : $arFields['PROPERTY_SPEC_VALUE']);
		if ($file) {
			$result = file_get_contents($_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($file));
		} else
			$result = false;
	}
	return $result;
}

/**************************************************************************
* Возвращает файл в виде строки с текстом договора по школе по номеру заказа
**************************************************************************/
function get_dogovor($order_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {

		$arOrder = getOrderInfo($order_id); // Загружаем информацию о заказе
		$arSchool = getSchoolInfo(get_school_by_order($order_id)); // Загружаем информацию о школе
		$doc = get_izd_file($order_id, $arSchool['PUNKT_FZ']); // Загружаем файл шаблона

		$orderNum = $arOrder['SCHOOL_ID'] . '-' . $arOrder['ORDER_NUM'];

		if (is_array($arSchool) && $doc) {

			// Общие поля для всех издательств ******************************

			$address = get_school_address($arSchool['ID']);

			// Форматируем НДС 10 и 18 (если есть)

			$ndsSum10 = getNdsSum(floatval($arOrder['SUM_10']), 10);
			$ndsSum18 = getNdsSum(floatval($arOrder['SUM_18']), 18);

			$nds_sum = sprintf('%01.2f', $ndsSum10);
			list($nds_rub, $nds_kop) = explode('.', $nds_sum);

			$nds18_sum = sprintf('%01.2f', $ndsSum18);
			list($nds18_rub, $nds18_kop) = explode('.', $nds18_sum);

			// Штрафы
			$shtraf25 = $arOrder['SUM'] * 0.025;	// 2.5%
			$shtraf10 = $arOrder['SUM'] * 0.1;		// 10%

			// В зависимости от типа школы определяем тип документа (для некоторых издательств)
			switch ($arSchool['STATUS']) {
				case 'orgskaz': $docType = 'Муниципальный контракт'; break;
				case 'orgsauto': $docType = 'Договор'; break;
				default: $docType = 'Гражданско-правовой договор';
			}

			$doc = str_replace('DOGOVORTYPE', strToHexByRtf($docType), $doc);

			$doc = str_replace('DOGOVORNUM', strToHexByRtf($orderNum), $doc);
//			$doc = str_replace('DOGOVORDATE', strToHexByRtf('« ' . date('d', $arOrder['DATE']) . ' » ' . month_name_r(date('n', $arOrder['DATE'])) . ' ' . date('Y', $arOrder['DATE']) . ' г.'), $doc);
			$doc = str_replace('DOGOVORDATE', strToHexByRtf('«____» ____________ 201__ г.'), $doc);

//			$doc = str_replace('DOGOVORDAY', strToHexByRtf(date('d', $arOrder['DATE'])), $doc);
//			$doc = str_replace('DOGOVORMONTH', strToHexByRtf(month_name_r(date('n', $arOrder['DATE']))), $doc);
//			$doc = str_replace('DOGOVORYEAR', strToHexByRtf(substr(date('Y', $arOrder['DATE']),3,1)), $doc);

			$doc = str_replace('DOGOVORDAY', strToHexByRtf('___'), $doc);
			$doc = str_replace('DOGOVORMONTH', strToHexByRtf('_____________'), $doc);
			$doc = str_replace('DOGOVORYEAR', strToHexByRtf('__'), $doc);

			$doc = str_replace('DOGOVORCITY', strToHexByRtf(($arSchool['PUNKT'] ? $arSchool['PUNKT'] : $arSchool['RAJON'])), $doc);
			$doc = str_replace('DOGOVOROBLAST', strToHexByRtf(get_obl_name_r(get_obl_id($arSchool['MUN']))), $doc);
			$doc = str_replace('SCHOOLFULLNAME', strToHexByRtf(html_entity_decode($arSchool['FULL_NAME'])), $doc);
			$doc = str_replace('SCHOOLSHORTNAME', strToHexByRtf(html_entity_decode($arSchool['NAME'])), $doc);
			$doc = str_replace('SCHOOLADDRESS', strToHexByRtf($address), $doc);

			$doc = str_replace('DATAPOSTAVKI', strToHexByRtf($arOrder['DATAPOSTAVKI'] ? '«' . date('d', $arOrder['DATAPOSTAVKI']) . '» ' . month_name_r(date('n', $arOrder['DATAPOSTAVKI'])) . ' ' . date('Y', $arOrder['DATAPOSTAVKI']) . ' г.' : '«____»________________201__ г.'), $doc);
			$doc = str_replace('ADDRESSPOSTAVKI', strToHexByRtf($address), $doc);

			$doc = str_replace('DIRFIOR', strToHexByRtf(trim($arSchool['DIR_FIO_R'])), $doc);
			$doc = str_replace('DIROSNOV', strToHexByRtf(trim($arSchool['DIR_DOC'])), $doc);

			$doc = str_replace('REKINN', strToHexByRtf($arSchool['INN']), $doc);
			$doc = str_replace('REKKPP', strToHexByRtf($arSchool['KPP']), $doc);
			$doc = str_replace('REKOGRN', strToHexByRtf($arSchool['OGRN']), $doc);
			$doc = str_replace('REKOKPO', strToHexByRtf($arSchool['OKPO']), $doc);
			$doc = str_replace('REKOKOPF', strToHexByRtf($arSchool['OKOPF']), $doc);
			$doc = str_replace('REKRASSCHET', strToHexByRtf($arSchool['RASCH']), $doc);
			$doc = str_replace('REKBANK', strToHexByRtf($arSchool['BANK']), $doc);
			$doc = str_replace('REKBIK', strToHexByRtf($arSchool['BIK']), $doc);
			$doc = str_replace('REKLICSCHET', strToHexByRtf($arSchool['LS']), $doc);
			$doc = str_replace('REKOKTMO', strToHexByRtf(''), $doc);
			$doc = str_replace('REKOKTO', strToHexByRtf(''), $doc);

			$doc = str_replace('REKTELEFON', strToHexByRtf($arSchool['PHONE']), $doc);
			$doc = str_replace('REKEMAIL', strToHexByRtf($arSchool['EMAIL']), $doc);
			$doc = str_replace('DIRFINIC', strToHexByRtf(get_finic($arSchool['DIR_FIO'])), $doc);

			$doc = str_replace('DOGOVORPRICE', strToHexByRtf(sprintf('%.2f руб.', $arOrder['SUM'])), $doc);
			$doc = str_replace('PROPISPRICE', strToHexByRtf(num2str($arOrder['SUM'])), $doc);

			$doc = str_replace('NDSSUM', strToHexByRtf($nds_sum), $doc);
			$doc = str_replace('NDSRUB', strToHexByRtf($nds_rub), $doc);
			$doc = str_replace('NDSKOP', strToHexByRtf($nds_kop), $doc);
			$doc = str_replace('NDSPROPIS', strToHexByRtf(num2str($nds_sum)), $doc);

			$doc = str_replace('NDS18SUM', strToHexByRtf($nds18_sum), $doc);
			$doc = str_replace('NDS18RUB', strToHexByRtf($nds18_rub), $doc);
			$doc = str_replace('NDS18KOP', strToHexByRtf($nds18_kop), $doc);
			$doc = str_replace('NDS18PROPIS', strToHexByRtf(num2str($nds18_sum)), $doc);

			$doc = str_replace('SHTRAFASUM', strToHexByRtf(sprintf('%.2f руб.', $shtraf25)), $doc);
			$doc = str_replace('SHTRAFAPROPIS', strToHexByRtf(num2str($shtraf25)), $doc);

			$doc = str_replace('SHTRAFBSUM', strToHexByRtf(sprintf('%.2f руб.', $shtraf10)), $doc);
			$doc = str_replace('SHTRAFBPROPIS', strToHexByRtf(num2str($shtraf10)), $doc);

			$doc = str_replace('NDSFULL', strToHexByRtf(
											($ndsSum10 ? 'в том числе НДС 10% в размере ' . sprintf('%01.2f', $ndsSum10) . ' руб. (' . num2str($ndsSum10) . ')' : '') .
											($ndsSum18 ? ', в том числе НДС 18% в размере ' . sprintf('%01.2f', $ndsSum18) . ' руб. (' . num2str($ndsSum18) . ')' : '')
										  ), $doc);

			$doc = str_replace('ISTOCHNIK', strToHexByRtf(strlen(trim($arOrder['ISTOCHNIK'])) > 0 ? $arOrder['ISTOCHNIK'] : '_______________________________'), $doc);

			$result = $doc;
		} else {
			$result = strToHexByRtf('');
		}
	}
	return $result;
}

/*****************************************
* Возвращает массив с информацией о заказе
*****************************************/
function get_order_info($order_id) { return getOrderInfo($order_id); }
function getOrderInfo($order_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $order_id),
			false, false,
			array('IBLOCK_ID', 'ID',
				'PROPERTY_IZD_ID',
				'ACTIVE_FROM',
				'PROPERTY_ISTOCHNIK',
				'PROPERTY_DATAPOSTAVKI',
				'PROPERTY_ORDER_NUM',
				'PROPERTY_SUM',
				'PROPERTY_SUM_10',
				'PROPERTY_SUM_18',
				'PROPERTY_SCHOOL_ID'
			)
		);
		if ($arFields = $res->getNext())
			$arIst = get_istochnik_spr();
			$result = array(
				'ORDER_NUM' => $arFields['PROPERTY_ORDER_NUM_VALUE'],
				'SCHOOL_ID' => $arFields['PROPERTY_SCHOOL_ID_VALUE'],
				'SUM' => $arFields['PROPERTY_SUM_VALUE'],
				'SUM_10' => $arFields['PROPERTY_SUM_10_VALUE'],
				'SUM_18' => $arFields['PROPERTY_SUM_18_VALUE'],
				'IZD' => $arFields['PROPERTY_IZD_ID_VALUE'],
				'ID'  => $arFields['ID'],
				'DATE' => MakeTimeStamp($arFields['ACTIVE_FROM']),
				'ISTOCHNIK' => $arIst[$arFields['PROPERTY_ISTOCHNIK_VALUE']] ? $arIst[$arFields['PROPERTY_ISTOCHNIK_VALUE']]['FULL'] : '              ',
				'DATAPOSTAVKI' => MakeTimeStamp($arFields['PROPERTY_DATAPOSTAVKI_VALUE'])
			);
	}
	return $result;
}

/************************************************************
* Возвращает массив с информацией о школе
* Устаревшая - рекомендуется использовать getSchoolInfoList()
*************************************************************/
function get_school_info($school_id) { return getSchoolInfo($school_id); }
function getSchoolInfo($school_id) {
	$result = false;
	$arTemp = getSchoolInfoList($school_id);
	if (is_array($arTemp)) $result = $arTemp[$school_id];
	return $result;
}

/******************************************
* Возвращает массив с информацией о школАХ
******************************************/
function getSchoolInfoList($school_id) {
	$result = false;
//	if (!is_array($school_id)) $school_id = array($school_id);
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'ID' => $school_id), false, false, array(
			'IBLOCK_ID', 'ID', 'NAME',
			'PROPERTY_FULL_NAME',
			'PROPERTY_OGRN',
			'PROPERTY_INN',
			'PROPERTY_KPP',
			'PROPERTY_PFR',
			'PROPERTY_OKPO',
			'PROPERTY_OGRN',
			'PROPERTY_OKOGU',
			'PROPERTY_OKFS',
			'PROPERTY_OKOPF',
			'PROPERTY_INDEX',
			'PROPERTY_RAJON',
			'PROPERTY_PUNKT',
			'PROPERTY_RAJON_GORODA',
			'PROPERTY_ULICA',
			'PROPERTY_DOM',
			'PROPERTY_PHONE',
			'PROPERTY_EMAIL',
			'PROPERTY_RASCH',
			'PROPERTY_BANK',
			'PROPERTY_BIK',
			'PROPERTY_DIR_FIO',
			'PROPERTY_DIR_FIO_R',
			'PROPERTY_DIR_DOC',
			'PROPERTY_OTV_FIO',
			'PROPERTY_OTV_PHONE',
			'PROPERTY_LS',
			'PROPERTY_MUN',
			'PROPERTY_PUNKT_FZ',
			'PROPERTY_REPORT_MODE',
			'PROPERTY_ADMIN',
			'PROPERTY_STATUS',
			'PROPERTY_OBLAST'
		));

		while ($arFields = $res->GetNext()) {
			if (!is_array($result)) $result = array();
			$result[$arFields['ID']] = array(
				'NAME' =>		trim($arFields['~NAME']),
				'FULL_NAME' =>	trim($arFields['~PROPERTY_FULL_NAME_VALUE']),
				'OGRN' =>		trim($arFields['PROPERTY_OGRN_VALUE']),
				'INN' =>		trim($arFields['PROPERTY_INN_VALUE']),
				'KPP' =>		trim($arFields['PROPERTY_KPP_VALUE']),
				'PFR' =>		trim($arFields['PROPERTY_PFR_VALUE']),
				'OKPO' =>		trim($arFields['PROPERTY_OKPO_VALUE']),
				'OGRN' =>		trim($arFields['PROPERTY_OGRN_VALUE']),
				'OKOGU' =>		trim($arFields['PROPERTY_OKOGU_VALUE']),
				'OKFS' =>		trim($arFields['PROPERTY_OKFS_VALUE']),
				'OKOPF' =>		trim($arFields['PROPERTY_OKOPF_VALUE']),
				'INDEX' =>		trim($arFields['PROPERTY_INDEX_VALUE']),
				'RAJON' =>		trim($arFields['PROPERTY_RAJON_VALUE']),
				'PUNKT' =>		trim($arFields['PROPERTY_PUNKT_VALUE']),
				'RAJON_GORODA' => trim($arFields['PROPERTY_RAJON_GORODA_VALUE']),
				'ULICA' =>		trim($arFields['~PROPERTY_ULICA_VALUE']),
				'DOM' =>		trim($arFields['PROPERTY_DOM_VALUE']),
				'PHONE' =>		trim($arFields['PROPERTY_PHONE_VALUE']),
				'EMAIL' =>		trim($arFields['PROPERTY_EMAIL_VALUE']),
				'RASCH' =>		(trim($arFields['PROPERTY_RASCH_VALUE']) ? ' ' . trim($arFields['PROPERTY_RASCH_VALUE']) . ' ' : ''),
				'BANK' =>		trim($arFields['~PROPERTY_BANK_VALUE']),
				'BIK' =>		trim($arFields['PROPERTY_BIK_VALUE']),
				'DIR_FIO' =>	trim($arFields['PROPERTY_DIR_FIO_VALUE']),
				'DIRFINIC' =>	get_finic(trim($arFields['PROPERTY_DIR_FIO_VALUE'])),
				'DIR_FIO_R' =>	trim($arFields['PROPERTY_DIR_FIO_R_VALUE']),
				'DIR_DOC' =>	trim($arFields['PROPERTY_DIR_DOC_VALUE']),
				'OTV_FIO' =>	trim($arFields['PROPERTY_OTV_FIO_VALUE']),
				'OTV_PHONE' =>	trim($arFields['PROPERTY_OTV_PHONE_VALUE']),
				'LS'		=>  (trim($arFields['PROPERTY_LS_VALUE']) ? ' ' . trim($arFields['PROPERTY_LS_VALUE']) . ' ' : ''),
				'ID'		=>	$arFields['ID'],
				'MUN'		=>  $arFields['PROPERTY_MUN_VALUE'],
				'PUNKT_FZ'	=>	$arFields['PROPERTY_PUNKT_FZ_VALUE'],
				'REPORT_MODE'=> $arFields['PROPERTY_REPORT_MODE_VALUE'],
				'ADDRESS' => get_school_address($school_id),
				'ADMIN' =>		$arFields['PROPERTY_ADMIN_VALUE'],
				'STATUS' =>		$arFields['PROPERTY_STATUS_VALUE'],
				'OBLAST' =>		$arFields['PROPERTY_OBLAST_VALUE']
			);
		}
	}
	return $result;
}




/****************************************************************************
* Возвращает файл, загруженный в строку
* Параметры:
*     $order_id - номер заказа
*     $type_school - статус школы (по какому ФЗ идут закупки) - для договоров
*     $type - тип документа (значение из справочника, по умолчанию - договор)
****************************************************************************/
function get_izd_file($order_id, $type_school = 'punktfz4', $type = 'dogovor') {
	$result = false;

	if (CModule::IncludeModule('iblock')) {
		if ($izd_id = get_izd_by_order($order_id)) {
			$res = CIBlockElement::GetList(
				false,
				array('IBLOCK_ID' => IB_IZD_FILES, 'PROPERTY_IZD_ID' => $izd_id, 'PROPERTY_TYPE' => $type, 'PROPERTY_TYPE_SCHOOL' => $type_school, 'PROPERTY_REGION_ID' => getRegionFilter()),
				false, false,
				array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_TYPE', 'PROPERTY_TYPE_SCHOOL', 'PROPERTY_FILE')
			);

			if ($arFields = $res->GetNext())
				$result = file_get_contents($_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($arFields['PROPERTY_FILE_VALUE']));
		}
	}
	return $result;
}

/**********************************************
* Возварщает ID издательства по номеру договора
**********************************************/
function get_izd_by_order($order_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $order_id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID'));
		if ($arFields = $res->GetNext()) $result = $arFields['PROPERTY_IZD_ID_VALUE'];
	}
	return $result;
}

/*********************************************
* Возвращает массив справочника статуса школы
*********************************************/
function getSchoolTypeSpr() {
	return (array(
		1 => array('VALUE' => 'orgsbudget',	'NAME' => 'Бюджетная'),
		2 => array('VALUE' => 'orgsauto',	'NAME' => 'Автономная'),
		3 => array('VALUE' => 'orgskaz',	'NAME' => 'Казённая')
	));
}

/***********************************************
* Возвращает массив справочника пунктов ФЗ школ
***********************************************/
function get_school_status_spr() { return getSchoolStatusSpr(); }
function getSchoolStatusSpr() {
	return (array(
		1 => array('VALUE' => 'punktfz4',	'NAME' => 'п.4 ФЗ-44'),
		2 => array('VALUE' => 'punktfz5',	'NAME' => 'п.5 ФЗ-44'),
		3 => array('VALUE' => 'punktfz14',	'NAME' => 'п.14 ФЗ-44'),
		4 => array('VALUE' => 'punktfz223',	'NAME' => 'ФЗ-223')
	));
}

/***************************************
* Возвращает массив справочника статусов
***************************************/
function get_status_spr() { return getStatusSpr(); }
function getStatusSpr() {
	return (array(
		0 => array('VALUE' => 'oscart',		'NAME' => 'В корзине'),
		1 => array('VALUE' => 'osdocs',		'NAME' => 'На оформлении'),
		2 => array('VALUE' => 'oscheck',	'NAME' => 'На проверке'),
		3 => array('VALUE' => 'oschecked',	'NAME' => 'Проверен'),
		4 => array('VALUE' => 'osconfirm',  'NAME' => 'Подтверждён'),
		5 => array('VALUE' => 'osaction',	'NAME' => 'На исполнении'),
        6 => array('VALUE' => 'ospaid',	    'NAME' => 'Оплачен'),
		7 => array('VALUE' => 'osready',	'NAME' => 'Исполнен'),
		8 => array('VALUE' => 'osrecieved',	'NAME' => 'Книги получены'),
		9 => array('VALUE' => 'osclosed',	'NAME' => 'Закрыт')
	));
}

/***************************************
* Возвращае название статуса по его коду 
***************************************/
function getStatusName($statusCode) {
	$result = false;
	$arTemp = getStatusSpr();
	foreach ($arTemp as $arValue)
		if ($arValue['VALUE'] == $statusCode) {
			$result = $arValue['NAME'];
			break;
		}
	return $result;
}

/********************************************************
* Возвращает массив справочника источников финансирования
********************************************************/
function get_istochnik_spr() {
	return (array(
		'istmun' => array('SHORT' => 'Муниц.бюджет', 'FULL' => 'муниципальный бюджет'),
		'istsob' => array('SHORT' => 'Собст.ср-ва', 'FULL' => 'собственные средства')
	));
}

/***********************************************************************************
* Поиск в справочнике статусов. Возвращает ID (mode = 'ID') или NAME (mode = 'NAME')
***********************************************************************************/
function status_search($status_value, $mode = 'ID') {
	$result = false;
	$arTemp = get_status_spr();
	foreach ($arTemp as $key => $arStatus)
		if ($arStatus['VALUE'] == $status_value)
			$result = ($mode == 'ID' ? $key : $arStatus['NAME']);
	return $result;
}

/************************************
* Возвращает название статуса по коду
************************************/
function get_spr_name($spr_id) {
	return (status_search($spr_id, 'NAME'));
}

/************************************
* Получение ID школы по номеру заказа
************************************/
function get_school_by_order($order_id = false) { return getSchoolByOrder($order_id); }
function getSchoolByOrder($order_id = false) {
	$result = false;
	if ($order_id && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $order_id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID'));
		if ($arFields = $res->GetNext()) $result = $arFields['PROPERTY_SCHOOL_ID_VALUE'];
	}
	return $result;
}

/******************************
* Сдвиг статуса заказа "вперед"
******************************/
function state_change($order_id, $forward = 1) {

	$arStatus = get_status_spr();

	// Статус заказа меняется только оператором. Проверяем.
	if (CSite::InGroup(array(9))) {
		if (CModule::IncludeModule('iblock')) {
			// Получаем текущий статус заказа
			$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $order_id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS'));
			if ($arFields = $res->GetNext()) {
				if (($key = status_search($arFields['PROPERTY_STATUS_VALUE'])) !== false) {
					$new_status = false;

					if ($forward == 1 && $key < 9) $new_status = $arStatus[$key + 1];
					else if ($forward == -1 && $key > 1) $new_status = $arStatus[$key - 1];

					if ($new_status !== false) {
						// Меняем статус в списке заказов
						CIBlockElement::SetPropertyValuesEx($order_id, 11, array('STATUS' => $new_status));

						// Меняем статус в списке заказанной литературы
						$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_ORDER_NUM' => $order_id), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_NUM', 'PROPERTY_STATUS'));
						while ($arFields = $res->GetNext())
							CIBlockElement::SetPropertyValuesEx($arFields['ID'], IB_ORDERS, array('STATUS' => $new_status));
					}
				}
			}
		}
	}
}

/********************************************
* Преобразование строки для записи в RTF-файл
********************************************/
function strToHexByRtf($sString, $sEncoding = 'utf-8') {
    $sString = iconv($sEncoding, 'windows-1251', $sString);
    $sString = preg_replace("/([a-zA-Z0-9]{2})/", "\'$1", bin2hex($sString));
    return $sString;
}

/************************************************************
* Определяет название издательства или муниципалитета по коду
************************************************************/
function get_izd_name($izd_id) { return getIzdName($izd_id); }
function getIzdName($izd_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetByID($izd_id);
		if ($arFields = $res->GetNext()) $result = $arFields['NAME'];
	}
	return($result);
}

/************************************************************
 * Определяет ID администратора школы по ID школы
 ************************************************************/
function get_adminId_by_schoolId($schoolId) {
    $admin = CIBlockElement::GetProperty(10, $schoolId, false, Array("ID" => 72));

    while ($prop = $admin->Fetch())
        if ($prop["ID"] === "72") $adminId = $prop["VALUE"];

    return $adminId;
}


/*********************************************
* Определяем ID области по коду муниципалитета
*********************************************/
function get_obl_id($mun_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		// Получаем depth_level
		$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'ID' => $mun_id), false, array('IBLOCK_ID', 'ID', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'));
		if ($arFields = $res->GetNext()) {
			$temp = $arFields['ID'];
			for ($i=$arFields['DEPTH_LEVEL']-1; $i>0; $i--) {
				$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'ID' => $arFields['IBLOCK_SECTION_ID']), false, array('IBLOCK_ID', 'ID', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'));
				$arFields = $res->GetNext();
				$temp = $arFields['ID'];
			}
			$result = $temp;
		}
	}
	return($result);
}

/********************************************************************************************
* Проверка на принадлежность текущего юзера к администраторам муниципалитета школы $school_id
********************************************************************************************/
function is_admin($school_id) {
	$result = false;
	global $USER;
	if (CSite::InGroup(array(6,7,9))) {
		$arSchoolList = get_schoolID_list($USER->GetID());
		$result = in_array($school_id, $arSchoolList);
	}
	return $result;
}

/*********************************************************************
* Проверка на наличие сформированного отчета для текущего пользователя
*********************************************************************/
function is_report_enabled() {
	global $USER;
	$result = false;
	if (CSite::InGroup(array(8)) && CModule::IncludeModule('iblock')) {

			$arPeriod = getWorkPeriod();

			$sid = getSchoolID($USER->GetID());
			$cnt = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS_LIST, 'PROPERTY_SCHOOL_ID' => $sid, 'PROPERTY_STATUS' => 'osrepready', 'PROPERTY_PERIOD' => $arPeriod['ID']), array(), false, array('IBLOCK_ID', 'ID', 'PROPERTY_SCHOOL_ID', 'PROPERTY_STATUS', 'PROPERTY_PERIOD'));
			$result = !($cnt > 0);
	}
	return $result;
}

/*******************************************
* Проверка и установка режима отчета/заказа
* На входе - on или off
*******************************************/
function set_report_mode($mode) {
	global $USER;
	if (CSite::InGroup(array(8)) && ($mode == 'off' || $mode == 'on')) {
		if (CModule::IncludeModule('iblock')) {
			$sid = getSchoolID($USER->GetID());
			CIBlockElement::SetPropertyValuesEx($sid, 10, array('REPORT_MODE' => ($mode == 'on' ? 1 : 0)));
		}
	}
}

/**********************************************************************************
* Получение режима работы школы текущего пользователя (false - заказ, true - отчет)
**********************************************************************************/
function get_report_mode() {
	global $USER;
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_ADMIN' => array($USER->GetID())), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ADMIN', 'PROPERTY_REPORT_MODE'));
		if ($arFields = $res->GetNext()) {
			$result = ($arFields['PROPERTY_REPORT_MODE_VALUE'] == 1);
		}
	}
	return $result;
}

/*******************************************************************
* Получение ID школы, админом которой является пользователь $user_id
*******************************************************************/
function get_schoolID($user_id) { return getSchoolID($user_id); }
function getSchoolID($user_id) {
	$result = false;
	if ($user_id && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_ADMIN' => array($user_id)), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ADMIN'));
		if ($arFields = $res->GetNext()) $result = $arFields['ID'];
	}
	return($result);
}

/*********************************************************************
* Получение NAME школы, админом которой является пользователь $user_id
*********************************************************************/
function get_schoolName($user_id) { return getSchoolName($user_id); }
function getSchoolName($user_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_ADMIN' => array($user_id)), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ADMIN', 'NAME'));
		if ($arFields = $res->GetNext()) $result = $arFields['NAME'];
	}
	return($result);
}

/******************************
* Получение NAME школы по ее ID
******************************/
function get_school_name_by_id($school_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetByID($school_id);
		if ($arFields = $res->GetNext()) $result = $arFields['NAME'];
	}
	return $result;
}

/************************************************************
* Получаем список вложенных ID муниципалитетов по ID родителя
************************************************************/
function get_mun_id_for_filter($parent_id) {
	$result = array($parent_id);
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $parent_id), false, array('IBLOCK_ID', 'ID', 'DEPTH_LEVEL', 'SECTION_ID'));
		while ($arFields = $res->GetNext()) {
			$result[] = $arFields['ID'];
			if ($arFields['DEPTH_LEVEL'] < 3) {
				$res2 = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields['ID']), false, array('IBLOCK_ID', 'ID', 'SECTION_ID'));
				while ($arFields2 = $res2->GetNext()) $result[] = $arFields2['ID'];
			}
		}
	}
	return $result;
}

/********************************************************************
* Получение списка муниципалитетов, админом которых является $user_id
********************************************************************/
function get_mun_list($user_id = false) { return getMunList($user_id); }
function getMunList($user_id = false) {
	$result = array();
	if ($user_id && CModule::IncludeModule('iblock')) {
		$arUserGroups = CUser::GetUserGroup($user_id);
		if (in_array(6, $arUserGroups) || in_array(9, $arUserGroups)) { // Администратор области
			$res = CIBlockSection::GetList(array('name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, array('LOGIC' => 'OR', 'UF_ADMIN' => array($user_id), 'UF_OPERATOR' => array($user_id)), 'DEPTH_LEVEL' => 1), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME'));
			while ($arFields = $res->GetNext()) {
				$result[$arFields['ID']] = $arFields['NAME'];
				$res2 = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields['ID'], 'DEPTH_LEVEL' => 2), false, array('IBLOCK_ID', 'ID', 'DEPTH_LEVEL', 'NAME', 'SECTION_ID'));
				while ($arFields2 = $res2->GetNext()) {
					$result[$arFields2['ID']] = '... ' . $arFields2['NAME'];
					$res3 = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields2['ID'], 'DEPTH_LEVEL' => 3), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME', 'SECTION_ID'));
					while ($arFields3 = $res3->GetNext())
						$result[$arFields3['ID']] = '...... ' . $arFields3['NAME'];
				}
			}
		} elseif (in_array(7, $arUserGroups)) { // Администратор муниципалитета
			$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'UF_ADMIN' => array($user_id), 'DEPTH_LEVEL' => 2), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME'));
			while ($arFields = $res->GetNext()) {
				$result[$arFields['ID']] = $arFields['NAME'];
				$res = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields['ID'], 'DEPTH_LEVEL' => 3), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME', 'SECTION_ID'));
				while ($arSectionFields = $res->GetNext())
					$result[$arSectionFields['ID']] = '... ' . $arSectionFields['NAME'];
			}

			// Проверяем админство на 3-м уровне и добавляем,если еще не добавили
			$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'UF_ADMIN' => array($user_id), 'DEPTH_LEVEL' => 3), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME'));
			while ($arFields = $res->GetNext()) {
				if (!$result[$arFields['ID']]) {
					$result[$arFields['ID']] = $arFields['NAME'];
				}
			}
		}
	}
	return(count($result) > 0 ? $result : false);
}


function getMunList2($user_id = false) {
    $result = array();
    if ($user_id && CModule::IncludeModule('iblock')) {
        $arUserGroups = CUser::GetUserGroup($user_id);
        if (in_array(6, $arUserGroups) || in_array(9, $arUserGroups)) { // Администратор области
            $res = CIBlockSection::GetList(array('name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, array('LOGIC' => 'OR', 'UF_ADMIN' => array($user_id), 'UF_OPERATOR' => array($user_id)), 'DEPTH_LEVEL' => 1), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME'));
            while ($arFields = $res->GetNext()) {
                //$result[$arFields['ID']] = $arFields['NAME'];
                $res2 = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields['ID'], 'DEPTH_LEVEL' => 2), false, array('IBLOCK_ID', 'ID', 'DEPTH_LEVEL', 'NAME', 'SECTION_ID'));
                while ($arFields2 = $res2->GetNext()) {
                    $result[$arFields2['ID']] = '... ' . $arFields2['NAME'];
                    $res3 = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields2['ID'], 'DEPTH_LEVEL' => 3), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME', 'SECTION_ID'));
                    while ($arFields3 = $res3->GetNext())
                        $result[$arFields3['ID']] = '...... ' . $arFields3['NAME'];
                }
            }
        } elseif (in_array(7, $arUserGroups)) { // Администратор муниципалитета
            $res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'UF_ADMIN' => array($user_id), 'DEPTH_LEVEL' => 2), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME'));
            while ($arFields = $res->GetNext()) {
                $result[$arFields['ID']] = $arFields['NAME'];
                $res = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $arFields['ID'], 'DEPTH_LEVEL' => 3), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME', 'SECTION_ID'));
                while ($arSectionFields = $res->GetNext())
                    $result[$arSectionFields['ID']] = '... ' . $arSectionFields['NAME'];
            }

            // Проверяем админство на 3-м уровне и добавляем,если еще не добавили
            $res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'UF_ADMIN' => array($user_id), 'DEPTH_LEVEL' => 3), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN', 'DEPTH_LEVEL', 'NAME'));
            while ($arFields = $res->GetNext()) {
                if (!$result[$arFields['ID']]) {
                    $result[$arFields['ID']] = $arFields['NAME'];
                }
            }
        }
    }
    return(count($result) > 0 ? $result : false);
}

/****************************************************************************************************
* Получение списка ID муниципалитетов, админом или оператором котрых является пользователь с $user_id
****************************************************************************************************/
function get_munID_list($user_id) {
	$result = array();
	if (CModule::IncludeModule('iblock')) {

		$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'UF_ADMIN' => $user_id), false, array('IBLOCK_ID', 'ID', 'UF_ADMIN'));
		while ($arFields = $res->GetNext()) {
			$result[] = $arFields['ID'];
		}

		$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'UF_OPERATOR' => $user_id), false, array('IBLOCK_ID', 'ID', 'UF_OPERATOR'));
		while ($arFields = $res->GetNext()) {
			if (!in_array($arFields['ID'], $result)) $result[] = $arFields['ID'];
		}

		// Если нашли разделы для указанного юзера - запрашиваем все дочерние разделы
		if (count($result) > 0) {
			$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $result), false, array('IBLOCK_ID', 'ID', 'SECTION_ID'));
			while ($arFields = $res->GetNext())
			if (!in_array($arFields['ID'], $result)) $result[] = $arFields['ID'];
		}
		// Если нашли разделы для указанного юзера - запрашиваем все дочерние разделы (третий уровень)
		if (count($result) > 0) {
			$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => $result), false, array('IBLOCK_ID', 'ID', 'SECTION_ID'));
			while ($arFields = $res->GetNext())
			if (!in_array($arFields['ID'], $result)) $result[] = $arFields['ID'];
		}
	}

	return(count($result) > 0 ? $result : false);
}

/**************************************************************************************
* Получение списка ID школ, которые подчиняются администратору или оператору с $user_id
**************************************************************************************/
function get_schoolID_list($user_id) {
	$result = array();
	if (CModule::IncludeModule('iblock')) {
		$arFilter = get_munID_list($user_id);

		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_MUN' => $arFilter), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_MUN'));
		while ($arFields = $res->GetNext()) $result[] = $arFields['ID'];
	}
	return(count($result) > 0 ? $result : false);
}

/******************************************************
* Получение названия области или муниципалитета по коду
******************************************************/
function get_obl_name($id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetByID($id);
		if ($arFields = $res->GetNext()) $result = $arFields['NAME'];
	}
	return($result);
}

/*******************************************************************
* Получение названия области или муниципалитета по коду в род.падеже
*******************************************************************/
function get_obl_name_r($id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetList(false, array('IBLOCK_ID' => IB_STRUCTURE, 'ID' => $id), false, array('IBLOCK_ID', 'ID', 'UF_NAME_R'));
		if ($arFields = $res->GetNext()) $result = $arFields['UF_NAME_R'];
	}
	return($result);
}

/****************************************
* Получение названия ед.измерений по коду
****************************************/
function get_ed_izm($id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 6, 'ID' => $id), false, false, array('IBLOCK_ID', 'ID', 'NAME'));
		if ($arFields = $res->GetNext()) $result = $arFields['NAME'];
	}
	return($result);
}

/**********************************************************
* Загрузка каталога
* Параметры:
*    $fileName - имя файла *.CSV на сервере (кодировка WIN)
*    $mode - издательство:
*               'PROSV' - "Просвещение"
*               'PROSV_EFU' - "Просвещение" (ЭФУ)
*               'DROFA' - Дрофа
*               'DROFA_EFU' - Дрофа (ЭФУ)
*				'ASTREL' - Астрель
*               'AKADEM' - Академкнига
*               'AKADEM_EFU' - Академкнига (ЭФУ)
*               'ASXXI' - Ассоциация XXI век
*               'BINOM' - Бином
*               'VLADOS' - Владос
*               'VITA' - Вита-пресс
*               'VITA_EFU' - Вита-пресс (ЭФУ)
*               'RUSSLOVO' - Русское слово
*               'VENTANA' - Вентана-Граф
*               'MNEMOZINA' - Мнемозина
*               'MNEMOZINA_EFU' - Мнемозина (ЭФУ)
*				'AKAD' - Академия
*               'GENDALF' - Гендальф ПО
* Возвращает массив:
* RESULT: true - загрузка успешна, false - ошибка
* ERROR: описание ошибки
**********************************************************/
function load_books($fileName, $mode) {
	$result = array('RESULT' => false, 'ERROR' => '');

	global $USER;

	// Проверяем существование файла
	if (file_exists($fileName)) {

		csv_test_file($fileName);

		$fp = fopen($fileName, 'r');
		$arBooks = array();
		switch ($mode) {

			// Импортируем каталог от Гендальф
			case 'GENDALF':

					// ************ НАСТРОЙКА *******************
					$izdID = 180;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numFullName = 1;		// Полное наименование учебника
					$numPrim = 2;			// Примечание
					$numPrice = 3;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(iconv('windows-1251', 'utf-8', fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$name = quotes_clear($arStr[$numFullName]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => 'нет',
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => '',
								'YEAR' => '',
								'ED_IZM' => 35,
								'PRICE' => floatval(str_replace('р.', '', str_replace(' ', '', $arStr[$numPrice]))),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => quotes_clear($arStr[$numPrim]),
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

				break;

			// Ипортируем каталог от "Просвещения"
			case 'PROSV':
					// ************ НАСТРОЙКА ПРОСВЕЩЕНИЯ *******************
					$izdID = 5;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numCode1C = 2;			// Код 1С
					$numAuthor = 3;			// Авторы
					$numClass = 5;			// Класс
					$numFullName = 7;		// Полное наименование учебника
					$numRemCode = 9;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numUMK = 8;			// линия УМК
					$numSystem = 1;			// Система
					$numYear = 10;			// Год издания
					$numPrim = 9;			// Примечание
					$numPrice = 11;			// Цена
//					$numURL = 0;			// УРЛ
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(iconv('windows-1251', 'utf-8', fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'аудио') !== false) $rem = 39;				// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'фонох') !== false) $rem = 39;			// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'online') !== false) $rem = 2698;			// с онлайн-поддержкой
						elseif (strpos($arStr[$numRemCode], 'электронным пр') !== false) $rem = 38;	// с электронным приложением

//						$url = quotes_clear($arStr[$numURL]);
//						if ((strlen($url) > 0) && (strpos($url, 'http://') === false)) $url = 'http://' . $url;

						$name = quotes_clear($arStr[$numFullName]);

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => (intval($arStr[$numYear])>0 ? intval($arStr[$numYear]) : ''),
								'ED_IZM' => strpos($arStr[$numFullName], 'омплект') !== false ? 35 : 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => quotes_clear($arStr[$numUMK]),
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => quotes_clear($arStr[$numSystem]),
								'STANDART' => '',
								'PRIM' => quotes_clear($arStr[$numPrim]),
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Просвещения" (ЭФУ)
			case 'PROSV_EFU':
					// ************ НАСТРОЙКА ПРОСВЕЩЕНИЯ *******************
					$izdID = 121;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numSystem = 1;			// Система
					$numCode1C = 2;			// Код 1С
					$numClass = 3;			// Класс
					$numAuthor = 4;			// Авторы
					$numFullName = 5;		// Полное наименование учебника
					$numUMK = 6;			// линия УМК
					$numPrim = 7;			// Примечание
					$numPrice = 8;			// Цена

//					$numRemCode = 9;		// откуда брать данные по дискам, фонохрестоматиям и пр.
//					$numYear = 10;			// Год издания
//					$numURL = 0;			// УРЛ
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(iconv('windows-1251', 'utf-8', fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$name = quotes_clear($arStr[$numFullName]);

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => 'нет',
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => quotes_clear($arStr[$numUMK]),
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => quotes_clear($arStr[$numSystem]),
								'STANDART' => '',
								'PRIM' => quotes_clear($arStr[$numPrim]),
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "ДРОФА"
			case 'DROFA':
					// ************ НАСТРОЙКА ДРОФЫ *******************
					$izdID = 100;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numCode1C = 2;			// Код 1С
					$numAuthor = 14;		// Авторы
					$numClass = 16;			// Класс
					$numFullName = 17;		// Полное наименование учебника
					$numRemCode = 17;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numSystem = 21;		// Система
					$numStandart = 20;		// Стандарт

					$numPrim1 = 24;			// Примечание1

					$numPrice = 25;			// Цена
					$numURL = 23;			// адрес УРЛ
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);		//********* Код ФП


						$rem = '';
						if (strpos($arStr[$numRemCode], 'CD') !== false) $rem = 38;		//********** Полное наименование - берем оттуда комменты

						$url = quotes_clear($arStr[$numURL]);		//********* УРЛ

						if ((strlen($url) > 0) && (strpos($url, 'http://') === false)) $url = 'http://' . $url;

						$name = quotes_clear($arStr[$numFullName]);		//********* Полное наименование

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => $url,
								'REMARKS' => $rem,
								'SYSTEM' => quotes_clear($arStr[$numSystem]),
								'STANDART' => quotes_clear($arStr[$numStandart]),
								'PRIM' => $arStr[$numPrim1] . ' ' . $arStr[$numPrim2],
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "ДРОФА" - ЭФУ
			case 'DROFA_EFU':
					// ************ НАСТРОЙКА ДРОФЫ *******************
					$izdID = 119;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numCode1C = 2;			// Код 1С
					$numAuthor = 4;			// Авторы
					$numClass = 7;			// Класс
					$numFullName = 5;		// Полное наименование учебника
					$numUMK = 8;			// линия УМК

					$numPrim1 = 1;			// ГУИД
					$numPrim2 = 3;			// ISBN

					$numPrice = 13;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);		//********* Код ФП

						$name = quotes_clear($arStr[$numFullName]);		//********* Полное наименование

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => 'нет',
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => quotes_clear($arStr[$numUMK]),
								'URL' => '',
								'REMARKS' => '',
								'PRIM' => 'Гуид: ' . $arStr[$numPrim1] . ', ISBN ' . $arStr[$numPrim2],
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "АКАДЕМИЯ"
			case 'AKAD':
					// ************ НАСТРОЙКА АКАДЕМИИ *******************
					$izdID = 110;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numCode1C = 2;			// Код 1С
					$numAuthor = 3;		// Авторы
					$numFullName = 4;		// Поное наименование учебника
					$numYear = 6;			// Год
					$numPrim1 = 5;			// Код ISBN

					$numPrice = 11;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);		//********* Код ФП

						$name = quotes_clear($arStr[$numFullName]);		//********* Полное наименование

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => '',
								'YEAR' => intval($arStr[$numYear]),
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => 'ISBN: '.$arStr[$numPrim1],
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "АСТРЕЛЬ"
			case 'ASTREL':
					// ************ НАСТРОЙКА ПОЛЕЙ *******************
					$izdID = 101;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numClass = 2;			// Класс
					$numFullName = 1;		// Поное наименование учебника
					$numPrice = 4;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Академкниги"
			case 'AKADEM':
					// ************ НАСТРОЙКА АКАДЕМКНИГИ *******************
					$izdID = 105;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numAuthor = 2;			// Автор
					$numFullName = 3;		// Полное наименование учебника
					$numClass = 5;			// Класс
					$numPrice = 10;			// Цена
					$numPrim = 4;			// Примечание
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => $arStr[$numPrim],
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Академкниги" - ЭФУ
			case 'AKADEM_EFU':
					// ************ НАСТРОЙКА АКАДЕМКНИГИ *******************
					$izdID = 112;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numAuthor = 2;			// Автор
					$numFullName = 3;		// Полное наименование учебника
					$numClass = 5;			// Класс
					$numPrice = 10;			// Цена
					$numPrim = 4;			// Примечание
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => $arStr[$numPrim],
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Ассоциация XXI век"
			case 'ASXXI':
					// ************ НАСТРОЙКА ****************************
					$izdID = 106;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numFullName = 2;		// Полное наименование учебника
					$numPrice = 7;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => '',
								'YEAR' => '',
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Бином"
			case 'BINOM':
					// ************ НАСТРОЙКА ПРОСВЕЩЕНИЯ *******************
					$izdID = 107;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numClass = 4;			// Класс
					$numFullName = 3;		// Поное наименование учебника
					$numRemCode = 6;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numStandart = 5;		// Стандарт
					$numPrice = 10;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$name = quotes_clear($arStr[$numFullName]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => strpos($arStr[$numRemCode], 'омплект') !== false ? 35 : 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => quotes_clear($arStr[$numStandart]),
								'PRIM' => quotes_clear($arStr[$numRemCode]),
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "ВЛАДОС"
			case 'VLADOS':
					// ************ НАСТРОЙКА *******************************
					$izdID = 103;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numClass = 3;			// Класс
					$numFullName = 2;		// Поное наименование учебника
					$numPrice = 8;			// Цена
					$numYear = 5;
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => quotes_clear($arStr[$numYear]),
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Русское слово"
			case 'RUSSLOVO':
					// ************ НАСТРОЙКА *******************************
					$izdID = 104;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 3;			// Код ФП
					$numClass = 5;			// Класс
					$numFullName = 4;		// Поное наименование учебника
					$numPrice = 8;			// Цена
					$numYear = 6;
					$numCode1C = 0;			// Код 1С
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => quotes_clear($arStr[$numYear]),
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => quotes_clear($arStr[$numCode1C]),
								'UMK' => '',
								'URL' => '',
								'REMARKS' => '',
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Вита-пресс"
			case 'VITA':
					// ************ НАСТРОЙКА *******************************
					$izdID = 102;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numAuthor = 1;
					$numFullName = 2;		// Поное наименование учебника
					$numClass = 3;			// Класс
					$numPrice = 5;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Вита-пресс" - ЭФУ
			case 'VITA_EFU':
					// ************ НАСТРОЙКА *******************************
					$izdID = 117;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 0;			// Код ФП
					$numAuthor = 1;
					$numFullName = 2;		// Поное наименование учебника
					$numClass = 3;			// Класс
					$numPrice = 5;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$name = quotes_clear($arStr[$numFullName]);

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => quotes_clear($arStr[$numClass]),
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => '',
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Вентана-граф"
			case 'VENTANA':
					// ************ НАСТРОЙКА *******************************
					$izdID = 108;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 1;			// Код ФП
					$numCode1C = 2;			// Код 1С
					$numFullName = 3;		// Полное наименование учебника
					$numRemCode = 3;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numPrice = 4;			// Цена
					$numPrim = 5;			// Примечание
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'аудио') !== false) $rem = 39;				// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'фонох') !== false) $rem = 39;			// с аудио-диском
						elseif (strpos($arStr[$numRemCode], 'online') !== false) $rem = 2698;			// с онлайн-поддержкой
						elseif (strpos($arStr[$numRemCode], 'CD-диск') !== false) $rem = 38;	// с электронным приложением

						$name = quotes_clear($arStr[$numFullName]);

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if ($fp_number !== false) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => '',
								'CLASS' => '',
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '№ и дата правоустанавливающего договора: ' . quotes_clear($arStr[$numPrim]),
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Мнемозина "
			case 'MNEMOZINA':
					// ************ НАСТРОЙКА *******************************
					$izdID = 109;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 4;			// Код ФП
					$numCode1C = 0;			// Код 1С
					$numFullName = 2;		// Полное наименование учебника
					$numAuthor = 1;			// Автор
					$numRemCode = 5;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numPrim = 5;			// Примечание
					$numPrice = 7;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$price = floatval($arStr[$numPrice]);	// Цена

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'CD/DVD') !== false) $rem = 38;	// с электронным приложением

						$name = quotes_clear($arStr[$numFullName]) . (strlen($arStr[$numPrim]) > 0 ? ' (' . quotes_clear($arStr[$numPrim]) . ')' : '');

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if (($fp_number !== false) && ($price > 0)) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => '',
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Ипортируем каталог от "Мнемозина - ЭФУ"
			case 'MNEMOZINA_EFU':
					// ************ НАСТРОЙКА *******************************
					$izdID = 120;	// Код издательства в системе

					// Номера полей в таблице (от 0)
					$numFPCode = 4;			// Код ФП
					$numCode1C = 0;			// Код 1С
					$numFullName = 2;		// Полное наименование учебника
					$numAuthor = 1;			// Автор
					$numRemCode = 5;		// откуда брать данные по дискам, фонохрестоматиям и пр.
					$numPrim = 5;			// Примечание
					$numPrice = 7;			// Цена
					// ******************************************************

					$numStr = 0;
					while (!feof($fp)) {
						$arStr = csv_explode(win2utf(fgets($fp)));
						$numStr++;

						$fp_number = test_fp_number($arStr[$numFPCode]);			//***** Номер ФП

						$price = floatval($arStr[$numPrice]);	// Цена

						// Поиск возможных примечаний
						$rem = '';
						if (strpos($arStr[$numRemCode], 'CD/DVD') !== false) $rem = 38;	// с электронным приложением

						$name = quotes_clear($arStr[$numFullName]) . (strlen($arStr[$numPrim]) > 0 ? ' (' . quotes_clear($arStr[$numPrim]) . ')' : '');

						$code_1c = quotes_clear($arStr[$numCode1C]);

						if (($fp_number !== false) && ($price > 0)) {
							$arBooks[$numStr] = array(
								'FP_CODE' => $fp_number,
								'NAME' => $name,
								'AUTHOR' => quotes_clear($arStr[$numAuthor]),
								'CLASS' => '',
								'YEAR' => 0,
								'ED_IZM' => 33,
								'PRICE' => floatval($arStr[$numPrice]),
								'CODE_1C' => $code_1c,
								'UMK' => '',
								'URL' => '',
								'REMARKS' => $rem,
								'SYSTEM' => '',
								'STANDART' => '',
								'PRIM' => '',
								'KEY' => $numStr
							);
						}
					}

					$result['RESULT'] = true;

					break;

			// Если $mode передали неверный
			default:
				$result['ERROR'] = "ОШИБКА: Передан неверный параметр mode: $mode";
		}
		fclose($fp);

		// Если ошибки нет, начинаем загрузку в базу
		if ($result['RESULT']) {
			if (CModule::IncludeModule('iblock')) {

				// Удаляем текущий каталог по указанному издательству
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_BOOKS, 'SECTION_ID' => $izdID), false, false, array('IBLOCK_ID', 'SECTION_ID', 'ID'));
				while ($arFields = $res->GetNext()) CIBlockElement::Delete($arFields['ID']);
$cnt_add = 0;
				foreach ($arBooks as $fp_key => $arBook) {
					// Добавляем
$cnt_add++;
					$el = new CIBlockElement;
					$res = $el->Add(array(
						'MODIFIED_BY' => $USER->GetID(),
						'IBLOCK_SECTION_ID' => $izdID,
						'IBLOCK_ID' => IB_BOOKS,
						'NAME' => $arBook['NAME'],
						'ACTIVE' => 'Y',
						'PROPERTY_VALUES' => array(
							'FP_CODE' => $arBook['FP_CODE'],
							'AUTHOR' => $arBook['AUTHOR'],
							'CLASS' => $arBook['CLASS'],
							'YEAR' => $arBook['YEAR'],
							'ED_IZM' => $arBook['ED_IZM'],
							'PRICE' => $arBook['PRICE'],
							'PRICE_56' => $arBook['PRICE'],
							'CODE_1C' => $arBook['CODE_1C'],
							'UMK' => $arBook['UMK'],
							'URL' => $arBook['URL'],
							'REMARKS' => array($arBook['REMARKS']),
							'SYSTEM' => $arBook['SYSTEM'],
							'STANDART' => $arBook['STANDART'],
							'PRIM' => $arBook['PRIM'],
							'KEY' => $arBook['KEY']
						)
					));
					if ($res === false) {
						echo "*** ОШИБКА *** $fp_key *** " . $el->LAST_ERROR . '<br>';
						echo '<pre>' . print_r($arBook, 1) . '</pre>';
						die();
					}
				}
			} else {
				$result['RESULT'] = false;
				$result['ERROR'] = 'Ошибка: не загружен модуль iblock...';
			}
		}
	} else {
		$result['ERROR'] = "ОШИБКА: не найден файл $fileName";
	}
	return $result;
}

/************************************
* Перекодировка строки из win в UTF-8
* 29.10.2014
* Исправлено на iconv 17.03.2016
************************************/
function win2utf($str) {
	return iconv('windows-1251', 'utf-8', $str);
}

/***************************************************
* Из строки Фамилия Имя Отчество делает Фамилия И.О.
****************************************************/
function get_finic($str) {
	$str = trim(str_replace('  ', ' ', $str));
	$arStr = explode(' ', $str);
	return (mb_substr($arStr[1], 0, 1, 'UTF-8') . '.' . mb_substr($arStr[2], 0, 1, 'UTF-8') . '. ' . $arStr[0]);
}

/*******************************************
* Возвращает строку с почтовым адресом школы
*******************************************/
function get_school_address($school_id) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_SCHOOLS, 'ID' => $school_id),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_INDEX', 'PROPERTY_OBLAST', 'PROPERTY_RAJON', 'PROPERTY_PUNKT', 'PROPERTY_ULICA', 'PROPERTY_DOM')
		);
		if ($arFields = $res->GetNext()) {
			$result = trim(str_replace(' ', '', $arFields['PROPERTY_INDEX_VALUE']));
//			$result .= (strlen($result) > 0 ? ', ' : '') . get_obl_name($arFields['PROPERTY_OBLAST_VALUE']);
			$result .= (strlen($result) > 0 ? ', ' : '') . get_obl_name($arFields['PROPERTY_OBLAST_VALUE']);
			$result .= (strlen($result) > 0 && $arFields['PROPERTY_RAJON_VALUE'] ? ', ' : '') . $arFields['PROPERTY_RAJON_VALUE'];
			$result .= (strlen($result) > 0 && $arFields['PROPERTY_PUNKT_VALUE'] ? ', ' : '') . $arFields['PROPERTY_PUNKT_VALUE'];
			$result .= (strlen($result) > 0 && $arFields['PROPERTY_ULICA_VALUE'] ? ', ' : '') . $arFields['PROPERTY_ULICA_VALUE'];
			$result .= (strlen($result) > 0 && $arFields['PROPERTY_DOM_VALUE'] ? ', ' : '') . ($arFields['PROPERTY_DOM_VALUE'] ? 'д.' : '') . $arFields['PROPERTY_DOM_VALUE'];
		}
	}
	return $result;
}

/*********************************
* Число - прописью
*********************************/
function num2str($num) {
	//
	$nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('копейка' ,'копейки' ,'копеек',	 1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
	//
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

// Склоняем словоформу
function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}

/**************************
* Родительный падеж месяцев
**************************/
function month_name_r($num) {
	switch ($num) {
		case  1: $result = 'Января'; break;
		case  2: $result = 'Февраля'; break;
		case  3: $result = 'Марта'; break;
		case  4: $result = 'Апреля'; break;
		case  5: $result = 'Мая'; break;
		case  6: $result = 'Июня'; break;
		case  7: $result = 'Июля'; break;
		case  8: $result = 'Августа'; break;
		case  9: $result = 'Сентября'; break;
		case 10: $result = 'Октября'; break;
		case 11: $result = 'Ноября'; break;
		case 12: $result = 'Декабря'; break;
		default: $result = false;
	}
	return $result;
}

/*******************************************
* Возвращает информацию о пользователе по ID
*******************************************/
function get_user_info($user_id) {
	$result = false;
	$arUser = CUser::GetByID($user_id);
	if ($arFields = $arUser->GetNext()) {
		$result = array(
			'LOGIN' => $arFields['LOGIN'],
			'NAME' => trim($arFields['LAST_NAME'] . ' ' . $arFields['NAME'] . ' ' . $arFields['SECOND_NAME']),
			'ID' => $user_id
		);
	}
	return $result;
}


/***************************************
* Тестовая печать массива или переменной
***************************************/
function test_print($var, $rem = false) {
	global $USER;
	if ($USER->IsAdmin()) {
		if (is_array($var))
			echo "***** $rem<br><pre>" . print_r($var, 1) . '</pre>';
		else
			echo "***** $rem => $var<br>";
	}
}

function test_out($str) {
	$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/bav_test.txt', 'a');
	if (is_array($str)) {
		fwrite($fp, print_r($str,1));
		fwrite($fp, "\n");
	} else
		fwrite($fp, $str . "\n");
	fclose($fp);
}

/**********************************************************
* Удаление из строки двойных пробелов - замена на одинарный
**********************************************************/
function trim_double_spaces($str) {
	while (strpos($str, '  ') !== false)
		$str = str_replace('  ', ' ', $str);
	return($str);
}

/********************************
* Загружает список школ ID - NAME
********************************/
function get_school_list() {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS), false, false, array('IBLOCK_ID', 'ID', 'NAME'));
		while ($arFields = $res->GetNext()) {
			if (!$result) $result = array();
			$result[$arFields['ID']] = html_entity_decode(html_entity_decode($arFields['NAME']));
		}
	}
	return $result;
}

/************************************************
* Получение списка ID школ по коду муниципалитета
************************************************/
function get_schoolID_by_mun($munID) {
	$result = '-1';
	if (CModule::IncludeModule('iblock')) {
		$arFilter = get_mun_id_for_filter($munID);
		if (count($arFilter) > 0) {
			$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'PROPERTY_MUN' => $arFilter), false, false, array('IBLOCK_ID', 'ID'));
			$arTemp = array();
			while ($arFields = $res->GetNext()) $arTemp[] = $arFields['ID'];
			if (count($arTemp) > 0) $result = $arTemp;
		}
	}
	return $result;
}

function get_schoolID_by_mun2($munID) {
    $arOrder = Array(
        "SORT" => "ASC"
    );
    $arFilter = Array(
        "IBLOCK_ID" => IB_SCHOOLS,
        "PROPERTY_44" => $munID
    );
    $arSelectFields = Array(
        "ID"
    );
    if (CModule::IncludeModule("iblock")) {
        $res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelectFields);

        while ($row = $res->Fetch())
            $tmp[] = $row["ID"];
    }

    return $tmp;
}

/*******************************************************
* Формирование буквенного адреса из номера столбца Excel
*******************************************************/
function getLetterAddress($num) {
	$address = '';
	while ($num > 0) {
		$address = chr(ord('A') + ($num-1) % 26) . $address;
		$num = floor(($num-1) / 26);
	}
	return ($address);
}

/*****************************************************
* Проверяет, заполнен ли пункт ФЗ по закупке у школы.
* Возвращает true/false
*****************************************************/
function testPunktFZ($schoolID) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$arFZ = get_school_status_spr();
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'ID' => $schoolID), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_PUNKT_FZ'));
		if ($arFields = $res->GetNext()) {
			foreach ($arFZ as $fz)
				if ($fz['VALUE'] === $arFields['PROPERTY_PUNKT_FZ_VALUE']) {
					$result = true;
					break;
				}
		}
	}
	return $result;
}

/***********************************************************************************
* ПРоверяет заполнение пункта "Статус школы" - обязательный для оформления договоров
***********************************************************************************/
function testSchoolStatus($schoolID = false) {
	$result = false;
	if ($schoolID && CModule::IncludeModule('iblock')) {
		$arStatus = getSchoolTypeSpr();
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'ID' => $schoolID), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_STATUS'));
		if ($arFields = $res->Fetch()) {
			foreach ($arStatus as $stat)
				if ($stat['VALUE'] == $arFields['PROPERTY_STATUS_VALUE']) {
					$result = true;
					break;
				}
		}
	}
	return $result;
}

/*********************************************************************
* Проверяет заполнение всех обязательных пунктов в карточке школы
* Возвращает true, если всё заполнено, в противном случае возвращает
* массив с названием незаполненных полей
*********************************************************************/
function testSchoolAttrib($schoolID = false) {
	$result = array(
		'PUNKT_FZ' => 'Пункт ФЗ',
		'STATUS' => 'Тип школы',
		'FULL_NAME' => 'Полное название ОО',
		'PHONE' => 'Телефон',
		'EMAIL' => 'E-mail',
		'DIR_FIO' => 'ФИО директора',
		'DIR_FIO_R' => 'ФИО директора (род.падеж)',
		'DIR_DOC' => 'Основание действия',
		'OTV_FIO' => 'Ответственный: ФИО',
		'OTV_DOLG' => 'Ответственный: должность',
		'OTV_PHONE' => 'Ответственный: телефон',
		'INN' => 'ИНН',
		'KPP' => 'КПП',
		'RASCH' => 'Расчётный счёт',
		'BANK' => 'Банк',
		'BIK' => 'БИК'
	);
	if ($schoolID && CModule::IncludeModule('iblock')) {
		$arFZ = get_school_status_spr();
		$arStatus = getSchoolTypeSpr();
		$arFields = array('IBLOCK_ID', 'ID');
		foreach ($result as $key => $value) $arFields[] = 'PROPERTY_'.$key;
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'ID' => $schoolID), false, false, $arFields);
		if ($arFields = $res->GetNext()) {
			// Проверка ФЗ
			foreach ($arFZ as $fz) {
				if ($fz['VALUE'] === $arFields['PROPERTY_PUNKT_FZ_VALUE']) {
					unset($result['PUNKT_FZ']);
					break;
				}
			}
			// Проверка статуса
			foreach ($arStatus as $stat) {
				if ($stat['VALUE'] == $arFields['PROPERTY_STATUS_VALUE']) {
					unset($result['STATUS']);
					break;
				}
			}
			// Проверка остальных полей
			foreach ($result as $key => $value) {
				if ($key == 'PUNKT_FZ' || $key == 'STATUS') continue;
				if (strlen(trim($arFields['PROPERTY_'.$key.'_VALUE'])) > 0) unset($result[$key]);
			}
		}
	}	
	if (count($result) == 0) $result = false;
	return $result;
}

/****************************************************************
* Синоним к get_obl_name() - возвращает название региона по коду
****************************************************************/
function getRegionName($id) {
	return (get_obl_name($id));
}

/***************************************
* Генерация пароля, длиной $len символов
***************************************/
function passwordGenerator($len) {
	$passSym = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
	$result = '';
	for ($i=0; $i<$len; $i++) {
		do {
			$c = substr($passSym, rand(0, 56), 1);
		} while (strpos($result, $c) !== false);
		$result .= $c;
	}
	return $result;
}

/****************************************************************
* Загрузка текущего каталога издательств в каталог инвентаризации
****************************************************************/
function refreshInventoryCatalog() {
	global $USER;
	if ($USER->IsAdmin() && CModule::IncludeModule('iblock')) {
		// Загружаем полный список литературы из каталогов издательств
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_BOOKS),
			false, false,
			array('IBLOCK_ID', 'ID', 'IBLOCK_SECTION_ID', 'NAME',
				'PROPERTY_FP_CODE',
				'PROPERTY_AUTHOR',
				'PROPERTY_CLASS',
				'PROPERTY_YEAR',
				'PROPERTY_ED_IZM',
				'PROPERTY_CODE_1C',
				'PROPERTY_UMK',
				'PROPERTY_SYSTEM',
                'PROPERTY_PRICE'
			)
		);
		while ($arFields = $res->GetNext()) {

			// Ищем книгу в каталоге инвентаризации
			$res2 = CIBlockElement::GetList(
				false,
				array(
					'IBLOCK_ID' => 24,
					'PROPERTY_IZD_ID' => $arFields['IBLOCK_SECTION_ID'],
					'NAME' => $arFields['~NAME'],
					'PROPERTY_AUTHOR' => $arFields['~PROPERTY_AUTHOR_VALUE'],
					'PROPERTY_CLASS' => $arFields['PROPERTY_CLASS_VALUE'],
					'PROPERTY_YEAR' => $arFields['PROPERTY_YEAR_VALUE'],
					'PROPERTY_CODE_1C' => $arFields['PROPERTY_CODE_1C_VALUE']
				),
				false, false,
				array('IBLOCK_ID', 'ID')
			);

			// Если еще нет - добавляем
			if (!($arFields2 = $res2->GetNext())) {

			    $price = CIBlockElement::GetList(
                    Array("SORT" => "ASC"),
                    Array(
                        "IBLOCK_ID" => 34,
                        "NAME" => $arFields["ID"]
                    ),
                    false,
                    false,
                    Array(
                        "PROPERTY_PRICE"
                    )
                )->Fetch();

			    $price = $price["PROPERTY_PRICE_VALUE"];

				$el = new CIBlockElement;

				$arNew = Array(
					'MODIFIED_BY'    => $USER->GetID(),
					'IBLOCK_SECTION_ID' => false,
					'IBLOCK_ID'      => 24,
					'NAME'           => $arFields['~NAME'],
					'ACTIVE'         => 'Y',
					'PROPERTY_VALUES'=> array(
						'FP_CODE' => $arFields['PROPERTY_FP_CODE_VALUE'],
						'AUTHOR' => $arFields['~PROPERTY_AUTHOR_VALUE'],
						'CLASS' => $arFields['PROPERTY_CLASS_VALUE'],
						'YEAR' => $arFields['PROPERTY_YEAR_VALUE'],
						'ED_IZM' => $arFields['PROPERTY_ED_IZM_VALUE'],
						'CODE_1C' => $arFields['PROPERTY_CODE_1C_VALUE'],
						'UMK' => $arFields['PROPERTY_UMK_VALUE'],
						'SYSTEM' => $arFields['PROPERTY_SYSTEM_VALUE'],
						'IZD_ID' => $arFields['IBLOCK_SECTION_ID'],
						'NOT_VERIFY' => 'Y',
                        'PRICE' => $price
					)
				);

				/**/

// test_print($arNew);

				$newID = $el->Add($arNew);
				if (!$newID) echo 'Ошибка: ' . $el->LAST_ERROR . '<br>';
			}

		}
	}
}

/*******************************************************************
* Возвращает массив с информацией о книге из каталога инвентаризации
* Если в $fieldName задать имя элемента массива с информацией -
* будет возвращено только это значение
*******************************************************************/
function getInvBookInfo($bookID, $fieldName = false) {
	$result = false;
	if ($bookID && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 24, 'ID' => $bookID),
			false, false,
			array(
				'IBLOCK_ID', 'ID',
				'NAME',
				'PROPERTY_FP_CODE',
				'PROPERTY_IZD_ID',
				'PROPERTY_AUTHOR',
				'PROPERTY_CLASS',
				'PROPERTY_YEAR',
				'PROPERTY_UMK',
				'PROPERTY_SYSTEM',
				'PROPERTY_NOT_VERIFY',
				'PROPERTY_WHO_ADD',
				'PROPERTY_CODE_1C'
			)
		);
		if ($arFields = $res->GetNext()) {
			if ($fieldName) {
				if ($fieldName == 'NAME')
					$result = $arFields['NAME'];
				else
					$result = $arFields['PROPERTY_' . $fieldName . '_VALUE'];
			} else
				$result = array(
					'NAME' => $arFields['NAME'],
					'FP_CODE' => $arFields['PROPERTY_FP_CODE_VALUE'],
					'IZD_ID' => $arFields['PROPERTY_IZD_ID_VALUE'],
					'AUTHOR' => $arFields['PROPERTY_AUTHOR_VALUE'],
					'CLASS' => $arFields['PROPERTY_CLASS_VALUE'],
					'YEAR' => $arFields['PROPERTY_YEAR_VALUE'],
					'UMK' => $arFields['PROPERTY_UMK_VALUE'],
					'SYSTEM' => $arFields['PROPERTY_SYSTEM_VALUE'],
					'NOT_VERIFY' => $arFields['PROPERTY_NOT_VERIFY_VALUE'],
					'WHO_ADD' => $arFieelds['PROPERTY_WHO_ADD_VALUE'],
					'CODE_1C' => $arFields['PROPERTY_CODE_1C_VALUE']
				);
		}
	}
	return $result;
}

/********************************************************
* Возвращает массив с информацией об инвентаризации по id
********************************************************/
function getInvInfo($invID, $fieldName) {
	$result = false;
	if ($invID && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => 25, 'ID' => $invID),
			false, false,
			array(
				'IBLOCK_ID', 'ID',
				'PROPERTY_REGION_ID',
				'PROPERTY_SCHOOL_ID',
				'PROPERTY_BOOK_ID',
				'PROPERTY_YEAR_PURCHASE',
				'PROPERTY_COUNT',
				'PROPERTY_REM'
			)
		);
		if ($arFields = $res->GetNext()) {
			if ($fieldName) {
				$result = $arFields['PROPERTY_' . $fieldName . '_VALUE'];
			} else
				$result = array(
					'REGION_ID' => $arFields['PROPERTY_REGION_ID_VALUE'],
					'SCHOOL_ID' => $arFields['PROPERTY_SCHOOL_ID_VALUE'],
					'BOOK_ID' => $arFields['PROPERTY_BOOK_ID_VALUE'],
					'YEAR_PURCHASE' => $arFields['PROPERTY_YEAR_PURCHASE_VALUE'],
					'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
					'REM' => $arFields['PROPERTY_REM_VALUE']
				);
		}
	}
	return $result;
}

/*************************************
* Возвращает массив с настройками АИС
*************************************/
function getMainOptions() {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_OPTIONS),
			false, array('nTopCount' => 1),
			array('IBLOCK_ID', 'ID', 'PROPERTY_SHOW_PRICE', 'PROPERTY_SHOW_REPORT_PRICE')
		);
		if ($arFields = $res->GetNext()) {
			$result = array(
				'SHOW_PRICE' => ($arFields['PROPERTY_SHOW_PRICE_VALUE'] == 'Y'),
				'SHOW_REPORT_PRICE' => ($arFields['PROPERTY_SHOW_REPORT_PRICE_VALUE'] == 'Y')
			);
		}
	}
	return $result;
}

/***************** Для новой версии *******************/

/*************************************************************************************
* Возвращает информацию о регионах
* Если задан $regID - возвращает информацию по указанному региону ID -> array(NAME)
* Если $regID не задан - вернет массив со всеми регионами
**************************************************************************************/
function getRegionInfo($regID = false) {
	$result = false;

	$arFilter = array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => false);
	if ($regID) $arFilter['ID'] = $regID;

	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetList(false, $arFilter, false, array('IBLOCK_ID', 'ID', 'NAME'));
		while ($arFields = $res->GetNext()) {
			if ($regID)
				$result = array('NAME' => $arFields['NAME']);
			else
				$result[$arFields['ID']] = array('NAME' => $arFields['NAME']);
		}
	}
	return $result;
}

/****************************************************************
* Возвращает строку с информацией о пользователе для шапки сайта
****************************************************************/
function getUserString() {
	global $USER;
	$result = false;

	if ($USER->IsAuthorized()) {
		$userID = $USER->GetID();
		$res = CUser::GetByID($userID);
		$arUser = $res->Fetch();
		$arGroup = $USER->GetUserGroup($userID);

		$fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];

		if (in_array(1, $arGroup)) $result = $fio . '(Технический администратор)';
		elseif (in_array(6, $arGroup)) $result = $fio . '(Администратор АИС)';
		elseif (in_array(9, $arGroup)) $result = $fio . '(Оператор АИС)';
		elseif (in_array(7, $arGroup)) {
			$arMun = get_mun_list($USER->GetID());
			$result = 'Администратор муниципалитета (' . reset($arMun) . ')';
		} elseif (in_array(8, $arGroup)) {
			$arSchool = get_school_info(getSchoolID($userID));
			$result = $arSchool['NAME'] . ' (' . get_izd_name($arSchool['MUN']) . ')';
		}
	}
	return $result;
}

/****************************************************
* Возвращает опции из инфоблока "Настройки" (32)
* Если параметр не задан, возвращается массив
* со всеми настройками. Если задан $key - возвращает
* значение соответствующего ключа
****************************************************/
function getOptions($key = false) {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$arFilter = array('IBLOCK_ID' => IB_OPTIONS);
		if ($key) $arFilter['PROPERTY_OPTION_KEY'] = $key;
		$res = CIBlockElement::GetList(false, $arFilter, false, false, array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_OPTION_KEY', 'PROPERTY_OPTION_ACTIVE', 'PROPERTY_OPTION_ARRAY', 'PROPERTY_OPTION_TYPE'));
		while ($arFields = $res->Fetch()) {
			if ($key && strtoupper($arFields['PROPERTY_OPTION_KEY_VALUE']) == strtoupper($key)) {
				switch ($arFields['PROPERTY_OPTION_TYPE_VALUE']) {
					case 'ot_yn':
						$result = ($arFields['PROPERTY_OPTION_ACTIVE_VALUE'] == 'Y');
						break;
					case 'ot_array':
						$result = $arFields['PROPERTY_OPTION_ARRAY_VALUE'];
						break;
				}
				break;
			} else {
				switch ($arFields['PROPERTY_OPTION_TYPE_VALUE']) {
					case 'ot_yn':
						$value = ($arFields['PROPERTY_OPTION_ACTIVE_VALUE'] == 'Y');
						break;
					case 'ot_array':
						$value = $arFields['PROPERTY_OPTION_ARRAY_VALUE'];
						break;
				}
				$result[$arFields['PROPERTY_OPTION_KEY_VALUE']] = array(
					'ID' => $arFields['ID'],
					'TYPE' => $arFields['PROPERTY_OPTION_TYPE_VALUE'],
					'TITLE' => $arFields['NAME'],
					'VALUE' => $value
				);
			}
		}
	}
	return $result;
}

/***************************************
* Возвращает список издательств с кодами
***************************************/
function get_izd_list() { return getIzdList(); }
function getIzdList() {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_BOOKS, 'ACTIVE' => 'Y', '!NAME' => '-'), false, array('IBLOCK_ID', 'ID', 'NAME'));
		while ($arFields = $res->GetNext())
			$result[$arFields['ID']] = $arFields['NAME'];
	}
	return $result;
}

/***********************************************
* Возвращает информацию об учебнике из каталога
***********************************************/
function getBookInfo($bookID = false, $alwaysArray = false) {
	$result = false;

	if (is_array($bookID) && count($bookID) == 0) $bookID = false;

	if ($bookID !== false && CModule::IncludeModule('iblock')) {

		if (!is_array($bookID)) $bookID = array($bookID);

		// Готовим список свойств для выборки
		$arFieldsList = array('IBLOCK_ID', 'ID', 'IBLOCK_SECTION_ID', 'NAME');
		$res = CIBlockProperty::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_BOOKS));
		while ($arFields = $res->Fetch()) $arFieldsList[] = 'PROPERTY_'.$arFields['CODE'];

		// Получаем информацию об учебнике
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_BOOKS, 'ID' => $bookID), false, false, $arFieldsList);

		if (count($bookID) == 1) {
			$arFields = $res->GetNext();
			if ($alwaysArray)
				$result = array($arFields['ID'] => $arFields);
			else
				$result = $arFields;
		} else {
			$result = array();
			while ($arFields = $res->GetNext()) $result[$arFields['ID']] = $arFields;
		}
	}
	return $result;
}

/****************************************************************
* Проверка на принадлежность текущего юзера к указанной группе.
* Можно задать массивом - проверяется принадлежность по ИЛИ
* !!! Функция для совместимости !!!
* Если не принадлежит, или юзер не авторизован - возвращаем false
****************************************************************/
function is_user_in_group($groupID) { return isUserInGroup($groupID); }
function isUserInGroup($groupID) {
//	$result = false;
//	global $USER;
//	if ($USER->IsAuthorized()) {
//		$arUserGroup = CUser::GetUserGroup($USER->getID());
//		$result = in_array($groupID, $arUserGroup);
//	}
	if (!is_array($groupID)) $groupID = array($groupID);
	return(CSite::InGroup($groupID));
}

/****************************************************
* Возвращает список регионов (сортировка sort + name)
****************************************************/
function getRegionList() {
	$result = false;
	if (CModule::IncludeModule('iblock')) {
		$res = CIBlockSection::GetList(array('sort' => 'asc', 'name' => 'asc'), array('IBLOCK_ID' => IB_STRUCTURE, 'SECTION_ID' => false, 'ACTIVE' => 'Y'), false, array('IBLOCK_ID', 'ID', 'NAME'));
		$result = array();
		while ($arFields = $res->GetNext()) $result[$arFields['ID']] = $arFields['~NAME'];
	}
	return $result;
}

/**************************************************
* Возвращает ID региона, к которому относится юзер
**************************************************/
function getUserRegion($userID = 0) {
	global $USER;
	if (!$userID) $userID = $USER->GetID();
	$res = CUser::GetByID($userID);
	$arUser = $res->Fetch();
	return ($arUser['UF_REGION']);
}

/**************************************
* Определение ID региона по хосту сайта
**************************************/
function getHostRegion() {
	$result = false;
	$host = strtoupper($_SERVER['HTTP_HOST']);
	$arRegions = array(
		array('HOST' => 'BOOK-70.OBLCIT.RU', 'ID' => 123),
		array('HOST' => 'BOOK-42.OBLCIT.RU', 'ID' => 144),
		array('HOST' => 'BOOK.OBLCIT.RU', 'ID' => 56)
	);
	foreach ($arRegions as $arItem) {
		if (strpos($host, $arItem['HOST']) !== false) {
			$result = $arItem['ID'];
			break;
		}
	}
	return $result;
}

/********************************************************
* Определение региона в зависимости от статуса посетителя
********************************************************/
function getRegionFilter() {
	global $USER;
	$result = false;
	if ($USER->IsAuthorized())
		$result = getUserRegion();
//	else
//		$result = getHostRegion();
	return $result;
}

/****************************************************************************
* Возвращает данные активного отчетного периода системы для активного региона
****************************************************************************/
function getWorkPeriod() {
	$arPeriod = false;
	$regionID = getRegionFilter();
	if ($regionID && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(array('ID' => 'desc'), array('IBLOCK_ID' => IB_WORKING_PERIOD, 'PROPERTY_REGION' => $regionID, '!PROPERTY_ARCHIVE' => 'Y'), false, false, array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_ARCHIVE'));
		if ($arFields = $res->GetNext())
			$arPeriod = array(
				'ID' => $arFields['ID'],
				'NAME' => $arFields['~NAME'],
				'ARCHIVE' => (strtoupper($arFields['PROPERTY_ARCHIVE_VALUE']) == 'Y')
			);
	}
	return ($arPeriod);
}

/********************************************************************************************************
* Возвращает массив со списком периодов для текущего региона, имеющихся в базе. Ключ массива - ID периода
* Если регион не определяется (не выполнен вход или не определен домен - вернет false
********************************************************************************************************/
function getPeriodList() {
	$arResult = false;
	$regionID = getRegionFilter();
	if ($regionID && CModule::IncludeModule('iblock')) {
		$arResult = array();
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_WORKING_PERIOD, 'PROPERTY_REGION' => $regionID), false, false, array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_ARCHIVE'));
		while ($arFields = $res->GetNext())
			$arResult[$arFields['ID']] = array(
				'NAME' => $arFields['~NAME'],
				'ARCHIVE' => (strtoupper($arFields['PROPERTY_ARCHIVE_VALUE']) == 'Y')
			);
	}
	return ($arResult);
}

/***************************************************************************
* Проверка файла на перенос строки в Excel и замена одиночного x0A на пробел
***************************************************************************/
function csvTestFile($filename) {
	$flag = false;

	$tempName = tempnam($_SERVER['DOCUMENT_ROOT'].'/upload/tmp', 'bok');
	$fp_in = fopen($filename, 'r');
	$fp_out = fopen($tempName, 'w');

	$prev_c = 0;

	while (!feof($fp_in)) {
		$sym = fgetc($fp_in);
		$c = ord($sym);
		if ($c == 10 && $prev_c != 13) {
			$sym = ' ';
			$flag = true;
		}
		$prev_c = $c;
		fwrite($fp_out, $sym);
	}
	fclose($fp_in);
	fclose($fp_out);

	if ($flag) { // Если были замены - заменяем исходный файл обработанным
		unlink($filename);
		rename($tempName, $filename);
	}

	if (file_exists($tempName)) unlink($tempName);
}

/*****************************************************************************
* Проверка строки на корректный номер учебника по ФП (шесть чисел через точку)
* Возвращает номер ФП или false в случае ошибки
*****************************************************************************/
function testFPNumber($str) {
	$result = false;
	$fError = false;
	$arTemp = explode(".", $str);
	if ((count($arTemp) == 6) || ((count($arTemp) == 7) && (strlen(trim($arTemp[6])) == 0))) {
		$strTemp = '';
		for ($i=0; $i<6; $i++) {
			if (intval(preg_replace( '/[^[:print:]]/', '',$arTemp[$i])) > 0) {
				$strTemp .= ($strTemp ? '.' : '') . intval($arTemp[$i]);
			} else {
				$fError = true;
				break;
			}
		}
		if (!$fError) $result = $strTemp;
	}
	return $result;
}
function test_fp_number($str) { return testFPNumber($str); }

/***************************************
* Удаляем закавыченность при импорте CSV
***************************************/
function quotesClear($str) {
	$result = trim($str);
	// Если строка начинается с ", значит раскавычиваем
	if (substr($result, 0, 1) == '"') {
		$result = substr($result, 1, strlen($result)-2);
		$result = str_replace('""', '"', $result);
	}
	return $result;
}
function quotes_clear($str) { return quotesClear($str); }

/***************************************************************************
* Разбивка импортируемой строки CSV (корректно обрабатывает точку-с-запятой)
***************************************************************************/
function csvExplode($str) {
	$arDelim = array('~', '`', '@', '#', '$', '^', '&', '<', '>', '=', '+', '/', '[', ']', '{', '}');
	$arReplaceDelim = array();

	//Проверяем строку на попадание ; в кавычки
	$need_correct = false;
	$fQuoteOpen = false;
	for ($i=0; $i<strlen($str); $i++) {
		$c = substr($str, $i, 1);
		if ($c == '"') {
			$fQuoteOpen = !$fQuoteOpen;
		} elseif ($c == ';') {
			if ($fQuoteOpen)
				$need_correct = true;
			else
				$arReplaceDelim[] = $i;
		}
	}

	// Если коррекция нужна - корректируем, если нет - разбиваем по ;
	if ($need_correct) {
		// Ищем символ-разделитель
		$delim = ';';
		foreach ($arDelim as $value) {
			if (strpos($str, $value) === false) {
				$delim = $value;
				break;
			}
		}

		// Заменяем "правильные" ; на новый разделитель
		$newStr = $str;
		foreach ($arReplaceDelim as $value)
			$newStr = substr($newStr, 0, $value) . $delim . substr($newStr, $value+1);
	} else {
		$delim = ';';
		$newStr = $str;
	}

	return(explode($delim, $newStr));
}
function csv_explode($str) { return csvExplode($str); }

/**********************************************************************************************************************
* Поиск текущей цены в каталоге
* Параметры:
*    $bookID - ID учебника из каталога. Можно задать массив ID - тогда возвращается массив цен в виде BOOK_ID => PRICE
*    $periodID - код периода. Если не задан (false) - то берется текущий период для региона
*    Возвращается последняя по дате активации цена
**********************************************************************************************************************/
function getPrice($bookID = false, $periodID = false, $alwaysArray = false) {
	$result = false;

	if (is_array($bookID) && count($bookID) == 0) $bookID = false;

	if ($bookID !== false && CModule::IncludeModule('iblock')) {

		if (!$periodID) {
			$arTemp = getWorkPeriod();
			$periodID = $arTemp['ID'];
			unset($arTemp);
		}

		if (!is_array($bookID)) $bookID = array($bookID);

		// запрашиваем цены

		$arPrice = array();

		$res = CIBlockElement::GetList(
			array('PROPERTY_START' => 'desc'),
			array('IBLOCK_ID' => IB_PRICE, 'PROPERTY_BOOK' => $bookID, 'PROPERTY_PERIOD' => $periodID),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_PRICE', 'PROPERTY_BOOK')
		);

		while ($arFields = $res->Fetch()) {
			if (!isset($arPrice[$arFields['PROPERTY_BOOK_VALUE']]))
				$arPrice[$arFields['PROPERTY_BOOK_VALUE']] = $arFields['PROPERTY_PRICE_VALUE'];
		}

		if (count($arPrice) > 0) {
			if (count($arPrice) == 1) {
				if ($alwaysArray)
					$result = $arPrice;
				else {
					reset($arPrice);
					$result = current($arPrice);
				}
			} else
				$result = $arPrice;
		}
	}
	return $result;
}

/*************************************************************************
* Возвращает следующий номер договора указанной школы, увеличивая счетчик
*************************************************************************/
function getNextOrderNum($schoolID = false) {
	$result = false;
	if ($schoolID && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_SCHOOLS, 'ID' => $schoolID), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ORDER_MAX'));
		if ($arFields = $res->getNext()) {
			$result = intval($arFields['PROPERTY_ORDER_MAX_VALUE']) + 1;
			CIBlockElement::SetPropertyValueCode($arFields['ID'], 'ORDER_MAX', $result);
		}
	}
	return $result;
}

/**********************************************
* Возвращает номер (название) заказа по его id
**********************************************/
function getOrderNum($orderID = false) {
	$result = false;
	if ($orderID && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => IB_ORDERS_LIST, 'ID' => $orderID), false, false, array('IBLOCK_ID', 'ID', 'NAME'));
		if ($arFields = $res->Fetch()) $result = $arFields['NAME'];
	}
	return $result;
}

/*************************************************************************
* Функция возвращает сумму НДС (процент НДС задается в $nds) в сумме $sum
*************************************************************************/
function getNdsSum($sum, $nds) {
	return $sum * $nds / (100 + $nds);
}

/*****************************************************************
* Функция проверяет наличие заказа в обработке для указаных
* школы $schoolID и издательства $izdID в текущем рабочем периоде
* Возвращает true - если заказов нет в обработке и false - если
* заказы есть и создавать новые нельзя
******************************************************************/
function canMakeOrder($schoolID = false, $izdID = false) {
	$result = true;
	if ($schoolID && $izdID && CModule::IncludeModule('iblock')) {

		$arPeriod = getWorkPeriod();

		$cnt = CIBlockElement::GetList(
			false,
			array(
				'IBLOCK_ID' => IB_ORDERS_LIST,
				'PROPERTY_PERIOD' => $arPeriod['ID'],
				'PROPERTY_SCHOOL_ID' => $schoolID,
				'PROPERTY_IZD_ID' => $izdID,
				'PROPERTY_STATUS' => array('osdocs', 'oscheck')
			),
			array(), false,
			array('IBLOCK_ID', 'ID')
		);

		$result = ($cnt == 0);
	}
	return $result;
}

/************************************************************
* Получение пути к файлу шаблона свода для издательства
* Если пропущен период - используется текущий
* Если пропущен регион - используется регион активного юзера
************************************************************/
function getSvodTemplate($izdID = false, $periodID = false, $regionID = false) {
	$result = false;
	if ($izdID && CModule::IncludeModule('iblock')) {
		if (!$periodID) {
			$arTemp = getWorkPeriod();
			$periodID = $arTemp['ID'];
		}

		if (!$regionID) $regionID = getRegionFilter();

		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_IZD_FILES, 'PROPERTY_REGION' => $regionID, 'PROPERTY_PERIOD' => $periodID, 'PROPERTY_IZD_ID' => $izdID, 'PROPERTY_TYPE' => 'svod'),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_FILE')
		);
		if ($arFields = $res->Fetch())
			$result = $_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($arFields['PROPERTY_FILE_VALUE']);
	}
	return $result;
}

/******************************************************
* Возвращает список подразделов каталогов издательства
******************************************************/
function getSubsections($izdID = false) {
	$result = false;
	if ($izdID && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			array('sort' => 'asc'),
			array('IBLOCK_ID' => IB_CATALOG_SUBSECTION, 'PROPERTY_IZD' => $izdID),
			false, false,
			array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_PAGE')
		);
		while ($arFields = $res->GetNext())
			$result[] = array(
				'IZD_ID' => $izdID,
				'SUB_ID' => $arFields['ID'],
				'NAME' => $arFields['~NAME'],
				'PAGE' => $arFields['PROPERTY_PAGE_VALUE']
			);
	}
	return $result;
}

/*********************************************
* Возвращает массив соответствия 1C_CODE - ID
*********************************************/
function getCodeByID($arBookID = false) {
	$result = false;
	if (is_array($arBookID) && CModule::IncludeModule('iblock')) {
		$res = CIBlockElement::GetList(
			false,
			array('IBLOCK_ID' => IB_BOOKS, 'ID' => $arBookID),
			false, false,
			array('IBLOCK_ID', 'ID', 'PROPERTY_CODE_1C')
		);
		while ($arFields = $res->Fetch())
			$result[$arFields['PROPERTY_CODE_1C_VALUE']] = $arFields['ID'];
	}
	return $result;
}

function file_force_download($file) {
    if (file_exists($file)) {
        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
        // если этого не сделать файл будет читаться в память полностью!
        if (ob_get_level()) {
            ob_end_clean();
        }
        // заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        // читаем файл и отправляем его пользователю
        readfile($file);
        exit;
    }
}

?>