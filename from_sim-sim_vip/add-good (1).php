<?
/*
	echo '-------------------';
	echo '<pre>';
		echo print_r($_POST,1);
		echo print_r($_FILES,1);
	echo '</pre>';
	echo '-------------------';
*/	
//die();

	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	require($_SERVER["DOCUMENT_ROOT"]."/mail-params.php");

	global $USER;
	$postavschik = $USER->getID();
	CModule::IncludeModule('iblock');

	require($_SERVER["DOCUMENT_ROOT"]."/add-good-function.php");	

	add_good($postavschik, $_REQUEST,$_FILES, 1);
	
?>