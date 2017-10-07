<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru" dir="ltr">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link href="/include/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?=SITE_TEMPLATE_PATH.'/myriad/myriad.css'?>" rel="stylesheet">
	<link rel="stylesheet" href="/include/bootstrap_validator/css/bootstrapValidator.min.css"/>
	<?$APPLICATION->ShowHead();?>
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="icon" href="/favicon.ico">
	<!--[if lte IE 6]>
	<style type="text/css">
		div.product-overlay {
			background-image: none;
			filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>images/product-overlay.png', sizingMethod = 'crop');
		}
	</style>
	<![endif]-->
	<title><?$APPLICATION->ShowTitle();?></title>
</head>
<body>
	<?
		global $USER;
		require_once($_SERVER["DOCUMENT_ROOT"] . '/include/bav.php');
		if (isset($_GET['report_mode'])) set_report_mode($_GET['report_mode']);

		$PERIOD_INFO = getWorkPeriod();
		$USER_SCHOOL_ID = get_schoolID($USER->GetID());

		if ($APPLICATION->GetDirProperty('SECTION') != 'ORDERS') unset($_SESSION['ORDERS_LIST_PAGE']);

		if ($REGION_ID = getRegionFilter())	$REGION_INFO = getRegionInfo($REGION_ID); else $REGION_INFO = false;

	?>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>

	<?// ***** Главное меню ***** ?>
 <?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"horizontal_multilevel_new",
	Array(
		"ALLOW_MULTI_SELECT" => "N",
		"CHILD_MENU_TYPE" => "top_second",
		"DELAY" => "N",
		"MAX_LEVEL" => "2",
		"MENU_CACHE_GET_VARS" => array(""),
		"MENU_CACHE_TIME" => "3600",
		"MENU_CACHE_TYPE" => "N",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"ROOT_MENU_TYPE" => "top",
		"USE_EXT" => "Y"
	)
);?>
	<?// ***** Главное меню (END) ***** ?>

	<div class="container container-main">
		<div class="row">

			<?// ***** Левая колонка ***** ?>
			<div class="col-xs-3">

				<?// ***** Логотип ***** ?>
				<div class="row"><div class="col-xs-12 logo"><a href="/" title="На главную"><img src="<?=SITE_TEMPLATE_PATH.'/images/logo1.png'?>" width="230" height="175"></a></div></div>
				<?// ***** Логотип (END) ***** ?>

				<?// --------- Указатель рабочего периода --------- ?>
				<?if (is_array($PERIOD_INFO)):?>
					<div class="panel panel-default">
						<div class="panel-heading panel-title">Рабочий период</div>
						<div class="panel-body period-name">
							<?=$PERIOD_INFO['NAME']?>
						</div>
					</div>
				<?endif;?>
				<?// --------- Указатель рабочего периода (END) --------- ?>

				<?// --------- Личный кабинет --------- ?>
				<?if (is_user_in_group(8) && $USER_SCHOOL_ID):?>
					<div class="panel panel-default personal-cabinet">
						<div class="panel-heading"><div class="panel-title">Личный кабинет</div></div>
						<div class="panel-body">
							<div class="row cart-data-first">
								<div class="col-xs-5">В корзине:</div>
								<div class="col-xs-7 text-right" id="cart_sum">0&nbsp;руб.</div>
							</div>
							<div class="row cart-data">
								<div class="col-xs-12 text-right" id="cart_count">0&nbsp;ед.</div>
							</div>
							<div class="row cart-data-first">
								<div class="col-xs-5">В отчёте:</div>
								<div class="col-xs-7 text-right" id="report_sum">0&nbsp;руб.</div>
							</div>
							<div class="row cart-data">
								<div class="col-xs-12 text-right" id="report_count">0&nbsp;ед.</div>
							</div>
							<div class="row">
								<div class="col-xs-12 text-center">
									<div class="btn-group">
										<?if (get_report_mode()):?>
											<?if (is_report_enabled()):?>
												<a href="/reports/basket/" class="btn btn-primary" title="Отчёт"><span class="glyphicon glyphicon-stats"></span> </a>
											<?endif;?>
										<?else:?>
											<a href="/orders/basket/" class="btn btn-primary" title="Корзина"><span class="glyphicon glyphicon-shopping-cart"></span> </a>
										<?endif;?>

										<?if (get_report_mode()):?>
											<a href="/reports/" class="btn btn-primary" title="Отчёты"><span class="glyphicon glyphicon-folder-open"></span> </a>
										<?else:?>
											<a href="/orders/" class="btn btn-primary" title="Заказы"><span class="glyphicon glyphicon-folder-open"></span> </a>
										<?endif;?>

										<?if (false):?>
											<a href="/inventory/" class="btn btn-primary" title="Инвентаризация"><span class="glyphicon glyphicon-book"></span> </a>
										<?endif;?>

										<a href="/schools/<?=$USER_SCHOOL_ID;?>/" class="btn btn-primary" title="Реквизиты школы"><span class="glyphicon glyphicon-list-alt"></span> </a>
									</div>
								</div>
							</div>

							<div class="row report-mode-button">
								<div class="col-cs-12 text-center">
									<?if (get_report_mode()):?>
										<a href="/?report_mode=off" class="btn btn-info btn-sm" title="Включить режим заказа">Включён режим отчета!</a>
									<?else:?>
										<a href="/?report_mode=on" class="btn btn-success btn-sm" title="Включить режим отчёта">Включён режим заказа!</a>
									<?endif;?>
								</div>
							</div>

							<?if (get_report_mode() && !is_report_enabled()):?>
								<div class="alert alert-info personal-cabinet-alert text-center" role="alert">Ваш отчёт сформирован!</div>
							<?endif;?>

						</div>
					</div>
				<?endif;?>
				<?// --------- Личный кабинет (END) --------- ?>

				<?// --------- Ошибки в реквизитах --------- ?>
				<?if (is_user_in_group(8) && $USER_SCHOOL_ID):?>
					<?$arErr = testSchoolAttrib($USER_SCHOOL_ID);?>
					<?if (is_array($arErr)):?>
						<div class="panel panel-danger error-list">
							<div class="panel-heading"><div class="panel-title">Ошибки в реквизитах!</div></div>
							<div class="panel-body">
								<div class="error-list-text">
									В реквизитах не заполнены <b>обязательные</b> поля:
								</div>
								<ul>
								<?foreach ($arErr as $key => $value):?>
									<li><?=$value;?></li>
								<?endforeach;?>
								</ul>
								<div class="row"><div class="col-xs-12 text-right">
									<button type="button" class="btn btn-primary btn-sm" onClick="document.location.href='/schools/<?=$USER_SCHOOL_ID;?>/'"><span class="glyphicon glyphicon-list-alt"></span> Реквизиты</button>
								</div></div>
							</div>
						</div>
					<?endif;?>
				<?endif;?>
				<?// --------- Ошибки в реквизитах (END) --------- ?>

				<?// --------- Меню издательств --------- ?>
				<?if (getOptions('SHOW_CATALOG_NOUSER') || $USER->IsAuthorized()):?>
					<div class="panel panel-default">
						<div class="panel-heading"><div class="panel-title">Издательства</div></div>
						<div class="panel-body">
							<?$APPLICATION->IncludeComponent(
								"bitrix:menu",
								"left",
								array(
									"ROOT_MENU_TYPE" => "left",
									"MENU_CACHE_TYPE" => "N",
									"MENU_CACHE_TIME" => "36000000",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"MENU_CACHE_GET_VARS" => array(
									),
									"MAX_LEVEL" => "1",
									"CHILD_MENU_TYPE" => "left",
									"USE_EXT" => "Y",
									"ALLOW_MULTI_SELECT" => "N",
									"DELAY" => "N"
								),
								false,
								array(
									"ACTIVE_COMPONENT" => "Y"
								)
							);?>
						</div>
					</div>
				<?endif;?>
				<?// --------- Меню издательств (END) --------- ?>

			</div>
			<?// ***** Левая колонка (END) ***** ?>

			<?// ***** Основная колонка ***** ?>
			<div class="col-xs-9">

				<?// ***** Шапка ***** ?>
				<div class="row"><div class="col-xs-12 site-title text-right">Заказ учебников для образовательных организаций</div></div>
				<div class="row"><div class="col-xs-12 site-title-2 text-right">
					<?if ($REGION_ID):?><?=$REGION_INFO['NAME']?><?endif;?>
				</div></div>
				<?if ($userStr = getUserString()):?>
					<div class="row"><div class="col-xs-12 site-title-3 text-right"><?=$userStr?></div></div>
				<?endif;?>
				<?// ***** Шапка (END) ***** ?>

				<hr>
