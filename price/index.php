<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');

$dbres = CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID'  => 5,
        'SECTION_ID' => 105,
//        'ACTIVE'     => 'Y',
//        'PROPERTY_SUBSECTION' =>
    ],
    false,
    false,
    [
        'ID',
        'IBLOCK_ID',
        'NAME',
        'PROPERTY_CODE_1C'
    ]
);

$arData = [];

while ($item = $dbres->Fetch()) {
    $arData[my_hash($item['NAME'])] = $item['PROPERTY_CODE_1C_VALUE'];
}

if (($handle = fopen("price.csv", "r")) !== FALSE) {
    $handle2 = fopen('price_import.csv', 'w+');

    while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        $hash = my_hash(iconv('WINDOWS-1251', 'UTF-8', $data[0]));
        if (!empty($arData[$hash])) {
            $data[] = $arData[$hash];
        } else {
            $data[] = "";
        }

        fputcsv($handle2, $data, ";");
    }

    fclose($handle);
    fclose($handle2);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

function my_hash($str)
{
    return md5(str_replace(['.', ',', 'ё', '/', '\\', ' ', "\n", "\r", "\t", ";", ":", "'", "\"", "+", "-", "*", "?", "&", "%", "$", "!", "@", "#", "^", "(", ")", "=", "|", "№", "<", ">"], "", strtolower($str)));
}