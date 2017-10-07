$(document).ready(function () {
	$('[data-toggle="tooltip"]').tooltip();
});

function oper_rem_save(mode) {
	if (mode !== 0) rem_text = $('#oper_rem_text').val(); else rem_text = '';
	$.ajax({
		url: "/include/ajax/oper_rem_save.php",
		method: "POST",
		data: {
			"ORDER_ID" : $('#oper_rem_order_id').val(),
			"TEXT" : rem_text
		},
		cache: false,
		success: function(data){
			var result = jQuery.parseJSON(data);
			$("#oper_rem_author").html('');
			$("#oper_rem_date").html('');
			$("#oper_rem_text").val('');
			if (result.delete == 'Y')
				$('#rem_flag_'+result.order_id).slideUp('fast');
			else
				$('#rem_flag_'+result.order_id).slideDown('fast');
		},
		error: function(){
			alert('Произошла ошибка! Ваш комментарий не сохранен!');
			$("#oper_rem_author").html("Ошибка");
			$("#oper_rem_date").html("Ошибка");
		}
	});
	$('#oper_rem').modal('hide');
}

function oper_rem(order_id) {
	$('#oper_rem_title').html('Комментарий к заказу №' + order_id);
	$.ajax({
		url: "/include/ajax/oper_rem_load.php",
		method: "POST",
		data: {"ORDER_ID" : order_id},
		cache: false,
		beforeSend: function(){
			$("#oper_rem_author").html('<img border="0" src="/bitrix/templates/books/images/loading.png" width="16" height="16">');
			$("#oper_rem_date").html('<img border="0" src="/bitrix/templates/books/images/loading.png" width="16" height="16">');
			$("#oper_rem_text").val('');
		},
		success: function(data){
			var result = jQuery.parseJSON(data);
			$("#oper_rem_author").html(result.author);
			$("#oper_rem_date").html(result.date);
			$("#oper_rem_order_id").val(result.order_id);
			$("#oper_rem_text").val(result.text).removeAttr('disabled');
		},
		error: function(){
			$("#oper_rem_author").html("Ошибка");
			$("#oper_rem_date").html("Ошибка");
		}
	});
	$('#oper_rem').modal('show');
}

function change_dpost(order_id, mode) {
	if (mode == 1) {
		$('#dpost_info_'+order_id).slideUp('fast');
		$('#dpost_edit_'+order_id).slideDown('fast');
		$('#dpost_input_'+order_id).focus();
	} else {
		$('#dpost_edit_'+order_id).slideUp('fast');
		$('#dpost_info_'+order_id).slideDown('fast');
	}
}

function save_dpost(order_id) {
	$.ajax({
		url: "/include/ajax/change_dpost.php",
		method: "POST",
		data: {"ORDER_ID" : order_id, "DPOST" : $("#dpost_input_"+order_id).val()},
		cache: false,
		beforeSend: function(){
			$("#dpost_name_"+order_id).html('');
		},
		success: function(data){
			var result = jQuery.parseJSON(data);
			$("#dpost_name_"+order_id).html(result.name);
		},
		error: function(){
			$("#dpost_name_"+order_id).html("Ошибка");
		}
	});
	change_dpost(order_id,0);
}

function orders_test_selection() {
	f = false;
	$.each($(".orders_select"), function(i, n) {f = f || n.checked; });
	if (f) $('#orders_action_panel').slideDown(); else $('#orders_action_panel').slideUp();
}

function orders_status(mode) {
	sList = '';
	$.each($(".orders_select"), function(i, n){
		if (n.checked) {
			if (sList.length > 0) sList += ',';
			sList += n.value;
		}
	});
	if (mode != 1 && mode != -1) mode = 0;
	sList = '/orders/?m=cng_stat&r=' + mode + '&orders=' + sList;
	window.location.href = sList;
}

function orders_pack(mode) {
	sList = '';
	$.each($(".orders_select"), function(i, n){
		if (n.checked) {
			if (sList.length > 0) sList += ',';
			sList += n.value;
		}
	});
	if (mode != 1 && mode != 2) mode = 1;
	sList = '/orders/out_pack/?mode=' + mode + '&orders=' + sList;
	window.open(sList, '');
}

function save_group_dpost() {
	sList = '';
	$.each($(".orders_select"), function(i, n){
		if (n.checked) {
			if (sList.length > 0) sList += ',';
			sList += n.value;
		}
	});
	sList = '/orders/?m=groupdpost&d=' + $('#groupDpost').val() + '&orders=' + sList;
	window.location.href = sList;
}

function change_ist(order_id, mode) {
	if (mode == 1) {
		$('#ist_info_'+order_id).slideUp('fast');
		$('#ist_edit_'+order_id).slideDown('fast');
	} else {
		$('#ist_edit_'+order_id).slideUp('fast');
		$('#ist_info_'+order_id).slideDown('fast');
	}
}

function save_ist(order_id) {
	$.ajax({
		url: "/include/ajax/change_ist.php",
		method: "POST",
		data: {"ORDER_ID" : order_id, "IST" : $("#ist_select_"+order_id).val()},
		cache: false,
		beforeSend: function(){
			$("#ist_name_"+order_id).html('');
		},
		success: function(data){
			var result = jQuery.parseJSON(data);
			$("#ist_name_"+order_id).html(result.name);
		},
		error: function(){
			$("#ist_name_"+order_id).html("Ошибка");
		}
	});
	change_ist(order_id,0);
}