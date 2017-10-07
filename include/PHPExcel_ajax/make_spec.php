<?
/********************************************
* Создание файла спецификации для заказа
* при необходимости формируется и список-приложение (Просвещение)
*
* Параметры (передаются через POST)
*    ORDER_ID  - ID заказа
*********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");	// Подключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");									// Подключаем библиотеки сайта
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/PHPExcel/PHPExcel/IOFactory.php';	// Подключаем PHPExcel

global $USER;

define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);

// Обработка параметра
$arOrders = explode(',', trim($_POST['ORDER_ID']));

$result = array();

foreach ($arOrders as $order_id){
	if ($order_id) {
		if(CModule::IncludeModule('iblock')) {

			$arPeriod = getWorkPeriod();

			// Определяем издательство по заказу и возможное наличие файла спецификации и доп.файла
			$res = CIBlockElement::GetList(
				false,
				array('IBLOCK_ID' => 11, 'ID' => $order_id),
				false, false,
				array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_SPEC', 'PROPERTY_SCHOOL_ID', 'PROPERTY_SPEC2', 'PROPERTY_REGION', 'PROPERTY_ORDER_NUM')
			);
			if ($arFields = $res->GetNext()) {
				$spec = $arFields['PROPERTY_SPEC_VALUE'];
				$spec2 = $arFields['PROPERTY_SPEC2_VALUE'];
				$regionID = $arFields['PROPERTY_REGION_VALUE'];
				$izd_id = $arFields['PROPERTY_IZD_ID_VALUE'];
				$arSchoolInfo = getSchoolInfo($arFields['PROPERTY_SCHOOL_ID_VALUE']);
				$arOrderInfo = getOrderInfo($order_id);
				$orderNum = $arSchoolInfo['ID'] . '-' . $arFields['PROPERTY_ORDER_NUM_VALUE'];
			}

//			if ($izd_id && !$spec) {
			if ($izd_id) {

				// Получаем файл бланка заказа
				$res = CIBlockElement::GetList(
					false,
					array(
						'IBLOCK_ID' => IB_IZD_FILES,
						'PROPERTY_IZD_ID' => $izd_id,
						'PROPERTY_TYPE' => 'spec',
						'PROPERTY_REGION_ID' => $regionID,
						'PROPERTY_PERIOD' => $arPeriod['ID']
					),
					false, false,
					array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_TYPE', 'PROPERTY_FILE')
				);
				if ($arFields = $res->GetNext()) $file = ($arFields['PROPERTY_FILE_VALUE'] ? CFile::GetPath($arFields['PROPERTY_FILE_VALUE']) : false);

				if ($file) {

					// Загружаем таблицу-бланк
					$objPHPExcel = PHPExcel_IOFactory::load($_SERVER['DOCUMENT_ROOT'] . $file);

					$arSpecGeneration = array(IZD_PROSV, 100, IZD_VITA_PRESS, 109, IZD_AKADEMIYA, IZD_PROSV_EFU, 117, 119, 120, 180);

					if (in_array($izd_id, $arSpecGeneration)) { // Генерация спецификации

						// Загружаем информацию об учебниках в заказе
						$arOrder = array();
						$res = CIBlockElement::GetList(
							array('PROPERTY_FP_CODE' => 'asc'),
							array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_ORDER_NUM' => $order_id),
							false, false,
							array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_ORDER_NUM', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_IZD_ID', 'PROPERTY_NDS', 'PROPERTY_BOOK')
						);
						$cnt = 0;
						$sum = 0;
						$sum_10 = 0;
						$sum_18 = 0;
						$arBooksID = array();
						while ($arFields = $res->GetNext()) {
							$arBooksID[] = $arFields['PROPERTY_BOOK_VALUE'];
							$arOrder[] = array(
								'BOOK_ID' => $arFields['PROPERTY_BOOK_VALUE'],
								'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
								'PRICE' => $arFields['PROPERTY_PRICE_VALUE'],
								'IZD_NAME' => getIzdName($arFields['PROPERTY_IZD_ID_VALUE'])
							);
							$cnt += $arFields['PROPERTY_COUNT_VALUE'];
							$sum += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
							if ($arFields['PROPERTY_NDS_VALUE'] == 18)
								$sum_18 += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
							else
								$sum_10 += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
						}

						$ndsSum10 = getNdsSum($sum_10, 10);
						$ndsSum18 = getNdsSum($sum_18, 18);

						$arBookInfo = (count($arBooksID) > 1 ? getBookInfo($arBooksID) : array($arBooksID[0] => getBookInfo($arBooksID)));

						$countBooks = count($arOrder);
						$addCount = $countBooks > 5 ? $countBooks - 5 : 0;	// Считаем, сколько строк надо добавить

						// Заполняем файл в зависимости от издательства
						switch ($izd_id) {

							//********** ПРОСВЕЩЕНИЕ ********** 2017
							case 5:

								$objPHPExcel->getActiveSheet()

									->setCellValue('K4', '№ ' . $orderNum)
									->setCellValue('C5', 'Спецификация на товар, поставляемый для ' . $arSchoolInfo['FULL_NAME'])

									->setCellValue('B18', $arSchoolInfo['FULL_NAME'])
									->setCellValue('B21', get_finic($arSchoolInfo['DIR_FIO']));


									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(11, $addCount);

									$numP = 1;
									$shift = 8; // Смещение на высоту шапки
									foreach ($arOrder as $arBook) {

										// Считаем сумму НДС
										$nds = ($arBookInfo[$arBook['BOOK_ID']]['PROPERTY_NDS_VALUE'] == 18 ? 18 : 10);
										$ndsSum = getNdsSum($arBook['COUNT']*$arBook['PRICE'], $nds);

										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(1, $numP+$shift, $numP)
											->setCellValueByColumnAndRow(2, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['PROPERTY_CODE_1C_VALUE'])
											->setCellValueByColumnAndRow(3, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['PROPERTY_FP_CODE_VALUE'])
											->setCellValueByColumnAndRow(4, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_UMK_VALUE'])
											->setCellValueByColumnAndRow(5, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_AUTHOR_VALUE'])
											->setCellValueByColumnAndRow(6, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_TITLE_VALUE'])
											->setCellValueByColumnAndRow(7, $numP+$shift, '')
											->setCellValueByColumnAndRow(8, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['PROPERTY_YEAR_VALUE'])
											->setCellValueByColumnAndRow(9, $numP+$shift, $arBook['COUNT'])
											->setCellValueByColumnAndRow(10, $numP+$shift, '')
											->setCellValueByColumnAndRow(11, $numP+$shift, $arBook['PRICE'])
											->setCellValueByColumnAndRow(12, $numP+$shift, $nds)
											->setCellValueByColumnAndRow(13, $numP+$shift, sprintf('%01.2f', $ndsSum))
											->setCellValueByColumnAndRow(14, $numP+$shift, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;

							//********** ПРОСВЕЩЕНИЕ - ЭФУ ********** 2017
							case IZD_PROSV_EFU:

								$objPHPExcel->getActiveSheet()

									->setCellValue('C3', '№ ' . $orderNum . ' от «___»________________201__ г.')
									->setCellValue('A8', '№ ' . $orderNum . ' от «___»________________201__ г.')

									->setCellValue('B19', $arSchoolInfo['FULL_NAME'])
									->setCellValue('B22', '___________________/'.get_finic($arSchoolInfo['DIR_FIO']).'/');


									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(13, $addCount);

									$numP = 1;
									$shift = 10; // Смещение на высоту шапки
									foreach ($arOrder as $arBook) {

										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+$shift, $numP)
											->setCellValueByColumnAndRow(1, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_AUTHOR_VALUE'])
											->setCellValueByColumnAndRow(2, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_TITLE_VALUE'])
											->setCellValueByColumnAndRow(3, $numP+$shift, $arBookInfo[$arBook['BOOK_ID']]['PROPERTY_CODE_1C_VALUE'])
											->setCellValueByColumnAndRow(4, $numP+$shift, 85)
											->setCellValueByColumnAndRow(5, $numP+$shift, $arBook['COUNT'])
											->setCellValueByColumnAndRow(6, $numP+$shift, $arBook['COUNT']*$arBook['PRICE']);

										$numP++;
									}
								break;

							//********** ГЭНДАЛЬФ **********
							case 180:

								$objPHPExcel->getActiveSheet()
									->setCellValue('A13', 'Итого без НДС: ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . ")")
									->setCellValue('A15', 'К ОПЛАТЕ: ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . ")")
									->setCellValue('C17', html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('C18',
										"ИНН/КПП " . $arSchoolInfo['INN'] . "/" . $arSchoolInfo['KPP'] . "\n" .
										$arSchoolInfo['ADDRESS'] . "\n" .
										"Р/сч " . $arSchoolInfo['RASCH'] . "\n" .
										"Банк " . $arSchoolInfo['BANK'] . "\n" .
										"БИК " . $arSchoolInfo['BIK'] . "\n" .
										"Л/сч " . $arSchoolInfo['LS'] . "\n" .
										"Телефон " . $arSchoolInfo['PHONE'] . "\n" .
										"E-mail " . $arSchoolInfo['EMAIL'] . "\n" .
										"_______________/" . $arSchoolInfo['DIR_FIO'] . "/ \n\n                М.П.")
									->setCellValue('B2', '№ ' . $order_id . ' от "___"____________ 201__ года');

									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(10, $addCount);

									$numP = 1;
									foreach ($arOrder as $arBook) {
										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+7, $numP)
											->setCellValueByColumnAndRow(1, $numP+7, $arBook['NAME'])
											->setCellValueByColumnAndRow(2, $numP+7, $arBook['COUNT'])
											->setCellValueByColumnAndRow(3, $numP+7, $arBook['PRICE'])
											->setCellValueByColumnAndRow(4, $numP+7, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;

							//********** ДРОФА **********
							case 100:

								$nds_sum = $sum * 10 / (100+10);

								$objPHPExcel->getActiveSheet()
									->setCellValue('H14', $sum)
									->setCellValue('A16', 'Итого на общую сумму ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . "),\nв том числе НДС " . sprintf('%01.2f', $nds_sum) . ' руб. (' . num2str($nds_sum) . ').')
									->setCellValue('E22', html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('E29', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
									->setCellValue('D4', '№ ' . $order_id . ' от "___"____________ 201__ года');

									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(12, $addCount);

									$numP = 1;
									foreach ($arOrder as $arBook) {
										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+9, $numP)
											->setCellValueByColumnAndRow(1, $numP+9, $arBook['FP'])
											->setCellValueByColumnAndRow(2, $numP+9, $arBook['CODE_1C'])
											->setCellValueByColumnAndRow(3, $numP+9, $arBook['NAME'])
											->setCellValueByColumnAndRow(6, $numP+9, 'Экз.')
											->setCellValueByColumnAndRow(5, $numP+9, $arBook['COUNT'])
											->setCellValueByColumnAndRow(4, $numP+9, $arBook['PRICE'])
											->setCellValueByColumnAndRow(7, $numP+9, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;

							//********** АКАДЕМИЯ ********** 2017
							case IZD_AKADEMIYA:

								$objPHPExcel->getActiveSheet()
									->setCellValue('E13', $sum)
									->setCellValue('D13', $cnt)
									->setCellValue('B15', num2str($sum))
									->setCellValue('C19', '_______________/'. $arSchoolInfo['DIRFINIC'] . '/')
									->setCellValue('C2', 'к Договору № НВС-' . $orderNum);

									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(10, $addCount);

									$numP = 1;
									foreach ($arOrder as $arBook) {
										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+7, $numP)
											->setCellValueByColumnAndRow(1, $numP+7, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_TITLE_VALUE'])
											->setCellValueByColumnAndRow(2, $numP+7, $arBook['PRICE'])
											->setCellValueByColumnAndRow(3, $numP+7, $arBook['COUNT'])
											->setCellValueByColumnAndRow(4, $numP+7, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;

							//********** ДРОФА ЭФУ **********
							case 119:

								$objPHPExcel->getActiveSheet()
									->setCellValue('F27', $sum)
									->setCellValue('A29', 'Общая сумма составляет ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . ')')
									->setCellValue('B18', 'Лицензиат: ' . html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('E37', html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('E44', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
									->setCellValue('D3', '№ ' . $order_id . ' от "___"____________ 201__ года');

									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(25, $addCount);

									$numP = 1;
									foreach ($arOrder as $arBook) {
										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+22, $numP)
											->setCellValueByColumnAndRow(1, $numP+22, $arBook['CODE_1C'])
											->setCellValueByColumnAndRow(2, $numP+22, $arBook['NAME'])
											->setCellValueByColumnAndRow(3, $numP+22, $arBook['COUNT'])
											->setCellValueByColumnAndRow(4, $numP+22, $arBook['PRICE'])
											->setCellValueByColumnAndRow(5, $numP+22, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;

							//********** АСТРЕЛЬ **********
/*
							case 101:

								$nds_sum = $sum * 10 / (100+10);

								$objPHPExcel->getActiveSheet()
									->setCellValue('I18', $sum)
									->setCellValue('A21', 'Итого на общую сумму ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . "),\nв том числе НДС 10% в размере " . sprintf('%01.2f', $nds_sum) . ' руб. (' . num2str($nds_sum) . ').')
									->setCellValue('H25', html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('H33', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
									->setCellValue('I5', '№ ' . $order_id . ' от "' . date('d', $arOrderInfo['DATE']) . '" ' . month_name_r(date('n', $arOrderInfo['DATE'])) . ' ' . date('Y', $arOrderInfo['DATE']) . 'года');

									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(12, $addCount);

									$numP = 1;
									foreach ($arOrder as $arBook) {
										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+13, $numP)
											->setCellValueByColumnAndRow(1, $numP+13, $arBook['FP'])
											->setCellValueByColumnAndRow(3, $numP+13, $arBook['NAME'])
											->setCellValueByColumnAndRow(10, $numP+13, 'шт')
											->setCellValueByColumnAndRow(7, $numP+13, $arBook['COUNT'])
											->setCellValueByColumnAndRow(6, $numP+13, $arBook['PRICE'])
											->setCellValueByColumnAndRow(4, $numP+13, $arBook['CLASS'])
											->setCellValueByColumnAndRow(11, $numP+13, $arBook['PRIM'])
											->setCellValueByColumnAndRow(8, $numP+13, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;
*/
							//********** ВИТА-ПРЕСС ********** 2017
							case IZD_VITA_PRESS:

								$objPHPExcel->getActiveSheet()
									->setCellValue('I16', $sum)
									->setCellValue('A18', 'Итого на общую сумму ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . ')' .
										($ndsSum10 ? ', в том числе НДС 10% ' .sprintf('%01.2f', $ndsSum10) . ' руб. (' . num2str($ndsSum10) . ')' : '') .
										($ndsSum18 ? ', в том числе НДС 18% ' .sprintf('%01.2f', $ndsSum18) . ' руб. (' . num2str($ndsSum18) . ')' : ''))
									->setCellValue('F21', $arSchoolInfo['FULL_NAME'])
									->setCellValue('F26', '_________________/'. $arSchoolInfo['DIRFINIC'] . '/')
									->setCellValue('F4', '№' . $orderNum . ' от «___»_____________ 201__ г.');

								// Добавляем строки при необходимости
								if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(13, $addCount);

								$numP = 1;
								foreach ($arOrder as $arBook) {
									$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(0, $numP+10, $numP)
										->setCellValueByColumnAndRow(1, $numP+10, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_FP_CODE_VALUE'])
										->setCellValueByColumnAndRow(2, $numP+10, $arBookInfo[$arBook['BOOK_ID']]['~PROPERTY_TITLE_VALUE'])
										->setCellValueByColumnAndRow(3, $numP+10, '22.11.21.191')
										->setCellValueByColumnAndRow(4, $numP+10, '643')
										->setCellValueByColumnAndRow(5, $numP+10, $arBook['PRICE'])
										->setCellValueByColumnAndRow(6, $numP+10, $arBook['COUNT'])
										->setCellValueByColumnAndRow(7, $numP+10, 'шт.')
										->setCellValueByColumnAndRow(8, $numP+10, $arBook['COUNT']*$arBook['PRICE']);
									$numP++;
								}
								break;

							//********** ВИТА-ПРЕСС - ЭФУ **********
							case 117:

								$objPHPExcel->getActiveSheet()
									->setCellValue('I14', $sum)
									->setCellValue('A16', 'Итого на общую сумму ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . '), в том числе НДС 10% (сумма указывается в счет-фактуре при отрузке).')
									->setCellValue('F22', html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('F30', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
									->setCellValue('C4', '№' . $order_id . ' от "' . date('d', $arOrderInfo['DATE']) . '" ' . month_name_r(date('n', $arOrderInfo['DATE'])) . ' ' . date('Y', $arOrderInfo['DATE']) . 'года');

								// Добавляем строки при необходимости
								if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(12, $addCount);

								$numP = 1;
								foreach ($arOrder as $arBook) {
									$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(0, $numP+9, $numP)
										->setCellValueByColumnAndRow(1, $numP+9, $arBook['FP'])
										->setCellValueByColumnAndRow(2, $numP+9, $arBook['AUTHOR'].' '.$arBook['NAME'])
										->setCellValueByColumnAndRow(3, $numP+9, '')
										->setCellValueByColumnAndRow(4, $numP+9, '643')
										->setCellValueByColumnAndRow(5, $numP+9, $arBook['PRICE'])
										->setCellValueByColumnAndRow(6, $numP+9, $arBook['COUNT'])
										->setCellValueByColumnAndRow(7, $numP+9, 'шт.')
										->setCellValueByColumnAndRow(8, $numP+9, $arBook['COUNT']*$arBook['PRICE']);
									$numP++;
								}
								break;

							//********** МНЕМОЗИНА, МНЕМОЗИНА - ЭФУ **********
							case 109:
							case 120:

//								$nds_sum = $sum * 10 / (100+10);

								$objPHPExcel->getActiveSheet()
									->setCellValue('G14', $sum)
									->setCellValue('A16', 'Итого на общую сумму ' . sprintf('%01.2f', $sum) . ' руб. (' . num2str($sum) . '), в том числе НДС).')
									->setCellValue('E22', html_entity_decode($arSchoolInfo['FULL_NAME']))
									->setCellValue('E29', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
									->setCellValue('D4', '№ ' . $order_id . ' от "____" ________________ 201__ г.');
//									->setCellValue('D4', '№ ' . $order_id . ' от "' . date('d', $arOrderInfo['DATE']) . '" ' . month_name_r(date('n', $arOrderInfo['DATE'])) . ' ' . date('Y', $arOrderInfo['DATE']) . 'года');

									// Добавляем строки при необходимости
									if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(12, $addCount);

									$numP = 1;
									foreach ($arOrder as $arBook) {
										$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0, $numP+9, $numP)
											->setCellValueByColumnAndRow(1, $numP+9, $arBook['FP'])
											->setCellValueByColumnAndRow(2, $numP+9, html_entity_decode(html_entity_decode($arBook['AUTHOR'])))
											->setCellValueByColumnAndRow(3, $numP+9, html_entity_decode(html_entity_decode($arBook['NAME'])))
											->setCellValueByColumnAndRow(4, $numP+9, $arBook['PRICE'])
											->setCellValueByColumnAndRow(5, $numP+9, $arBook['COUNT'])
											->setCellValueByColumnAndRow(6, $numP+9, $arBook['COUNT']*$arBook['PRICE']);
										$numP++;
									}
								break;
						}

						$result = array('error' => 0);

					} else {	// Заполнение шаблона спецификации =========================================================================

						// Выбираем учебники заказа (ключ - уникальный код)
						$arBookID = array();
						$arBooks = array();
						$res = CIBlockElement::GetList(
							false,
							array('IBLOCK_ID' => IB_ORDERS, 'PROPERTY_ORDER_NUM' => $order_id),
							false, false,
							array('IBLOCK_ID', 'ID', 'PROPERTY_BOOK', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_NDS')
						);
						while ($arFields = $res->GetNext()) {
							$arBookID[] = $arFields['PROPERTY_BOOK_VALUE'];
							$arBooks[$arFields['PROPERTY_BOOK_VALUE']] = array(
								'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
								'PRICE' => $arFields['PROPERTY_PRICE_VALUE'],
								'NDS' => $arFields['PROPERTY_NDS_VALUE']
							);
						}

//if ($USER->GetID()==1) test_out($arBookID);
//if ($USER->GetID()==1) test_out($arBooks);

						// Запрашиваем информацию о выбранных учебниках
						$arBookInfo = getBookInfo($arBookID, true);

//if ($USER->GetID()==1) test_out($arBookInfo);

						// Составляем массив заказа
						$arOrder = array();
						foreach ($arBooks as $bookID => $bookInfo) {
							$arOrder[$arBookInfo[$bookID]['PROPERTY_CODE_1C_VALUE']] = $arBookInfo[$bookID];
							$arOrder[$arBookInfo[$bookID]['PROPERTY_CODE_1C_VALUE']]['PRICE'] = $bookInfo['PRICE'];
							$arOrder[$arBookInfo[$bookID]['PROPERTY_CODE_1C_VALUE']]['COUNT'] = $bookInfo['COUNT'];
							$arOrder[$arBookInfo[$bookID]['PROPERTY_CODE_1C_VALUE']]['NDS'] = $bookInfo['NDS'];
							$arOrder[$arBookInfo[$bookID]['PROPERTY_CODE_1C_VALUE']]['SUM'] = $bookInfo['PRICE'] * $bookInfo['COUNT'];
							$arOrder[$arBookInfo[$bookID]['PROPERTY_CODE_1C_VALUE']]['NDSSUM'] = getNdsSum($bookInfo['PRICE'] * $bookInfo['COUNT'], $bookInfo['NDS']);
						}

						$allCount = 0;
						$allSum = 0;
						$allNdsSum = 0;
						$allNds18Sum = 0;

						$arErrorPrice = array();

//if ($USER->GetID()==1) test_out($arOrder);

						$maxRow= $objPHPExcel->getActiveSheet()->getHighestRow();
						for ($i = 0; ($i < $maxRow) && (count($arOrder) > 0); $i++) {

							// Строка для Просвещения и Вентаны
//							$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
//							$codeBinom = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(12, $i)->getValue();

							// Заполнение файла - в зависимости от издательства!
							switch ($izd_id) {

								//********** ВЛАДОС ********** 2017
								case IZD_VLADOS:
									$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(10, $i)->getValue();

									if ($arOrder[$code]['NDS'] == 18)
										$allNds18Sum += $arOrder[$code]['NDSSUM'];
									else
										$allNdsSum += $arOrder[$code]['NDSSUM'];

									if ($arOrder[$code]) {

										$sPrice = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue();
										if ($sPrice != $arOrder[$code]['PRICE']) $arErrorPrice[$code] = i;

										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $i, $arOrder[$code]['COUNT']);
										unset($arOrder[$code]);
									}
									break;

								//********** РУССКОЕ СЛОВО **********
								case IZD_RUSSLOVO:
									$objPHPExcel->setActiveSheetIndex(0);
									$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0, $i)->getValue();

									if ($arOrder[$code]) {

										$sPrice = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
										if ($sPrice != $arOrder[$code]['PRICE']) $arErrorPrice[$code] = i;

										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $i, $arOrder[$code]['COUNT']);
										unset($arOrder[$code]);
									}
									break;

								//********** АКАДЕМКНИГА **********
								case IZD_AKADEMKNIGA:
									$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(12, $i)->getValue();

									if ($arOrder[$code]) {

										$allCount += $arOrder[$code]['COUNT'];
										$allSum += $arOrder[$code]['SUM'];
										
										if ($arOrder[$code]['NDS'] == 18)
											$allNds18Sum += $arOrder[$code]['NDSSUM'];
										else
											$allNdsSum += $arOrder[$code]['NDSSUM'];

										$sPrice = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(10, $i)->getValue();
										if ($sPrice != $arOrder[$code]['PRICE']) $arErrorPrice[$code] = i;

										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $i, $arOrder[$code]['COUNT']);
										unset($arOrder[$code]);
									}
									break;

								//********** БИНОМ ********** 2017
								case IZD_BINOM:
									$objPHPExcel->setActiveSheetIndex(2);
									$code = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
									if ($arOrder[$code]) {

										$sPrice = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(9, $i)->getValue();
										if ($sPrice != $arOrder[$code]['PRICE']) $arErrorPrice[$code] = i;

										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $i, $arOrder[$code]['COUNT']);
										unset($arOrder[$code]);
									}
									break;

							}
						}

//if ($USER->GetID()==1) test_out('END SETTING COUNT');

						// Если остались записи в $arOrder - это ошибка
						if (count($arOrder) || count($arErrorPrice)) {
							$result['error_list'] = array();
							foreach ($arOrder as $code1C => $arBook)
								$result['error_list'][] = 'Не найден код ' . $code1C;
							foreach ($arErrorPrice as $code1C => $numStr)
								$result['error_list'][] = 'не совпадает цена. Код 1С: ' . $code1C . '. Номер строки в спецификации: ' + $numStr;
						} else {
							$result['error_list'] = 0;
						}


						// Если были ошибки, формируем лист ошибок
						if (count($arOrder) || count($arErrorPrice)) {
							$errSheet = new PHPExcel_Worksheet($objPHPExcel, 'ERROR_LIST');
							$objPHPExcel->addSheet($errSheet);

							$errLine = 1;

							if (count($arOrder) > 0) {
								$objPHPExcel->getSheetByName('ERROR_LIST')
									->setCellValue('A'.$errLine++, 'Позиции, присутствующие в каталоге, но не найденные в спецификации:')
									->setCellValue('A'.$errLine++, 'Код 1С учебника');
								foreach ($arOrder as $code1C => $arRep)
									$objPHPExcel->getSheetByName('ERROR_LIST')
										->setCellValue('A'.$errLine++, $code1C);
								$errLine++;
							}
							if (count($arErrorPrice) > 0){
								$objPHPExcel->getSheetByName('ERROR_LIST')
									->setCellValue('A'.$errLine++, 'Позиции, в которых цена свода не совпадает с ценой в базе')
									->setCellValue('A'.$errLine++, 'Код 1С учебника');
								foreach ($arErrorPrice as $code1C)
									$objPHPExcel->getSheetByName('ERROR_LIST')
										->setCellValue('A'.$errLine++, $code1C);
							}
						}



					}

//if ($USER->GetID()==1) test_out('START DATASHEET');

					/*********************************************************************
					* Заполняем ячейки с реквизитами (если надо) и удаление рабочих данных
					*********************************************************************/
					switch ($izd_id) {

						//********** ВЛАДОС **********
						case IZD_VLADOS:
							$objPHPExcel->getActiveSheet()
								->setCellValue('J20', $allNdsSum)
								->setCellValue('B25', $arSchoolInfo['NAME'])
								->setCellValue('B29', '__________________'.get_finic($arSchoolInfo['DIR_FIO']))
								->removeColumn('K');
							break;

						//********** РУССКОЕ СЛОВО **********
						case IZD_RUSSLOVO:
							$objPHPExcel->setActiveSheetIndex(0);
							$objPHPExcel->getActiveSheet()
								->setCellValue('G1', "Приложение №1\n к Контракту (Договору)\n №" . $orderNum . ' от "___"______________201__г.')
								->setCellValue('B314', $arSchoolInfo['NAME'])
								->setCellValue('D317', get_finic($arSchoolInfo['DIR_FIO']));
							$objPHPExcel->setActiveSheetIndex(1);
							$objPHPExcel->getActiveSheet()
								->setCellValue('B2', $arSchoolInfo['FULL_NAME'])
								->setCellValue('B3', $arSchoolInfo['NAME'])
								->setCellValue('B4', $arSchoolInfo['INN'])
								->setCellValue('B5', $arSchoolInfo['KPP'])
								->setCellValue('B6', $arSchoolInfo['OKPO'])
								->setCellValue('B7', $arSchoolInfo['ADDRESS'])
								->setCellValue('B8', $arSchoolInfo['ADDRESS'])
								->setCellValue('B9', $arSchoolInfo['ADDRESS'])
								->setCellValue('B10', $arSchoolInfo['PHONE'])
								->setCellValue('B11', $arSchoolInfo['EMAIL'])
								->setCellValue('B12', '')
								->setCellValue('B13', $arSchoolInfo['DIR_FIO'])
								->setCellValue('B14', $arSchoolInfo['BIK'])
								->setCellValue('B15', $arSchoolInfo['RASCH'])
								->setCellValue('B16', $arSchoolInfo['BANK'])
								->setCellValue('B17', $arSchoolInfo['LS'])
								->setCellValue('B18', '')
								->setCellValue('B19', $arSchoolInfo['OTV_FIO'].' ('.$arSchoolInfo['OTV_PHONE'].')')
								->setCellValue('B20', $arSchoolInfo['PUNKT_FZ'] ? substr($arSchoolInfo['PUNKT_FZ'], 7) : '');
							break;

						//********** АКАДЕМКНИГА **********
						case IZD_AKADEMKNIGA:
							$objPHPExcel->getActiveSheet()
								->removeColumn('M');
							$objPHPExcel->setActiveSheetIndex(0);
							$objPHPExcel->getActiveSheet()
								->setCellValue('A457', 'Итого ' . num2str($allSum) .
											($allNdsSum ? ", в том числе НДС 10%: " . num2str($allNdsSum) : '') .
											($allNds18Sum ? ", в том числе НДС 18%: " . num2str($allNds18Sum) : ''))
								->setCellValue('B460', $arSchoolInfo['NAME'])
								->setCellValue('D464', get_finic($arSchoolInfo['DIR_FIO']));
							$objPHPExcel->setActiveSheetIndex(1);
							$objPHPExcel->getActiveSheet()
								->setCellValue('B2', $arSchoolInfo['FULL_NAME'])
								->setCellValue('B4', 'Директора')
								->setCellValue('B5', $arSchoolInfo['DIR_FIO_R'])
								->setCellValue('B8', 'ИНН '.$arSchoolInfo['INN'].'; КПП '.$arSchoolInfo['KPP'].'; ОГРН '.$arSchoolInfo['OGRN'])
								->setCellValue('B9', 'Адрес: '.$arSchoolInfo['ADDRESS'])
								->setCellValue('B10', 'Телефон/факс: '.$arSchoolInfo['PHONE'])
								->setCellValue('B11', 'E_mail: '.$arSchoolInfo['EMAIL'])
								->setCellValue('B12', 'Р/счет: '.$arSchoolInfo['RASCH'])
								->setCellValue('B13', 'Л/счет: '.$arSchoolInfo['LS'])
								->setCellValue('B14', $arSchoolInfo['BANK'])
								->setCellValue('B15', 'БИК: '.$arSchoolInfo['BIK'])
								->setCellValue('B16', ' ')
								->setCellValue('B17', ' ')
								->setCellValue('B18', ' ')
								->setCellValue('B19', ' ')
								->setCellValue('B21', $arSchoolInfo['OTV_FIO'])
								->setCellValue('B22', $arSchoolInfo['OTV_PHONE']);
							$objPHPExcel->setActiveSheetIndex(0);

							break;

						//********** АКАДЕМКНИГА - ЭФУ **********
						case 112:
							$objPHPExcel->setActiveSheetIndex(1);
							$objPHPExcel->getActiveSheet()
								->setCellValue('B2', html_entity_decode($arSchoolInfo['FULL_NAME']))
								->setCellValue('B4', 'Директора')
								->setCellValue('B5', $arSchoolInfo['DIR_FIO_R'])
								->setCellValue('B8', 'ИНН '.$arSchoolInfo['INN'].'; КПП '.$arSchoolInfo['KPP'].'; ОГРН '.$arSchoolInfo['OGRN'])
								->setCellValue('B9', 'Адрес: '.$arSchoolInfo['ADDRESS'])
								->setCellValue('B10', 'Телефон/факс: '.$arSchoolInfo['PHONE'])
								->setCellValue('B11', 'E_mail: '.$arSchoolInfo['EMAIL'])
								->setCellValue('B12', 'Р/счет: '.$arSchoolInfo['RASCH'])
								->setCellValue('B13', 'Л/счет: '.$arSchoolInfo['LS'])
								->setCellValue('B14', $arSchoolInfo['BANK'])
								->setCellValue('B15', 'БИК: '.$arSchoolInfo['BIK'])
								->setCellValue('B16', ' ')
								->setCellValue('B17', ' ')
								->setCellValue('B18', ' ')
								->setCellValue('B19', ' ')
								->setCellValue('B21', $arSchoolInfo['OTV_FIO'])
								->setCellValue('B22', $arSchoolInfo['OTV_PHONE']);
							$objPHPExcel->setActiveSheetIndex(0);

							break;

						//********** БИНОМ ********** 2017
						case IZD_BINOM:
							$objPHPExcel->setActiveSheetIndex(1);
							$objPHPExcel->getActiveSheet()
								->setCellValue('C4', $arSchoolInfo['PUNKT'] ? $arSchoolInfo['PUNKT'] : $arSchoolInfo['RAJON'])
								->setCellValue('C5', $arSchoolInfo['FULL_NAME'])
								->setCellValue('C6', $arSchoolInfo['NAME'])
								->setCellValue('C7', $arSchoolInfo['STATUS'] ? ($arSchoolInfo['STATUS'] == 'orgskaz' ? 'Муниципальный контракт' : 'Договор') : '')
								->setCellValue('C8', $arSchoolInfo['PUNKT_FZ'] ? substr($arSchoolInfo['PUNKT_FZ'], 7) : '')
								->setCellValue('C9', 'Директора ' . $arSchoolInfo['DIR_FIO_R'])
								->setCellValue('C10', 'Директор ' . get_finic($arSchoolInfo['DIR_FIO']))
								->setCellValue('C11', $arSchoolInfo['DIR_DOC'])
								->setCellValue('C12', $arSchoolInfo['ADDRESS'])
								->setCellValue('C13', $arSchoolInfo['ADDRESS'])
								->setCellValue('C14', $arSchoolInfo['OGRN'] ? ' ' . $arSchoolInfo['OGRN'] . ' ' : '')
								->setCellValue('C15', $arSchoolInfo['INN'] ? ' ' . $arSchoolInfo['INN'] . ' ' : '')
								->setCellValue('C16', $arSchoolInfo['KPP'] ? ' ' . $arSchoolInfo['KPP'] . ' ' : '')
								->setCellValue('C17', $arSchoolInfo['OKPO'] ? ' ' . $arSchoolInfo['OKPO'] . ' ' : '')
								->setCellValue('C18', $arSchoolInfo['LS'])
								->setCellValue('C19', $arSchoolInfo['RASCH'])
								->setCellValue('C20', $arSchoolInfo['BANK'])
								->setCellValue('C21', $arSchoolInfo['BIK'])
								->setCellValue('C22', $arSchoolInfo['PHONE'])
								->setCellValue('C23', $arSchoolInfo['EMAIL']);
							$objPHPExcel->setActiveSheetIndex(3);
							break;

						//********** ВЕНТАНА-ГРАФ **********
						case 108:
							$objPHPExcel->getActiveSheet()
								->setCellValue('A4', html_entity_decode('ЗАКАЗЧИК: ' . $arSchoolInfo['FULL_NAME']))
								->setCellValue('E218', html_entity_decode($arSchoolInfo['FULL_NAME']))
								->setCellValue('E222', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
								->setCellValue('A3', 'к контракту №' . $order_id . ' от "' . date('d', $arOrderInfo['DATE']) . '" ' . month_name_r(date('n', $arOrderInfo['DATE'])) . ' ' . date('Y', $arOrderInfo['DATE']) . 'г.');
							break;

						//********** АССОЦИАЦИЯ XXI ВЕК **********
						case 106:
							$objPHPExcel->getActiveSheet()
								->setCellValue('C82', html_entity_decode($arSchoolInfo['FULL_NAME']))
								->setCellValue('C84', '_______________/'. $arSchoolInfo['DIR_FIO'] . '/')
								->setCellValue('D1', 'Приложение к контракту (договору) №' . $order_id . ' от "___"__________201__ г.');
							break;
					}

//if ($USER->GetID()==1) test_out('END DATASHEET');

					// Записываем спецификацию во временный файл
					$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'ord');
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					$objWriter->save($tempFileName);

					// Загружаем файл в Битрикс
					$arFile = CFile::MakeFileArray($tempFileName);
					$arFile['name'] = 'spec_' . $orderNum . '.xlsx';
					$arFile['DESCRIPTION'] = 'spec_' . $orderNum . '.xlsx';

					// Записываем файл в заказ
					CIBlockElement::SetPropertyValuesEx($order_id, 11, array('SPEC' => $arFile));

					// Удаляем временный файл
					unlink($tempFileName);
				}

				// Обрабатываем дополнительный файл (если есть)
				// Получаем файл приложения
				$file = false;
				$res = CIBlockElement::GetList(
					false,
					array('IBLOCK_ID' => $arFields['PROPERTY_FILE_VALUE'], 'PROPERTY_IZD_ID' => $izd_id, 'PROPERTY_TYPE' => 'dop', 'PROPERTY_REGION_ID' => $regionID),
					false, false,
					array('IBLOCK_ID', 'ID', 'PROPERTY_IZD_ID', 'PROPERTY_TYPE', 'PROPERTY_FILE')
				);
				if ($arFields = $res->GetNext()) $file = ($arFields['PROPERTY_FILE_VALUE'] ? CFile::GetPath($arFields['PROPERTY_FILE_VALUE']) : false);

				if ($file) {

					// Загружаем таблицу-бланк
					$objPHPExcel = PHPExcel_IOFactory::load($_SERVER['DOCUMENT_ROOT'] . $file);

					// Загружаем информацию об учебниках в заказе
					$arOrder = array();
					$res = CIBlockElement::GetList(
						array('PROPERTY_FP_CODE' => 'asc'),
						array('IBLOCK_ID' => 9, 'PROPERTY_ORDER_NUM' => $order_id),
						false, false,
						array('IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_ORDER_NUM', 'PROPERTY_FP_CODE', 'PROPERTY_COUNT', 'PROPERTY_PRICE', 'PROPERTY_IZD_NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRIM', 'PROPERTY_CODE_1C', 'PROPERTY_CLASS')
					);
					$sum = 0;
					while ($arFields = $res->GetNext()) {
						$arOrder[] = array(
							'NAME' => $arFields['NAME'],
							'FP' => $arFields['PROPERTY_FP_CODE_VALUE'],
							'COUNT' => $arFields['PROPERTY_COUNT_VALUE'],
							'PRICE' => $arFields['PROPERTY_PRICE_VALUE'],
							'IZD_NAME' => $arFields['PROPERTY_IZD_NAME_VALUE'],
							'AUTHOR' => $arFields['PROPERTY_AUTHOR_VALUE'],
							'PRIM' => $arFields['PROPERTY_PRIM_VALUE'],
							'CODE_1C' => $arFields['PROPERTY_CODE_1C_VALUE'],
							'CLASS' => $arFields['PROPERTY_CLASS_VALUE']
						);
						$sum += $arFields['PROPERTY_COUNT_VALUE'] * $arFields['PROPERTY_PRICE_VALUE'];
					}

					$countBooks = count($arOrder);
					$addCount = $countBooks > 4 ? $countBooks - 4 : 0;	// Считаем, сколько строк надо добавить

					// Заполняем файл в зависимости от издательства
					switch ($izd_id) {
						//********** ПРОСВЕЩЕНИЕ - ЭФУ **********
						case 121:

							$nds_sum = $sum * 10 / (100+10);

							$objPHPExcel->getActiveSheet()
								->setCellValue('G15', sprintf('%01.2f', $sum))
								->setCellValue('F15', 'ИТОГО:')
								->setCellValue('A19', html_entity_decode($arSchoolInfo['FULL_NAME']) . "\nДиректор")
								->setCellValue('A23', '____________________/'. $arSchoolInfo['DIR_FIO'] . '/')
								->setCellValue('D3', '№ ' . $order_id . ' от "___"____________ 201__ года')
								->setCellValue('A8', '№ ' . $order_id . ' от "___"____________ 201__ года');

								// Добавляем строки при необходимости
								if ($addCount) $objPHPExcel->getActiveSheet()->insertNewRowBefore(13, $addCount);

								$numP = 1;
								foreach ($arOrder as $arBook) {
									$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(0, $numP+10, $numP)
										->setCellValueByColumnAndRow(1, $numP+10, $arBook['AUTHOR'])
										->setCellValueByColumnAndRow(2, $numP+10, $arBook['NAME'])
										->setCellValueByColumnAndRow(3, $numP+10, $arBook['CODE_1C'])
										->setCellValueByColumnAndRow(4, $numP+10, '85.00')
										->setCellValueByColumnAndRow(5, $numP+10, $arBook['COUNT'])
										->setCellValueByColumnAndRow(6, $numP+10, $arBook['COUNT']*$arBook['PRICE']);
									$numP++;
								}
							break;
					}

					// Записываем приложение во временный файл
					$tempFileName = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/', 'pril');
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					$objWriter->save($tempFileName);

					// Загружаем файл в Битрикс
					$arFile = CFile::MakeFileArray($tempFileName);
					$arFile['name'] = 'pril_' . $order_id . '.xlsx';
					$arFile['DESCRIPTION'] = 'pril_' . $order_id . '.xlsx';

					// Записываем файл в заказ
					CIBlockElement::SetPropertyValuesEx($order_id, 11, array('SPEC2' => $arFile));

					// Удаляем временный файл
					unlink($tempFileName);
				}

			} // if ($izd_id) {
		}
	} else {
		$result = array('result' => 'Ошибка');
	}
}

// Отдаем результат
echo json_encode($result);



// Отключаем API Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>