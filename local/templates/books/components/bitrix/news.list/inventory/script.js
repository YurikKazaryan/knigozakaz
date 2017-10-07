$(document).ready(function(){

	$('[data-toggle="tooltip"]').tooltip();

	$('#editYearPurchase').blur(function(){
		var s = parseInt($('#editYearPurchase').val());
		if (isNaN(s) || s < 1980 || s > 2050) {
			$('#editYearPurchaseAlert').slideDown('fast');
			editYearPurchaseAlert = true;
		} else {
			$('#editYearPurchaseAlert').slideUp('fast');
			editYearPurchaseAlert = false;
		}
		testAddButtonPurchase();
	});

	$('#editCountPurchase').blur(function(){
		s = parseInt($('#editCountPurchase').val());
		if (isNaN(s) || s < 1) {
			$('#editCountPurchaseAlert').slideDown('fast');
			editCountPurchaseAlert = true;
		} else {
			$('#editCountPurchaseAlert').slideUp('fast');
			editCountPurchaseAlert = false;
		}
		testAddButtonPurchase();
	});

	$('tr.clickable').click(function () {
		if ($('tr#show'+this.id).attr('class') == 'hidden')
            $('tr#show'+this.id).removeClass();
		else
            $('tr#show'+this.id).addClass('hidden');
    });

	$(".clickable").each(function () {
		var id = this.id;
        var cnt = 0;
		$("tr#show" + id).each(function () {
			cnt += parseInt($(this).children("td:eq(2)").html());
        });

		$("tr td#total_count" + id).html("<b>" + cnt + " шт. </b>");
    })

	$("button[name=SET_FILTER]").click(function () {
		if ($(this).val() == 'SET_FILTER') {
			var bookname = $("input[name=INV_FILTER_BOOKNAME]").val().trim();
			var author = $("input[name=INV_FILTER_AUTHOR]").val().trim();
			var _class = $("input[name=INV_FILTER_CLASS]").val().trim();
			var izd = $("input[name=INV_FILTER_IZD]").val().trim();
			var subject = $("input[name=INV_FILTER_SUBJECT]").val().trim();

			var filterArray = [];

			if (bookname.length > 0)
				$("div.book-name").each(function(c, bn) {
					if (bn.innerText.toLowerCase().indexOf(bookname.toLowerCase()) > -1)
						filterArray.push($(bn).closest('tr').attr('id'));
				});

            if (author.length > 0)
                $("div.book-author").each(function(c, bn) {
                    if (bn.innerText.toLowerCase().indexOf(author.toLowerCase()) > -1)
                        filterArray.push($(bn).closest('tr').attr('id'));
                });

            if (izd.length > 0)
                $("div.book-izd").each(function(c, bn) {
                    if (bn.innerText.toLowerCase().indexOf(izd.toLowerCase()) > -1)
                        filterArray.push($(bn).closest('tr').attr('id'));
                });

            if (_class.length > 0)
                $("div.book-class").each(function(c, bn) {
                    if (bn.innerText.toLowerCase().indexOf(_class.toLowerCase()) > -1)
                        filterArray.push($(bn).closest('tr').attr('id'));
                });

            if (subject.length > 0)
                $("div.book-name").each(function(c, bn) {
                    console.log(bn.innerText.toLowerCase().indexOf(subject.toLowerCase()));
                    if (bn.innerText.toLowerCase().indexOf(subject.toLowerCase()) > -1)
                        filterArray.push($(bn).closest('tr').attr('id'));
                });

            $.unique(filterArray);

			if (filterArray.length > 0) {
                $.each(filterArray, function (key, id) {
                    $('tr#' + id).attr('toBeHidden', 'no');
                });

                $.each($('tr.clickable'), function () {
                    if ($(this).attr('toBeHidden') !== 'no')
                        $(this).addClass('hidden');
                });
            } else
                if ((bookname === '') && (izd === '') && (_class === '') && (subject === '') && (author === '')) {
                    alert('Выберите условия фильтра!');
                } else {
                $('tr.clickable').addClass('hidden');
                alert('Ни одна строка не удовлетворяет условиям фильтрации!')
            }
		} else {
			$('tr.clickable').removeClass('hidden');
			$('tr.clickable').removeAttr('toBeHidden');
			$('.filter-form input').val('');
		}
	});
});

function del_inv_book(invID, bookID) {
	$.ajax({
		url: "/include/ajax/get_book_info.php",
		method: "POST",
		data: {"BOOK_ID" : bookID, "ALIGN_LEFT" : 1},
		cache: false,
		async: false,
		success: function(data){
			var result = jQuery.parseJSON(data);
			if (result.error == 0) {
				$('#delInvBookTitle').html(result.body);
				$('#delInvBookID').val(invID);
				$('#delInvBookModal').modal();
			} else {
				alert('Не найдена учебная литература с кодом ' + bookID);
			}
		},
		error: function(){
			alert('Ajax error');
		}
	});
}

function edit_inv_book(invID, bookID) {
	$.ajax({
		url: "/include/ajax/get_book_info.php",
		method: "POST",
		data: {"BOOK_ID" : bookID},
		cache: false,
		async: false,
		success: function(data){
			var result = jQuery.parseJSON(data);
			if (result.error == 0) {

				$.ajax({
					url: "/include/ajax/get_inv_info.php",
					method: "POST",
					data: {"INV_ID" : invID},
					cache: false,
					async: false,
					success: function(data){
						var inv_result = jQuery.parseJSON(data);
						if (inv_result.error == 0) {
							$('#editYearPurchase').val(inv_result.year_purchase);
							$('#editCountPurchase').val(inv_result.count);
							$('#editRemPurchase').val(inv_result.rem);
							$('#editUseCurrPurchase').val(inv_result.use_curr);
							$('#editUseNextPurchase').val(inv_result.use_next);
						} else {
							alert('Не найдена инвентаризация с кодом ' + invID);
						}
					}
				});

				$('#editBookModalTitle').html(result.body);
				$('#editInvIDPurchase').val(invID);
				$('#editBookIDPurchase').html(bookID);

	$('#editYearPurchase').blur();
	$('#editCountPurchase').blur();
				
				$('#editBookModal').modal();
			} else {
				alert('Не найдена учебная литература с кодом ' + bookID);
			}
		},
		error: function(){
			alert('Ajax error');
		}
	});
}

var editYearPurchaseAlert = true;
var editCountPurchaseAlert = true;

function testAddButtonPurchase() {
	if (editYearPurchaseAlert || editCountPurchaseAlert)
		$('#editAddButtonPurchase').attr('disabled', 'disabled');
	else
		$('#editAddButtonPurchase').removeAttr('disabled');
}