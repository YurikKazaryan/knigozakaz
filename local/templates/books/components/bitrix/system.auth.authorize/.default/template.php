<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->SetTitle('Авторизация');?>

<h3 class="main-text-title">Авторизация</h3>

<div class="bx-auth">

	<?if($arResult["AUTH_SERVICES"]):?>
		<div class="bx-auth-title"><?=GetMessage("AUTH_TITLE")?></div>
	<?endif?>

	<?MyShowMessage($arParams["~AUTH_RESULT"]);?>
	<?MyShowMessage($arResult['ERROR_MESSAGE']);?>

	<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">

		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="AUTH" />

		<?if (strlen($arResult["BACKURL"]) > 0):?>
			<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
		<?endif?>

		<?foreach ($arResult["POST"] as $key => $value):?>
			<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
		<?endforeach?>

		<div class="row">
			<div class="col-xs-5">
				<div class="form-group">
					<label for="user_login"><?=GetMessage("AUTH_LOGIN")?></label>
					<input class="form-control bx-auth-input" type="text" name="USER_LOGIN" maxlength="255" value="<?=$arResult["LAST_LOGIN"]?>" id="user_login">
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-5">
				<div class="form-group">
					<label for="user_password"><?=GetMessage("AUTH_PASSWORD")?></label>
					<input class="form-control bx-auth-input" type="password" name="USER_PASSWORD" maxlength="255" id="user_password">
					<?if($arResult["SECURE_AUTH"]):?>
						<span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
							<div class="bx-auth-secure-icon"></div>
						</span>
						<noscript>
						<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
							<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
						</span>
						</noscript>
						<script type="text/javascript">
							document.getElementById('bx_auth_secure').style.display = 'inline-block';
						</script>
					<?endif?>
				</div>
			</div>
		</div>

		<?if($arResult["CAPTCHA_CODE"]):?>
			<div class="row">
				<div class="col-xs-5">
					<div class="form-group">
						<label for="captcha_input"><?echo GetMessage("AUTH_CAPTCHA_PROMT")?></label>
						<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
						<img class="bx-auth-captcha-img" src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA">
						<input class="form-control bx-auth-input" type="text" name="captcha_word" maxlength="50" value="" size="15" id="captcha_input">
					</div>
				</div>
			</div>
		<?endif;?>

		<?if ($arResult["STORE_PASSWORD"] == "Y"):?>
			<div class="row">
				<div class="col-xs-10">
					<div class="checkbox">
						<label>
							<input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" />
							Запомнить меня на этом компьютере
						</label>
					</div>
				</div>
			</div>
		<?endif;?>

		<div class="row bx-auth-button-padding">
			<div class="col-xs-10">
				<button type="submit" class="btn btn-primary" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>">Вход</button>
				<button type="button" class="btn btn-default" onClick="location.href='/'">Отменить</button>
			</div>
		</div>

		<?if (0 && $arParams["NOT_SHOW_LINKS"] != "Y"):?>
			<noindex>
				<div class="row">
					<div class="col-xs-5 login-forgot-text">
						<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a>
					</div>
				</div>
			</noindex>
		<?endif?>

		<?if(0 && $arParams["NOT_SHOW_LINKS"] != "Y" && $arResult["NEW_USER_REGISTRATION"] == "Y" && $arParams["AUTHORIZE_REGISTRATION"] != "Y"):?>
			<noindex>
				<div class="row">
					<div class="col-xs-5 login-forgot-text">
						<a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_REGISTER")?></a>
					</div>
				</div>
			</noindex>
		<?endif?>

	</form>
</div>

<script type="text/javascript">
	<?if (strlen($arResult["LAST_LOGIN"])>0):?>
		try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
	<?else:?>
		try{document.form_auth.USER_LOGIN.focus();}catch(e){}
	<?endif;?>
</script>
