<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	if (0 && $USER->IsAdmin()) {

		$arFile = file($_SERVER['DOCUMENT_ROOT'] . '/test/sch.csv');
		$arError = array();

		$arPassword = array();
		foreach ($arFile as $str) {
			$arStr = explode(';', $str);

			$arStr[0] = trim($arStr[0]);
			$arStr[1] = trim($arStr[1]);

			$res = CUser::GetByLogin($arStr[0]);

			if ($arFields = $res->GetNext()) {
				$user = new CUser;
				$user->Update($arFields['ID'], array(
					'PASSWORD' => $arStr[1],
					'CONFIRM_PASSWORD' => $arStr[1]
				));
//				echo $arFields['ID'] . ' - ' . trim($arStr[0]) . ' - ' . trim($arStr[1]) ;
				echo $user->LAST_ERROR . '<br>';
			}

		}



	}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>