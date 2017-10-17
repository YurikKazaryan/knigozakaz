<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?

for ($i=1; $i<=34; $i++)
	echo passwordGenerator(8) . '<br>';

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>