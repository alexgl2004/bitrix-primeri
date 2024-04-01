<?

	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require("/home/bitrix/www/bitrix/modules/main/include/prolog_before.php");

	CModule::IncludeModule('iblock');
	
	$el = new CIBlockElement;
	
	$IBLOCK_ID_POSTAVSCHIKS = 32;
	
	$arSelect = Array("ID", "PROPERTY_ISNEWGOODS", "PROPERTY_POSTAVSCHIK");
	$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID_POSTAVSCHIKS,"PROPERTY_ISNEWGOODS"=>1);
//	$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID_POSTAVSCHIKS);//Для быстрого проставления городов в товарах
	$res = $el->GetList(Array(), $arFilter, false, Array("nTopCount"=>100000), $arSelect);
	
/*
	Для быстрого проставления городов в товарах
	while($ob = $res->GetNextElement()){
		$post_fields_ = $ob->GetFields();
		$rsUser = CUser::GetByID($post_fields_['PROPERTY_POSTAVSCHIK_VALUE']);
		$client_all = $rsUser->Fetch();
		$client_city = $client_all['WORK_CITY'];
		if(!$client_city){
			$client_city = 'Екатеринбург';
		}
		$arFilter = array('IBLOCK_ID'=>16,'PROPERTY_POSTAVSCHIK'=>$post_fields_['PROPERTY_POSTAVSCHIK_VALUE']);
		$res2 = $el->GetList(Array("SORT"=>"ASC"), $arFilter, false, false, array('ID'));
		while($ob2 = $res2->GetNextElement()){
			$good_fields_ = $ob2->GetFields();
			$el->SetPropertyValuesEx($good_fields_['ID'],16, array('CITY'=>$client_city));
		}
	}
		
	die();
*/
	
	
	$array_for_sender = array();
	$client_names = array();
	$emails_to_send = array();
	
	while($ob = $res->GetNextElement()){
		
		$fileds_ = $ob->fields;
		
		$is_new = $fileds_['PROPERTY_ISNEWGOODS_VALUE'];
		if($is_new){
			$POSTAVSCHIK_ID = $fileds_['PROPERTY_POSTAVSCHIK_VALUE'];
			$array_for_sender[] = $POSTAVSCHIK_ID;
			
			$rsUser = CUser::GetByID($fileds_['PROPERTY_POSTAVSCHIK_VALUE']);
			$client_all = $rsUser->Fetch();
			$client_name = ($client_all['WORK_COMPANY']?$client_all['WORK_COMPANY']:$client_all['NAME']);
			$client_names[$POSTAVSCHIK_ID] = $client_name;
			$el->SetPropertyValuesEx($fileds_['ID'], $IBLOCK_ID_POSTAVSCHIKS, array('ISNEWGOODS'=>'0'));
		}
		
	}
	
	if(count($array_for_sender)>0){
		
		$arSelect = Array("ID", "PROPERTY_EMAIL","PROPERTY_POSTAVSCHIK");
		$arFilter = Array("IBLOCK_ID"=>33, "PROPERTY_POSTAVSCHIK"=>$array_for_sender);
		$res = $el->GetList(Array(), $arFilter, false, Array("nTopCount"=>100000), $arSelect);
		
		while($ob = $res->GetNextElement()){
			$fileds_ = $ob->fields;
			$emails_to_send[$fileds_['PROPERTY_POSTAVSCHIK_VALUE'].'_'.$fileds_['PROPERTY_EMAIL_VALUE']]['ID'] = $fileds_['PROPERTY_POSTAVSCHIK_VALUE'];
			$emails_to_send[$fileds_['PROPERTY_POSTAVSCHIK_VALUE'].'_'.$fileds_['PROPERTY_EMAIL_VALUE']]['NAME'] = $client_names[$fileds_['PROPERTY_POSTAVSCHIK_VALUE']];
			$emails_to_send[$fileds_['PROPERTY_POSTAVSCHIK_VALUE'].'_'.$fileds_['PROPERTY_EMAIL_VALUE']]['EMAIL'] = $fileds_['PROPERTY_EMAIL_VALUE'];
//			echo $fileds_['PROPERTY_POSTAVSCHIK_VALUE'].'||'.$client_names[$fileds_['PROPERTY_POSTAVSCHIK_VALUE']].'||'.$fileds_['PROPERTY_EMAIL_VALUE'].'<br>';
		}
		
	}
	
	if(count($emails_to_send)>0){
		foreach($emails_to_send as $elem){
			
			$head_out = 'Поставщик "'.$elem['NAME'].'" добавил новые товары на сайт sim-sim.vip';
			$text_out = 'Поставщик "'.$elem['NAME'].'" добавил новые товары на сайт sim-sim.vip.<br>Вы можете взглянуть на них, перейдя по ссылке <a href="https://sim-sim.vip/providers/goods/?ID='.$elem['ID'].'">https://sim-sim.vip/providers/goods/?ID='.$elem['ID'].'</a>';
			
			$send2 = mail($elem['EMAIL'], $head_out, nl2br($text_out),
				"MIME-Version: 1.0\r\n"
				."Content-type: text/html; utf-8\r\n"
				."From: info@sim-sim.vip\r\n"
				."Reply-To: info@sim-sim.vip"."\r\n"
				."X-Mailer: PHP/" . phpversion());
		}
	}
/*
	echo '<pre>';
	print_r($emails_to_send);
*/	
/*	
	print_r($array_for_sender);
	print_r($client_names);
*/	
	
	//"Поставщик "ИМЯ" добавил новые товары на сайт sim-sim.vip"	

?>