<?
$aMenuLinks = Array(
	Array(
		"<span class=\"glyphicon glyphicon-home\"></span>", 
		"/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Новости", 
		"/news/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Учебники", 
		"/books/", 
		Array(), 
		Array(), 
		"getOptions('SHOW_CATALOG_NOUSER') || \$USER->IsAuthorized()" 
	),
	Array(
		"Администратор", 
		"/admin/", 
		Array(), 
		Array(), 
		"CSite::InGroup(array(1,6,7))" 
	),
	Array(
		"Оператор", 
		"/oper/", 
		Array(), 
		Array(), 
		"CSite::InGroup(array(1,9))" 
	),
	Array(
		"Фонды", 
		"/inventory/", 
		Array(), 
		Array(), 
		"CSite::InGroup(array(1,8))" 
	),
	Array(
		"Конструктор отчётов", 
		"/reports/build/", 
		Array(), 
		Array(), 
		"CSite::InGroup(array(1,6,7))" 
	),
	Array(
		"Информация", 
		"/info/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Контакты", 
		"/info/contacts/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"<span class=\"glyphicon glyphicon-log-out\" aria-hidden=\"true\"></span> Выход", 
		"/?logout=yes", 
		Array(), 
		Array(), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"<span class=\"glyphicon glyphicon-log-in\" aria-hidden=\"true\"></span> Вход", 
		"/auth/", 
		Array(), 
		Array(), 
		"!\$USER->IsAuthorized()" 
	)
);
?>