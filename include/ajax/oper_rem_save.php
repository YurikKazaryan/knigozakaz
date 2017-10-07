<?
/********************************************
* ���������� ����������� ��������� � ������
*
* ��������� (���������� ����� POST)
*    ORDER_ID - ID ������
*    TEXT - ����� �����������
* ���� TEXT ������ - ����������� ���������
*********************************************/
// ���������� API ��������
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define('LANG', 'ru');
define("NO_KEEP_STATISTIC", true);
require($_SERVER["DOCUMENT_ROOT"]."/include/bav.php");

// ��������� ����������
$order_id = ($_POST['ORDER_ID'] ? intval($_POST['ORDER_ID']) : 0);
$rem_text = trim($_POST['TEXT']);

// ���������� �������� ����� ������ ��������
if ($order_id && is_user_in_group(9)) {
	if(CModule::IncludeModule('iblock')) {
		// ���� ����� �� ������ - ��������� ������
		if (strlen($rem_text) > 0)	$rem_text = time() . '@@@' . $USER->GetFullName() . '@@@' . $rem_text;

		// ���������� � ����
		CIBlockElement::SetPropertyValuesEx($order_id, 11, array('OPER_REM' => $rem_text));

		$result = array('delete' => strlen($rem_text) > 0 ? 'N' : 'Y', 'order_id' => $order_id);
	}
}

// ������ ���������
echo json_encode($result);

// ��������� API ��������
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>