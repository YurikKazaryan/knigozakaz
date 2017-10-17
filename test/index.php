<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	if ($USER->GetID() != 1) LocalRedirect('/');

	$t = testSchoolAttrib(6001);

	test_print($t);

?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>