<?

if(!function_exists('translit')){
	
	function translit($s){
		  $s = (string) $s; // преобразуем в строковое значение
		  $s = strip_tags($s); // убираем HTML-теги
		  $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
		  $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
		  $s = trim($s); // убираем пробелы в начале и конце строки
		  $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
		  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
		  $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
		  $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
		  return $s; // возвращаем результат
	}
	
}

//RegisterModuleDependences("main", "OnBeforeUserRegister", $this->MODULE_ID, "CWBroker", "OnBeforeUserRegister");
AddEventHandler("main", "OnBeforeUserRegister", Array("CWBroker", "OnBeforeUserRegister"));
AddEventHandler("main", "OnAfterUserRegister", Array("CWBroker", "OnAfterUserRegister"));
AddEventHandler("main", "OnBeforeUserUpdate", Array("CWBroker", "OnBeforeUserUpdate"));
AddEventHandler("main", "EventOnBeforeAdd", Array("CWBroker", "EventOnBeforeAdd"));

class CWBroker {
	
	public static function EventOnBeforeAdd($arFields){
		
		if($arFields['EVENT_NAME']=='STARTSHOP_NEW_ORDER'){
			
//			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_cart_EventOnBeforeAdd.txt',print_r($arFields,1),FILE_APPEND);
			
			global $DB;

			$strSql = "
				SELECT 
				 	* 
				FROM 
					`startshop_order_items` 
				WHERE 
					`ORDER` = '".$arFields['C_FIELDS']['ORDER_ID']."' 
				";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			
			$array_of_goods = array();
			$array_of_items_id = array();
			$array_of_postavschik_id = array();
			
			$ALL_SUM = 0;
			$arFields_ORDER_LIST = '';
			
			while($res_fields = $res->Fetch()){
				
//				echo '<pre>'.print_r($res_fields,1).'</pre>';

//				if($res_fields['QUANTITY']>=100 || $res_fields['QUANTITY']>=1000){
					$array_of_items_id[] = $res_fields['ITEM'];
//				}

				
				$array_of_goods[$res_fields['ITEM']]['ORDER']			=	$res_fields['ORDER'];
				$array_of_goods[$res_fields['ITEM']]['ITEM']				=	$res_fields['ITEM'];
				$array_of_goods[$res_fields['ITEM']]['NAME']			= 	$res_fields['NAME'];
				
//				print_r($_SESSION['ADD_PROPERTY']);
				
				if(isset($_SESSION['ADD_PROPERTY'][$res_fields['ITEM']])){
					$temp___ = $_SESSION['ADD_PROPERTY'][$res_fields['ITEM']];
					if($temp___['SIZE']){
						$array_of_goods[$res_fields['ITEM']]['NAME'] .= '. Размер: '.$temp___['SIZE'];
					}
					if($temp___['COLOR']){
						$array_of_goods[$res_fields['ITEM']]['NAME'] .= '. Цвет: '.$temp___['COLOR'];
						$array_of_goods[$res_fields['ITEM']]['COLOR']	 = $temp___['COLOR'];
					}
				}
				
				$array_of_goods[$res_fields['ITEM']]['QUANTITY']		= 	$res_fields['QUANTITY'];
				$array_of_goods[$res_fields['ITEM']]['PRICE']			=	$res_fields['PRICE'];
				
			}
			
			if(count($array_of_items_id)>0){

			    $dbEl = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>16, 'ID'=>$array_of_items_id));
				while($obEl = $dbEl->GetNextElement()){
				
					$fields = $obEl->GetFields();
					$props = $obEl->GetProperties();

					$array_of_goods[$fields['ID']]['PRICE_BASE'] = $array_of_goods[$fields['ID']]['PRICE'];
					
					if($array_of_goods[$fields['ID']]['QUANTITY']>=100){
						$array_of_goods[$fields['ID']]['PROCENT'] = (float)$props['STARTSHOP_PROCENT_2']['VALUE'];
						$array_of_goods[$fields['ID']]['PRICE'] = $array_of_goods[$fields['ID']]['PRICE_BASE'] - $array_of_goods[$fields['ID']]['PRICE_BASE'] * ($array_of_goods[$fields['ID']]['PROCENT']/100);
					}else{
						$array_of_goods[$fields['ID']]['PROCENT'] = 0;
					}

					if($array_of_goods[$fields['ID']]['QUANTITY']>=1000){
						$array_of_goods[$fields['ID']]['PROCENT'] = (float)$props['STARTSHOP_PROCENT_3']['VALUE'];
						$array_of_goods[$fields['ID']]['PRICE'] = $array_of_goods[$fields['ID']]['PRICE_BASE'] - $array_of_goods[$fields['ID']]['PRICE_BASE'] * ($array_of_goods[$fields['ID']]['PROCENT']/100);
					}
					
					$array_of_goods[$fields['ID']]['SUB_SUM'] = $array_of_goods[$fields['ID']]['PRICE'] * $array_of_goods[$fields['ID']]['QUANTITY'];
					$ALL_SUM += $array_of_goods[$fields['ID']]['SUB_SUM'];
					
					$array_of_goods[$fields['ID']]['POSTAVSCHIK_ID'] = $props['POSTAVSCHIK']['VALUE'];
					
					if((int)$props['POSTAVSCHIK']['VALUE']>0){
						$rsUser = CUser::GetByID($props['POSTAVSCHIK']['VALUE']);
						$arUser = $rsUser->Fetch();
						$array_of_goods[$fields['ID']]['POSTAVSCHIK_NAME'] = ($arUser['LAST_NAME']?$arUser['LAST_NAME']:'').($arUser['NAME']?' '.$arUser['NAME']:'').($arUser['MIDDLE_NAME']?' '.$arUser['MIDDLE_NAME']:'');
						$array_of_goods[$fields['ID']]['POSTAVSCHIK_EMAIL'] = $arUser['EMAIL'];
						$array_of_goods[$fields['ID']]['POSTAVSCHIK_PHONE'] = $arUser['WORK_PHONE'];
						$array_of_goods[$fields['ID']]['POSTAVSCHIK_COMPANY'] = $arUser['WORK_COMPANY'];
						
					}
					
					$array_of_goods[$fields['ID']]['POSTAVSCHIK_LINE'] = $array_of_goods[$fields['ID']]['NAME'].($array_of_goods[$fields['ID']]['COLOR']?'<span style="margin-left:10px;display:inline-block;vertical-align:middle;width:20px;height:20px;background:'.$array_of_goods[$fields['ID']]['COLOR'].'"></span>':'').' - '.$array_of_goods[$fields['ID']]['QUANTITY'].' шт., '.number_format($array_of_goods[$fields['ID']]['PRICE'],2,'.',' ').' руб.: '.number_format($array_of_goods[$fields['ID']]['SUB_SUM'],2,'.',' ').' руб.'."\n";

					$arFields_ORDER_LIST .= $array_of_goods[$fields['ID']]['POSTAVSCHIK_LINE'];
					
					if($array_of_goods[$fields['ID']]['PROCENT']>0 && $array_of_goods[$fields['ID']]['ORDER']){
						$strSql_new = "UPDATE `startshop_order_items` SET `NAME` = '".$array_of_goods[$fields['ID']]['NAME']."', `PRICE` = '".$array_of_goods[$fields['ID']]['PRICE']."' WHERE `ORDER` = '".$array_of_goods[$fields['ID']]['ORDER']."' AND `ITEM` = '".$fields['ID']."'";
						$res_new = $DB->Query($strSql_new, false, $err_mess.__LINE__);
					}
					
				}

			}
			
			$arFields['C_FIELDS']['ORDER_AMOUNT'] = number_format($ALL_SUM,2,'.',' ').' руб.';
			$arFields['C_FIELDS']['STARTSHOP_ORDER_LIST'] = $arFields_ORDER_LIST."\n";
			$arFields['C_FIELDS']['STARTSHOP_THEME_EMAIL'] = 'Информация информацией о всех товарах в заказе №'.$elem['C_FIELDS']['ORDER_ID'].' с сайта sim-sim.vip';
			
			$arr_for_mails = array();
			
			foreach($array_of_goods as $elem){
				$arr_for_mails[$elem['POSTAVSCHIK_ID']]['EMAIL'] = $elem['POSTAVSCHIK_EMAIL'];
				$arr_for_mails[$elem['POSTAVSCHIK_ID']]['NAME'] = $elem['POSTAVSCHIK_NAME'];
				$arr_for_mails[$elem['POSTAVSCHIK_ID']]['COMPANY'] = $elem['POSTAVSCHIK_COMPANY'];
				
				if(!isset($arr_for_mails[$elem['POSTAVSCHIK_ID']]['STARTSHOP_ORDER_LIST'])){
					$arr_for_mails[$elem['POSTAVSCHIK_ID']]['STARTSHOP_ORDER_LIST'] = '';
				}
				if(!isset($arr_for_mails[$elem['POSTAVSCHIK_ID']]['SUM'])){
					$arr_for_mails[$elem['POSTAVSCHIK_ID']]['SUM'] = 0;
				}
				$arr_for_mails[$elem['POSTAVSCHIK_ID']]['STARTSHOP_ORDER_LIST'] .= $elem['POSTAVSCHIK_LINE'];
				$arr_for_mails[$elem['POSTAVSCHIK_ID']]['SUM'] += $elem	['SUB_SUM'];
			}
			
			$arr_for_mails_events = array();
			
			foreach($arr_for_mails as $k=>$elem){
				
				$arr_for_mails_events[$k] = $arFields;
				
				$arr_for_mails_events[$k]['C_FIELDS']['ORDER_AMOUNT'] = number_format($elem['SUM'],2,'.',' ').' руб.';
//				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_CLIENT_EMAIL'] = $arFields['C_FIELDS']['STARTSHOP_CLIENT_EMAIL'];
				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_POSTAVSCHIK_EMAIL'] = $elem['EMAIL'];
				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_POSTAVSCHIK_COMPANY'] = $elem['COMPANY'];
				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_ORDER_LIST'] = $elem['STARTSHOP_ORDER_LIST'];
//				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_THEME_EMAIL'] = 'Ваш заказ №'.$elem['C_FIELDS']['ORDER_ID'].' в '.$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_COMPANY'].' с сайта sim-sim.vip';
			}

//			echo '<pre>'.print_r($arr_for_mails_events,1).'</pre>';
			
//			CModule::IncludeModule('event');
//			$event_send = new CEvent;
			
			foreach($arr_for_mails_events as $k=>$elem){

//				$sended = 
/*
				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_THEME_EMAIL'] = 'Ваш заказ №'.$elem['C_FIELDS']['ORDER_ID'].' в '.$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_COMPANY'].' с сайта sim-sim.vip';
				CEvent::Send($arr_for_mails_events[$k]['EVENT_NAME'], $arr_for_mails_events[$k]['LID'], $arr_for_mails_events[$k]['C_FIELDS']);

				$temp_mail = $arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_CLIENT_EMAIL'];
				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_CLIENT_EMAIL'] = $arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_POSTAVSCHIK_EMAIL'];
				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_POSTAVSCHIK_EMAIL'] = $temp_mail;

				$arr_for_mails_events[$k]['C_FIELDS']['STARTSHOP_THEME_EMAIL'] = 'Заказ №'.$elem['C_FIELDS']['ORDER_ID'].' с сайта sim-sim.vip';
				CEvent::Send($arr_for_mails_events[$k]['EVENT_NAME'], $arr_for_mails_events[$k]['LID'], $arr_for_mails_events[$k]['C_FIELDS']);
*/

				$temp_text = "Заказ №".$elem['C_FIELDS']['ORDER_ID'].' в '.$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_COMPANY']."\n\n";
				$temp_text .= "Содержание заказа:"."\n";
				$temp_text .= $elem['C_FIELDS']['STARTSHOP_ORDER_LIST'];
				$temp_text .= 'Стоимость заказа: '.$elem['C_FIELDS']['ORDER_AMOUNT'];
				$temp_text .= ($elem['C_FIELDS']['ORDER_DELIVERY']?'Стоимость доставки: '.number_format($elem['C_FIELDS']['ORDER_DELIVERY'],2,'.',' ').' руб.':'')."\n";
				$temp_text .= 'Оплата: '.$elem['C_FIELDS']['ORDER_PAYMENT']."\n";
				

				$send2 = mail($elem['C_FIELDS']['STARTSHOP_CLIENT_EMAIL'], 'Ваш заказ №'.$elem['C_FIELDS']['ORDER_ID'].' в '.$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_COMPANY'].' с сайта sim-sim.vip', nl2br($temp_text),
					"MIME-Version: 1.0\r\n"
					."Content-type: text/html; utf-8\r\n"
					."From: info@sim-sim.vip\r\n"
					."Reply-To: ".$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_EMAIL']."\r\n"
					."X-Mailer: PHP/" . phpversion());

				$send1 = mail($elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_EMAIL'], 'Получен заказ №'.$elem['C_FIELDS']['ORDER_ID'].' в '.$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_COMPANY'].' с сайта sim-sim.vip', nl2br($temp_text),
					"MIME-Version: 1.0\r\n"
					."Content-type: text/html; utf-8\r\n"
					."From: info@sim-sim.vip\r\n"
					."Reply-To: ".$elem['C_FIELDS']['STARTSHOP_CLIENT_EMAIL']."\r\n"
					."X-Mailer: PHP/" . phpversion());


/*
				echo $k.'||'.$send1.'||'.$send2.'<br>';
				echo $k.'||'.$elem['C_FIELDS']['STARTSHOP_POSTAVSCHIK_EMAIL'].'||'.'alexgl2004@gmail.com'.'<br>';				
*/

			}

			$arFields['C_FIELDS']['STARTSHOP_CLIENT_EMAIL'] = 'sim-sim.vip@yandex.ru';
			
			unset($_SESSION['ADD_PROPERTY']);
			
//			echo '<pre>'.print_r($array_of_goods,1).'</pre>';
//			echo '<pre>'.print_r($arFields,1).'</pre>';
//			echo '<pre>'.print_r($arr_for_mails,1).'</pre>';

			
			
/*			
			startshop_order
				ID
			startshop_order_items 
				ORDER	ITEM	NAME	QUANTITY	PRICE
*/
			
//			die();
			
		}
		
	}
	

	public static function OnBeforeUserRegister(&$arFields) {
		
		if($_REQUEST['POSTAVSCHIK']){
			
			global $APPLICATION;
			
			$_REQUEST['USER_PERSONAL_MOBILE'] = preg_replace('#[^0-9]#', '', $_REQUEST['USER_PERSONAL_MOBILE']);
			
			$pass_ = substr(md5(date('Y-m-d H:i:s').rand(10,20).$arFields['EMAIL']),1,8);
			
//			echo $pass_;

			$arFields['LOGIN'] = $arFields['EMAIL'];
			$arFields['CONFIRM_PASSWORD'] = $arFields['PASSWORD'];
			
			$arFields['NAME'] = $_REQUEST['USER_NAME'];
			$arFields['LAST_NAME'] = $_REQUEST['USER_LAST_NAME'];
			$arFields['EMAIL'] = $_REQUEST['USER_EMAIL'];
			$arFields['PERSONAL_PHONE'] = $_REQUEST['USER_PERSONAL_MOBILE'];
			$arFields['WORK_PHONE'] = $_REQUEST['USER_PERSONAL_MOBILE'];
			$arFields['WORK_COUNTRY'] = $_REQUEST['USER_PERSONAL_COUNTRY'];
			$arFields['WORK_STATE'] = $_REQUEST['USER_STATE'];
			$arFields['WORK_CITY'] = $_REQUEST['USER_CITY'];
			$arFields['WORK_COMPANY'] = $_REQUEST['USER_COMPANY'];
			$arFields['WORK_STREET'] = $_REQUEST['USER_ADDRESS'];
			$arFields['LOGIN'] = $_REQUEST['USER_EMAIL'];
//			$arFields['LOGIN'] = $_REQUEST['USER_LOGIN'];
			$arFields['PASSWORD'] = $pass_;
			$arFields['CONFIRM_PASSWORD'] = $pass_;
			
			$arFields["GROUP_ID"][]=5;
			
/*
		echo '<pre>'.print_r($arFields,1).'</pre>';
		echo '<pre>'.print_r($_REQUEST,1).'</pre>';
		die();
*/

			if (empty($arFields['NAME'])) {
				$APPLICATION->ThrowException('Не указано имя');
				return false;
			}
			if (empty($arFields['LAST_NAME'])) {
				$APPLICATION->ThrowException('Не указана фамилия');
				return false;
			}
			if (empty($arFields['WORK_PHONE'])) {
				$APPLICATION->ThrowException('Не указан мобильный телефон');
				return false;
			}
			if (empty($arFields['WORK_STATE'])) {
				$APPLICATION->ThrowException('Не указан регион');
				return false;
			}

			if ($_REQUEST['USER_AGREE_TERMS'] <> 'Y'/* || $_REQUEST['USER_AGREE_PHONE'] <> 'Y' || $_REQUEST['USER_AGREE_EMAIL'] <> 'Y'*/) {
				$APPLICATION->ThrowException('Необходимо принять все условия');
				return false;
			}
			
		}else{
			$arFields["GROUP_ID"][]=6;
		}

		return $arFields;
	}

	public static function OnBeforeUserUpdate(&$arFields){
//die();		
		global $USER;
		$user_id = $USER->getID();

		CModule::IncludeModule('iblock');
		$el = new CIBlockElement();
		
		$user_id_IBLOCK = 32;

		$arFilter = array('IBLOCK_ID'=>$user_id_IBLOCK,'PROPERTY_POSTAVSCHIK'=>$user_id);
		$res = $el->GetList(Array("SORT"=>"ASC"), $arFilter, false, false, array('ID'));
		$ob = $res->GetNextElement();
		
		if($ob){
			$arFields_user_post = $ob->GetFields();
		}else{
			
			$arFieldsAdd = array(		
				"ACTIVE" => "Y",	
				"IBLOCK_ID" => 32,
				"DATE_ACTIVE_FROM" => ConvertTimeStamp(time(), 'FULL'),
				"NAME" => $arFields['NAME'].' '.$arFields['LAST_NAME'],
				"CODE" => translit($arFields['NAME'].'_'.$arFields['LAST_NAME'].'_'.$arFields['WORK_COMPANY']),
				"PROPERTY_VALUES" => array(
					"POSTAVSCHIK" => $user_id,
					"ADDRESS" => $arFields['WORK_COUNTRY'].', '.$arFields['WORK_STATE'].', '.$arFields['WORK_CITY'].', '.$arFields['WORK_COMPANY'].', '.$_REQUEST['WORK_STREET']
				)
			);
			
			$el_ID = $el->Add($arFieldsAdd);
			$arFields_user_post['ID'] = $el_ID;
		}
		
/*
		echo '<pre>'.print_r($arFields_user_post,1).'</pre>';
		echo '<pre>'.print_r($arFields,1).'</pre>';
		echo '<pre>'.print_r($_REQUEST,1).'</pre>';
		
		die();
*/

		$charact_of_arr = array();
		foreach($_REQUEST["Address_ADD_CHARACT_NAME"] as $k=>$elem){
			if($_REQUEST["Address_ADD_CHARACT_NAME"][$k]){
				
				$chract_ = $_REQUEST["Address_ADD_CHARACT_VALUE"][$k];
				switch($_REQUEST["Address_ADD_CHARACT_NAME"][$k]){
					case 'telegram':
						$chars = preg_split('//u', $chract_, NULL, PREG_SPLIT_NO_EMPTY);
						if($chars[0]!='@'){
							$chract_ = '@'.$chract_;
						}
					break;
					case 'whatsapp':
						$chars = preg_split('//u', $chract_, NULL, PREG_SPLIT_NO_EMPTY);
						if($chars[0]=='+'){
							unset($chars[0]);
							if($chars[1]=='8'){
								$chars[1] = '7';
							}
							$chract_ = implode('',$chars);
						}
						if($chars[0]=='8'){
							$chars[0] = '7';
							$chract_ = implode('',$chars);
						}
					break;
				}
				
				$_REQUEST["Address_ADD_CHARACT_VALUE"][$k] = $chract_;
				
				$charact_of_arr[] = $_REQUEST["Address_ADD_CHARACT_NAME"][$k].'||'.$_REQUEST["Address_ADD_CHARACT_VALUE"][$k];
			}
		}
		
		$el->SetPropertyValuesEx($arFields_user_post['ID'],$user_id_IBLOCK, array('DOP_ADDRESS'=>$charact_of_arr));
		$el->SetPropertyValuesEx($arFields_user_post['ID'],$user_id_IBLOCK, array('ADDRESS'=>$_REQUEST['WORK_STREET']));

		$arFilter = array('IBLOCK_ID'=>16,'PROPERTY_POSTAVSCHIK'=>$user_id);
		$res = $el->GetList(Array("SORT"=>"ASC"), $arFilter, false, false, array('ID'));
		while($ob = $res->GetNextElement()){
			$good_fields_ = $ob->GetFields();
			$el->SetPropertyValuesEx($good_fields_['ID'],16, array('CITY'=>$arFields['WORK_CITY']));
		}
		
	}	
	
	public static function OnAfterUserRegister(&$arFields){
		
//		echo '<pre>'.print_r($arFields,1).'</pre>';
		
		if($arFields["USER_ID"]>0){
			
			CModule::IncludeModule('iblock');
		
			$arFieldsAdd = array(		
				"ACTIVE" => "Y",	
				"IBLOCK_ID" => 32,
				"DATE_ACTIVE_FROM" => ConvertTimeStamp(time(), 'FULL'),
				"NAME" => $arFields['NAME'].' '.$arFields['LAST_NAME'],
				"CODE" => translit($arFields['NAME'].'_'.$arFields['LAST_NAME'].'_'.$arFields['WORK_COMPANY']),
				"PROPERTY_VALUES" => array(
					"POSTAVSCHIK" => $arFields["USER_ID"],
					"ADDRESS" => $arFields['WORK_COUNTRY'].', '.$arFields['WORK_STATE'] = $_REQUEST['USER_STATE'].', '.$arFields['WORK_CITY'].', '.$arFields['WORK_COMPANY'].', '.$arFields['WORK_STREET']
				)
			);
			
			$el = new CIBlockElement();
			$el_ID = $el->Add($arFieldsAdd);
			
		}
		
//		echo $el_ID.'||';die();		
	}

}

function getResizedImgOrPlaceholder($imgId, $width, $height = "auto", $proportional = true, $position_ = 'center'){
	
    if (!$width)
        throw new \Exception( "File dimensions can not be a null" );
    $resizeType = BX_RESIZE_IMAGE_EXACT;
    $autoHeightMax = 9999;
    //
    if ($height == "auto") {
        $height = $autoHeightMax;
        $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
    }
    if (!$height) 
        $height = $width;
    if ($proportional)
        $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
    // если картинка не существует (например, пустое значение некотрого св-ва) - вернем заглушку нужного размера
    if (!$imgId) {
        // тут можно положить собственную заглушку под стиль сайта
        $customNoImg = SITE_TEMPLATE_PATH . "/upload/img_placeholder.jpg";
        // есть ограничение на размер заглушки на сайте dummyimage.com. можно еще задать цвет фона и текста.
        $height = $height == $autoHeightMax ? $width : $height;
        return file_exists($_SERVER["DOCUMENT_ROOT"] . $customNoImg) ? $customNoImg : "http://dummyimage.com/{$width}x{$height}/5C7BA4/fff";
    }
    $arFilters = [];
    /*
     * <watermark>
     * 1) получаем размер ($arDestinationSize) итоговой картинки (фото товара) после ресайза, с учетом типа ресайза ($resizeType)
     * 2) создаем водяной знак под этот размер фото (он должен быть чуть меньше самого фото)
     * 3) формируем фильтр для наложения знака
     * */
    $watermark = $_SERVER['DOCUMENT_ROOT'] . "/upload/watermark.png";
    if (is_readable($watermark)) {
        $bNeedCreatePicture = $arSourceSize = $arDestinationSize = false;
        $imgSize = \CFile::GetImageSize( $_SERVER["DOCUMENT_ROOT"] .  \CFile::GetPath($imgId) );
        \CFile::ScaleImage($imgSize["0"], $imgSize["1"], array("width" => $width, "height" => $height), $resizeType, $bNeedCreatePicture, $arSourceSize, $arDestinationSize);
        $koef = 0.95;
        $watermarkResized = $_SERVER['DOCUMENT_ROOT'] . "/upload/watermark/watermark_" . $arDestinationSize["width"] * $koef . ".png";
        if (!is_readable($watermarkResized))
            \CFile::ResizeImageFile($watermark, $watermarkResized, [ "width" => $arDestinationSize["width"] * $koef, "height" => $arDestinationSize["height"] * $koef ], BX_RESIZE_IMAGE_PROPORTIONAL, false, 95, []);
        if (is_readable($watermarkResized))
            $arFilters[] = [
                "name"     => "watermark",
                "position" => $position_,
                "size"     => "real",
                "file"     => $watermarkResized
            ];
    }
    /*
     * </watermark>
     * */
    $resizedImg = \CFile::ResizeImageGet($imgId, [ "width" => $width, "height" => $height ], $resizeType, false, $arFilters, false, 100);
    // если файл по каким-то причинам не создался - вернем заглушку
    if (!file_exists($_SERVER["DOCUMENT_ROOT"] . $resizedImg['src'])) {
        if ($height == $autoHeightMax)
            $height = $width;
        return getResizedImgOrPlaceholder(false, $width, $height, $proportional);
    }
	
    return $resizedImg['src'];
	
}

?>