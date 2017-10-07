function showBookInfo(id) {
	$.ajax({
		url: "/include/ajax/get_cat_book.php",
		method: "POST",
		data: {"BOOK_ID" : id},
		cache: false,
		async: false,
		beforeSend: function(){
			$('#book_show_body').html('');
		},
		success: function(data){
			var result = jQuery.parseJSON(data);
			if (!result.error) {
				$('#book_show_body').html(result.body);
				$('#book_show').modal();
			}
		},
		error: function(){
		}
	});
}