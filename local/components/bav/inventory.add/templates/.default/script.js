var yearPurchaseAlert = true;
var countPurchaseAlert = true;

$(document).ready(function(){

	$('#findStr').keypress(function(e){
		if (e.which == 13) $('#addFindButton').click();
	});

	$('#yearPurchase').blur(function(){
		var s = parseInt($('#yearPurchase').val());
		if (isNaN(s) || s < 1980 || s > 2050) {
			$('#yearPurchaseAlert').slideDown('fast');
			yearPurchaseAlert = true;
		} else {
			$('#yearPurchaseAlert').slideUp('fast');
			yearPurchaseAlert = false;
		}
		testAddButtonPurchase();
	});

	$('#countPurchase').blur(function(){
		s = parseInt($('#countPurchase').val());
		if (isNaN(s) || s < 1) {
			$('#countPurchaseAlert').slideDown('fast');
			countPurchaseAlert = true;
		} else {
			$('#countPurchaseAlert').slideUp('fast');
			countPurchaseAlert = false;
		}
		testAddButtonPurchase();
	});

	$('#newBookYear').blur(function(){
		var s = parseInt($('#newBookYear').val());
		if (isNaN(s) || s < 1980 || s > 2050) {
			$('#newBookYearAlert').slideDown('fast');
			newBookYear = true;
		} else {
			$('#newBookYearAlert').slideUp('fast');
			newBookYear = false;
		}
		test_new_book_button();
	});

	$('#newBookTitle').blur(function(){
		var s = $('#newBookTitle').val();
		if (s.length < 10) {
			$('#newBookTitleAlert').slideDown('fast');
			newBookTitle = true;
		} else {
			$('#newBookTitleAlert').slideUp('fast');
			newBookTitle = false;
		}
		test_new_book_button();
	});

	$('#newBookClass').blur(function(){
		var s = $('#newBookClass').val();
		if (s.length < 1) {
			$('#newBookClassAlert').slideDown('fast');
			newBookClass = true;
		} else {
			$('#newBookClassAlert').slideUp('fast');
			newBookClass = false;
		}
		test_new_book_button();
	});

	$('#newBookIzd').blur(function(){
		var s = parseInt($('#newBookIzd').val());
		if (s = 0) {
			$('#newBookIzdAlert').slideDown('fast');
			newBookIzd = true;
		} else {
			$('#newBookIzdAlert').slideUp('fast');
			newBookIzd = false;
		}
		test_new_book_button();
	});

	$('#addButtonNewYearCount').click(function() {
		var html = '';
		var i = 2;
		html += '<hr><div class="form-group form-group-first">' +
			'<label>Год приобретения&nbsp;</label>' +
			'<input class="form-control" name="YEAR_PURCHASE' + i + '" id="yearPurchase">' +
			'<div class="alert alert-danger" hidden id="yearPurchaseAlert">' +
			'Нужно указать 4-значный год приобретения! Например: 2010' +
			'</div>' +
			'</div>' +
			'<div class="form-group">' +
			'<label>Количество&nbsp;</label>' +
			'<input class="form-control" name="COUNT' + i + '" id="countPurchase">' +
			'<div class="alert alert-danger" hidden id="countPurchaseAlert">' +
			'Количество должно быть больше 0!' +
			'</div>' +
			'</div>';
		$('#addBookModal .form-group:last').append(html);
		$('#bookIDPurchase_YC').val(i);
		i ++;
	});

});

function testAddButtonPurchase() {
	if (yearPurchaseAlert || countPurchaseAlert)
		$('#addButtonPurchase').attr('disabled', 'disabled');
	else
		$('#addButtonPurchase').removeAttr('disabled');
}

function addNewBookShow() {
	$('#addNewBook').slideDown('fast');
}

function addBookModal(bookID) {
	$.ajax({
		url: "/include/ajax/get_book_info.php",
		method: "POST",
		data: {"BOOK_ID" : bookID},
		cache: false,
		async: false,
		success: function(data){
			var result = jQuery.parseJSON(data);
			if (result.error == 0) {
				$('#addBookModalTitle').html(result.body);
				$('#bookIDPurchase').val(bookID);
			} else {
				$('#addBookModalTitle').html('Не найдена учебная литература с кодом ' + bookID);
			}
		},
		error: function(){
			alert('Ajax error');
		}
	});
	$('#addBookModal').modal();
}

function add_find(maxResult) {
	$('.add-control').attr('disabled', 'disabled');

	$('#addFindResult').attr('hidden', 'hidden');
	$('#addFindResultBody').html('');

	$btnName = $('#addFindButton').html();
	$('#addFindButton').html('Поиск...');
	$('.add-find-res').attr('hidden', 'hidden');
	$.ajax({
		url: "/include/ajax/add_find_books.php",
		method: "POST",
		data: {"FIND_STR" : $('#findStr').val(), "MAX_RESULT" : maxResult},
		cache: false,
		async: false,
		success: function(data){
			var result = jQuery.parseJSON(data);

			if (result.cnt <= maxResult) {
				$('#addFindResultBody').html(result.body);
				$('#addFindCount').html(result.cnt);
				if (result.cnt == 0)
					$('#addFindRes2').removeAttr('hidden');
				else {
					$('#addFindRes1').removeAttr('hidden');
					$('#addFindRes4').removeAttr('hidden');
				}
			} else {
				$('#addFindRes3').removeAttr('hidden');
			}

			$('#addFindResult').slideDown('fast');
		},
		error: function(){
			alert('Ajax error');
		}
	});
	$('#addFindButton').html($btnName);
	$('.add-control').removeAttr('disabled');
}

var newBookTitle = true;
var newBookIzd = true;
var newBookYear = true;
var newBookClass = true;

function cancel_new_book() {
	$('.new-book-field').val('');
	$('#newBookIzd').val(0);
	newBookTitle = true;
	newBookIzd = true;
	newBookYear = true;
	newBookClass = true;
	test_new_book_button();
	$('#addNewBook').slideUp('fast');
}

function test_new_book_button() {
	if (newBookTitle  || newBookIzd || newBookYear || newBookClass)
		$('#newBookButton').attr('disabled', 'disabled');
	else
		$('#newBookButton').removeAttr('disabled');
}

function add_new_book() {
	$('.form-control').attr('disabled','disabled');
	$.ajax({
		url: "/include/ajax/add_new_book_to_catalog.php",
		method: "POST",
		data: {
			"AUTHOR" : $('#newBookAuthor').val(),
			"TITLE" : $('#newBookTitle').val(),
			"IZD" : $('#newBookIzd').val(),
			"FP_CODE" : $('#newBookFPCode').val(),
			"YEAR" : $('#newBookYear').val(),
			"CLASS" : $('#newBookClass').val(),
			"UMK" : $('#newBookUmk').val(),
			"SYSTEM" : $('#newBookSystem').val(),
			"EFU" : $('#efu').val()
		},
		cache: false,
		async: false,
		success: function(data){
			var result = jQuery.parseJSON(data);
			if (result.error == 0) {
				$('.form-control').removeAttr('disabled');
				cancel_new_book(); // закрываем форму добавления и очищаем ее
				addBookModal(result.id);
			} else {
				alert(result.error_text);
			}
		},
		error: function(){
			alert('Ajax error');
		}
	});
	$('.form-control').removeAttr('disabled');
}