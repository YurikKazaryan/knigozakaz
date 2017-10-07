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

<?
	// Обрабатываем сохранение данных из формы редактировани данных школы
	if (($_POST['MODE'] == 'INFO') && ($_POST['btnAction'] == 'SAVE')) {
		if (is_user_in_group(8) || is_user_in_group(6) || is_user_in_group(7) || is_user_in_group(9)) { // Сохраняем данные школьного админа

			$el = new CIBlockElement;
			$el->Update($_POST['ID'], array('NAME' => $_POST['NAME']));
			CIBlockElement::SetPropertyValuesEx($_POST['ID'], 10,
				array(
					'FULL_NAME' => $_POST['FULL_NAME'],
					'OGRN' => $_POST['OGRN'],
					'INN' => $_POST['INN'],
					'KPP' => $_POST['KPP'],
					'PFR' => $_POST['PFR'],
					'OKPO' => $_POST['OKPO'],
					'OKOGU' => $_POST['OKOGU'],
					'OKFS' => $_POST['OKFS'],
					'OKOPF' => $_POST['OKOPF'],
					'INDEX' => $_POST['INDEX'],
					'RAJON' => $_POST['RAJON'],
					'PUNKT' => $_POST['PUNKT'],
					'RAJON_GORODA' => $_POST['RAJON_GORODA'],
					'ULICA' => $_POST['ULICA'],
					'DOM' => $_POST['DOM'],
					'PHONE' => $_POST['PHONE'],
					'EMAIL' => $_POST['EMAIL'],
					'RASCH' => $_POST['RASCH'],
					'BANK' => $_POST['BANK'],
					'BIK' => $_POST['BIK'],
					'DIR_FIO' => $_POST['DIR_FIO'],
					'DIR_FIO_R' => $_POST['DIR_FIO_R'],
					'OTV_DOLG' => $_POST['OTV_DOLG'],
					'DIR_DOC' => $_POST['DIR_DOC'],
					'OTV_FIO' => $_POST['OTV_FIO'],
					'OTV_PHONE' => $_POST['OTV_PHONE'],
					'LS' => $_POST['LS'],
					'PUNKT_FZ' => ($_POST['STATUS'] == '0' ? '' : $_POST['STATUS']),
					'STATUS' => ($_POST['TYPE'] == '0' ? '' : $_POST['TYPE']),
			));
			$result_state = 'info';
			$result_info = 'Изменения успешно сохранены!';
		} elseif (is_user_in_group(6)) { // Сохраняем данные админа АИС
			$el = new CIBlockElement;
			$el->Update($_POST['ID'], array('NAME' => $_POST['NAME']));
			CIBlockElement::SetPropertyValuesEx($_POST['ID'], 10, array('MUN' => $_POST['MUN'], 'OBLAST' => get_obl_id($_POST['MUN'])));
			$result_state = 'info';
			$result_info = 'Изменения успешно сохранены!';
		} else {
			$result_state = 'danger';
			$result_info = 'У Вас нет прав на изменение этой информации!';
		}
	} elseif (($_POST['MODE'] == 'INFO') && ($_POST['btnAction'] == 'CANCEL')) {
		$result_state = 'info';
		$result_info = 'Изменения не были сохранены...';
	} elseif ($_POST['MODE'] == 'PASS') { // Меняем пароль администратора школы
		// Проверяем пароль
		if (test_user_pass($_POST['PASS'])) {
			$user = new CUser;
			$res = $user->Update($_POST['USER_ID'], array('PASSWORD' => $_POST['NEWPASS'], 'CONFIRM_PASSWORD' => $_POST['RENEWPASS']));
			if ($res) {
				$result_state = 'info';
				$result_info = 'Изменения успешно сохранены!';
			} else {
				$result_state = 'danger';
				$result_info = 'Ошибка при сохранении изменений!';
			}
		}
	} elseif ($_POST['MODE'] == 'USER') { // Меняем ФИО администратора школы
		// Проверяем пароль
		if (test_user_pass($_POST['PASS'])) {
			$arName = explode(' ', trim($_POST['NAME']));
			$user = new CUser;
			$res = $user->Update($_POST['USER_ID'], array(
				'NAME' => count($arName) > 1 ? $arName[1] : $arName[0],
				'LAST_NAME' => count($arName) > 1 ? $arName[0] : ' ',
				'SECOND_NAME' => $arName[2] ? $arName[2] : ' '
			));
			if ($res) {
				$result_state = 'info';
				$result_info = 'Изменения успешно сохранены!';
			} else {
				$result_state = 'danger';
				$result_info = 'Ошибка при сохранении изменений!';
			}
		} else {
			$result_state = 'danger';
			$result_info = 'Вы ввели неверный пароль!';
		}
	} elseif ($_POST['MODE'] == 'NEWUSER') { // Добавление нового админа в школу
		// Проверяем пароль
		if (test_user_pass($_POST['PASS'])) {
			$arName = explode(' ', trim($_POST['NAME']));
			$user = new CUser;
			$new_id = $user->Add(array(
				"NAME"              => count($arName) > 1 ? $arName[1] : $arName[0],
				"LAST_NAME"         => count($arName) > 1 ? $arName[0] : ' ',
				"SECOND_NAME"		=> $arName[2] ? $arName[2] : ' ',
				"EMAIL"             => $_POST['EMAIL'],
				"LOGIN"             => $_POST['LOGIN'],
				"LID"               => "ru",
				"ACTIVE"            => "Y",
				"GROUP_ID"          => array(3, 4, 8),
				"PASSWORD"          => $_POST['NEWPASS'],
				"CONFIRM_PASSWORD"  => $_POST['RENEWPASS'],
				"UF_REGION"			=> getUserRegion()
			));
			if ($new_id) {
				// Прикрепляем пользователя к школе
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 10, 'ID' => $_POST['SCHOOL_ID']), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ADMIN'));
				if ($arFields = $res->GetNext()) {
					$arFields['PROPERTY_ADMIN_VALUE'][] = $new_id;
					CIBlockElement::SetPropertyValuesEx($_POST['SCHOOL_ID'], 10, array('ADMIN' => $arFields['PROPERTY_ADMIN_VALUE']));
					$result_state = 'info';
					$result_info = 'Пользователь успешно создан!';
				} else {
					CUser::Delete($new_id);
					$result_state = 'danger';
					$result_info = 'Ошибка параметров!';
				}
			} else {
				$result_state = 'danger';
				$result_info = 'Ошибка при создании пользователя!';
			}
		}
	} elseif ($_POST['MODE'] == 'DELETE') { // Удаление пользователя
		// Проверяем пароль
		if (test_user_pass($_POST['PASS'])) {
			// Удалить пользователя из системы
			if (CUser::Delete($_POST['USER_ID']) !== false) {
				// Удалить пользователя из школы
				$res = CIBlockElement::GetList(false, array('IBLOCK_ID' => 10, 'ID' => $_POST['SCHOOL_ID']), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_ADMIN'));
				if ($arFields = $res->GetNext()) {
					$arAdmins = $arFields['PROPERTY_ADMIN_VALUE'];
					$key = array_search($_POST['USER_ID'], $arAdmins);
					if ($key !== false) {
						unset($arAdmins[$key]);
						CIBlockElement::SetPropertyValuesEx($_POST['SCHOOL_ID'], 10, array('ADMIN' => $arAdmins));
					}
					$result_state = 'info';
					$result_info = 'Пользователь успешно удален!';
				} else {
					CUser::Delete($new_id);
					$result_state = 'danger';
					$result_info = 'Ошибка параметров!';
				}
			} else {
				CUser::Delete($new_id);
				$result_state = 'danger';
				$result_info = 'Ошибка параметров!';
			}
		}
	}
?>

<?if ($result_state):?>
	<div class="alert alert-<?=$result_state?> text-center" id="alert-box"><?=$result_info?></div>
	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout(function() { $('#alert-box').slideUp('slow') }, 3000);
		});
	</script>
<?endif;?>

<?$ElementID = $APPLICATION->IncludeComponent(
	"bitrix:news.detail",
	"",
	Array(
		"DISPLAY_DATE" => $arParams["DISPLAY_DATE"],
		"DISPLAY_NAME" => $arParams["DISPLAY_NAME"],
		"DISPLAY_PICTURE" => $arParams["DISPLAY_PICTURE"],
		"DISPLAY_PREVIEW_TEXT" => $arParams["DISPLAY_PREVIEW_TEXT"],
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"FIELD_CODE" => $arParams["DETAIL_FIELD_CODE"],
		"PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
		"DETAIL_URL"	=>	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
		"SECTION_URL"	=>	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"META_KEYWORDS" => $arParams["META_KEYWORDS"],
		"META_DESCRIPTION" => $arParams["META_DESCRIPTION"],
		"BROWSER_TITLE" => $arParams["BROWSER_TITLE"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"INCLUDE_IBLOCK_INTO_CHAIN" => $arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
		"ADD_SECTIONS_CHAIN" => $arParams["ADD_SECTIONS_CHAIN"],
		"ACTIVE_DATE_FORMAT" => $arParams["DETAIL_ACTIVE_DATE_FORMAT"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		"DISPLAY_TOP_PAGER" => $arParams["DETAIL_DISPLAY_TOP_PAGER"],
		"DISPLAY_BOTTOM_PAGER" => $arParams["DETAIL_DISPLAY_BOTTOM_PAGER"],
		"PAGER_TITLE" => $arParams["DETAIL_PAGER_TITLE"],
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => $arParams["DETAIL_PAGER_TEMPLATE"],
		"PAGER_SHOW_ALL" => $arParams["DETAIL_PAGER_SHOW_ALL"],
		"CHECK_DATES" => $arParams["CHECK_DATES"],
		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"IBLOCK_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["news"],
		"USE_SHARE" 			=> $arParams["USE_SHARE"],
		"SHARE_HIDE" 			=> $arParams["SHARE_HIDE"],
		"SHARE_TEMPLATE" 		=> $arParams["SHARE_TEMPLATE"],
		"SHARE_HANDLERS" 		=> $arParams["SHARE_HANDLERS"],
		"SHARE_SHORTEN_URL_LOGIN"	=> $arParams["SHARE_SHORTEN_URL_LOGIN"],
		"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
		"ADD_ELEMENT_CHAIN" => (isset($arParams["ADD_ELEMENT_CHAIN"]) ? $arParams["ADD_ELEMENT_CHAIN"] : '')
	),
	$component
);?>

<?if ($USER->GetID() == 1 || is_user_in_group(6) || is_user_in_group(7)):?>
	<p><a href="<?=$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["news"]?>"><?=GetMessage("T_NEWS_DETAIL_BACK")?></a></p>
<?endif;?>

<?if($arParams["USE_RATING"]=="Y" && $ElementID):?>
<?$APPLICATION->IncludeComponent(
	"bitrix:iblock.vote",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ELEMENT_ID" => $ElementID,
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	),
	$component
);?>
<?endif?>
<?if($arParams["USE_CATEGORIES"]=="Y" && $ElementID):
	global $arCategoryFilter;
	$obCache = new CPHPCache;
	$strCacheID = $componentPath.LANG.$arParams["IBLOCK_ID"].$ElementID.$arParams["CATEGORY_CODE"];
	if(($tzOffset = CTimeZone::GetOffset()) <> 0)
		$strCacheID .= "_".$tzOffset;
	if($arParams["CACHE_TYPE"] == "N" || $arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "N")
		$CACHE_TIME = 0;
	else
		$CACHE_TIME = $arParams["CACHE_TIME"];
	if($obCache->StartDataCache($CACHE_TIME, $strCacheID, $componentPath))
	{
		$rsProperties = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $ElementID, "sort", "asc", array("ACTIVE"=>"Y","CODE"=>$arParams["CATEGORY_CODE"]));
		$arCategoryFilter = array();
		while($arProperty = $rsProperties->Fetch())
		{
			if(is_array($arProperty["VALUE"]) && count($arProperty["VALUE"])>0)
			{
				foreach($arProperty["VALUE"] as $value)
					$arCategoryFilter[$value]=true;
			}
			elseif(!is_array($arProperty["VALUE"]) && strlen($arProperty["VALUE"])>0)
				$arCategoryFilter[$arProperty["VALUE"]]=true;
		}
		$obCache->EndDataCache($arCategoryFilter);
	}
	else
	{
		$arCategoryFilter = $obCache->GetVars();
	}
	if(count($arCategoryFilter)>0):
		$arCategoryFilter = array(
			"PROPERTY_".$arParams["CATEGORY_CODE"] => array_keys($arCategoryFilter),
			"!"."ID" => $ElementID,
		);
		?>
		<hr /><h3><?=GetMessage("CATEGORIES")?></h3>
		<?foreach($arParams["CATEGORY_IBLOCK"] as $iblock_id):?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				$arParams["CATEGORY_THEME_".$iblock_id],
				Array(
					"IBLOCK_ID" => $iblock_id,
					"NEWS_COUNT" => $arParams["CATEGORY_ITEMS_COUNT"],
					"SET_TITLE" => "N",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
					"FILTER_NAME" => "arCategoryFilter",
					"CACHE_FILTER" => "Y",
					"DISPLAY_TOP_PAGER" => "N",
					"DISPLAY_BOTTOM_PAGER" => "N",
				),
				$component
			);?>
		<?endforeach?>
	<?endif?>
<?endif?>
<?if($arParams["USE_REVIEW"]=="Y" && IsModuleInstalled("forum") && $ElementID):?>
<hr />
<?$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.reviews",
	"",
	Array(
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"FORUM_ID" => $arParams["FORUM_ID"],
		"URL_TEMPLATES_READ" => $arParams["URL_TEMPLATES_READ"],
		"SHOW_LINK_TO_FORUM" => $arParams["SHOW_LINK_TO_FORUM"],
		"DATE_TIME_FORMAT" => $arParams["DETAIL_ACTIVE_DATE_FORMAT"],
		"ELEMENT_ID" => $ElementID,
		"AJAX_POST" => $arParams["REVIEW_AJAX_POST"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"URL_TEMPLATES_DETAIL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
	),
	$component
);?>
<?endif?>
