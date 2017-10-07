$(document).ready(function(){
	$("#schoolNewForm").validate({
		rules: {
			NAME: {
				required: true,
				minlength: 10
			},
			schoolMun: "required"
		},
		messages: {
			NAME: '<div class="label label-danger" role="alert">Укажите краткое наименование школы (не менее 10 символов)!</div>',
			schoolMun: '<div class="label label-danger" role="alert">Необходимо выбрать муниципалитет!</div>'
		}
	});
});
