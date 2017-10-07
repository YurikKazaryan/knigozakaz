//************************************************
//* ��������� cookie
//* expire - ����� ����� � ��� �� �������� �������
//************************************************
function setCookie(name, value, expire) {
	var date = new Date(new Date().getTime() + expire * 1000);
	document.cookie = name + "=" + value + "; path=/; expires=" + date.toUTCString();
}

//*****************
//* �������� cookie
//*****************
function delCookie(name) {
	var date = new Date(0);
	document.cookie = name + "=; path=/; expires=" + date.toUTCString();
}

//**********************************************************************************************
//* ������������ ������������ �� �������� orderID (����� ����� ������� ID, ����������� ��������)
//**********************************************************************************************
function makeSpec(orderID, asyncMode = true) {
	$.ajax({
		url: "/include/PHPExcel_ajax/make_spec.php",
		method: "POST",
		async: asyncMode,
		data: {"ORDER_ID" : orderID},
		cache: false,
		success: function(data) {
			var result = jQuery.parseJSON(data);
			if (result.error_list !== 0) {
				return result.error_list;
			}
		}
	});
}