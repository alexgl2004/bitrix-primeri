<?

	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	
	CModule::IncludeModule('iblock');

	$ID = IntVal($_REQUEST['ID']);
	$email = strip_tags(addslashes($_REQUEST['email']));
	$IBLOCK_ID_POSTAVSCHIKS = 32;
	
	global $USER;
	
	if($ID){

		$el = new CIBlockElement;

		$arSelect = Array("ID", "PROPERTY_SUBSCRIBES");
		$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID_POSTAVSCHIKS, "PROPERTY_POSTAVSCHIK"=>$ID);
		$res = $el->GetList(Array(), $arFilter, false, Array("nTopCount"=>1), $arSelect);
		$ob = $res->GetNextElement();
		
		$count_ = $ob->fields['PROPERTY_SUBSCRIBES_VALUE'];
		$ID_elem_add_postavsckhik = $ob->fields['ID'];

		if($ID && $email){
			
			$arSelect = Array("ID");
			$arFilter = Array("IBLOCK_ID"=>33, "PROPERTY_POSTAVSCHIK"=>$ID, "PROPERTY_EMAIL"=>$email);
			$res = $el->GetList(Array(), $arFilter, false, Array("nTopCount"=>1), $arSelect);
			$ob = $res->GetNextElement();
			if($ob){
				//Такой уже есть
//				$el->SetPropertyValuesEx($ob->fields['ID'], 32, array('SUBSCRIBES'=>$count_));
			}else{
				
				$count_ = $count_ + 1;
				$el->SetPropertyValuesEx($ID_elem_add_postavsckhik, $IBLOCK_ID_POSTAVSCHIKS, array('SUBSCRIBES'=>$count_));
				$el->Add(
					array(
						"MODIFIED_BY" => $USER->GetID(),
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 33,
						"PROPERTY_VALUES"=> array(
							'EMAIL'=>$email,
							'POSTAVSCHIK'=>$ID,
							'CLIENT'=>$USER->GetID()
						),
						"NAME" => $email,
						"ACTIVE" => "Y",            // активен
					)
				);
			}
			
		}
		
		if($count_){
			echo $count_;
		}
		
	}
	
	
/*
		$count_ = ($ar_props?IntVal($ar_props["VALUE"]):0);
	
		if($_REQUEST['DO']=='1'){
			
			$_SESSION['LIKED'][$PRODUCT_ID] = 1;
			$count_ +=1;
			CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $IBLOCK_ID_GOODS, array('SYSTEM_LIKE'=>$count_));
			
		}
	
		echo $count_;
*/

?>