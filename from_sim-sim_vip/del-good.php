<?

	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	require($_SERVER["DOCUMENT_ROOT"]."/mail-params.php");

	$spisok_send_mail ='';
	
//	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_mail.txt','1');
	
	CModule::IncludeModule('iblock');

	$el_id_Delete = $_REQUEST['id'];
	global $USER;

	$id_iblok_catalog= 16; //это число-id инфоблока 
	$db_props = CIBlockElement::GetProperty($id_iblok_catalog, $el_id_Delete, array("sort" => "asc"), Array("CODE"=>"POSTAVSCHIK"));
	
	if($ar_props = $db_props->Fetch()){
		print_r($ar_props);
		$USER_ID_TOVAR = IntVal($ar_props["VALUE"]);
	}else{
		$USER_ID_TOVAR = false;
	}
	
	if($USER->getid()==$USER_ID_TOVAR){
	
		$el = new CIBlockElement();
		$el_ID = $el->Delete($el_id_Delete);
		
		header('Location: /personal/profile/goods/');
			
	}else{
		echo "Error: Ошибка сохранения.\n Или Товар не ваш или свяжитесь с администратором!";
	}
//	echo 'aaa';
?>