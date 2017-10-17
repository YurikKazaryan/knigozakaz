<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	/************************************
	* Генерация пользователей для школ
	************************************/
	if (0 && $USER->IsAdmin()) {

		// Загружаем список школ и генерим логины и фио пользователей, создаем пароли, пишем в текстовый файл (муниципалитет - школа - логин - пароль)
		$arSchool = array();
		$cnt = 1;
		$res = CIBlockElement::GetList(
			array('PROPERTY_MUN' => 'asc', 'NAME' => 'asc'),
			array('IBLOCK_ID' => 10, 'PROPERTY_OBLAST' => 144),
			false, false,
			array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_MUN', 'PROPERTY_FULL_NAME')
		);
		while ($arFields = $res->GetNext()) {
			$arSchool[] = array(
				'ID' => $arFields['ID'],
				'MUN_ID' => $arFields['PROPERTY_MUN_VALUE'],
				'MUN_NAME' => get_obl_name($arFields['PROPERTY_MUN_VALUE']),
				'NAME' => $arFields['~NAME'],
				'FULL_NAME' => $arFields['~PROPERTY_FULL_NAME_VALUE'],
				'LOGIN' => 'KEM_' . sprintf("%'.04d", $cnt++),
				'PASS' => passwordGenerator(8),
				'REGION' => 144
			);
		}

		// Создаем пользователей и связываем их со школами
		foreach ($arSchool as $arItem) {
			$user = new CUser;
			$arFields = Array(
			  "NAME" => "Администратор школы",
			  "EMAIL"             => "nomail@nomail.ru",
			  "LOGIN"             => $arItem['LOGIN'],
			  "ACTIVE"            => "Y",
			  "GROUP_ID"          => array(3,4,8),
			  "PASSWORD"          => $arItem['PASS'],
			  "CONFIRM_PASSWORD"  => $arItem['PASS'],
			  "UF_REGION"		  => $arItem['REGION']
			);

			$id = $user->Add($arFields);

			if (intval($id) > 0) {
				// Добавляем id администратора в школу
				CIBlockElement::SetPropertyValuesEx($arItem['ID'], 10, array('ADMIN' => array($id)));
			} else
				echo 'Ошибка добавления пользователя ' . $arItem['LOGIN'] . ' - ' . $user->LAST_ERROR;


		}

		// Записываем в файл логины и парооли
		$f = fopen($_SERVER['DOCUMENT_ROOT'] . '/test/kemerovo_list.csv', 'w');
		foreach ($arSchool as $arItem) {
			$name = $arItem['NAME'];
			$fullName = $arItem['FULL_NAME'];
			if (strpos($name, '"') !== false) $name = '"' . str_replace('"', '""', $name) . '"';
			if (strpos($fullName, '"') !== false) $fullName = '"' . str_replace('"', '""', $fullName) . '"';
			fwrite($f, $arItem['MUN_NAME'].';'.$arItem['LOGIN'].';'.$arItem['PASS'].';'.$name.';'.$fullName."\n");
		}

		fclose($f);

	}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>