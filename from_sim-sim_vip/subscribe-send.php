<?
	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	require($_SERVER["DOCUMENT_ROOT"]."/mail-params.php");

	$spisok_send_mail ='';
	
	CModule::IncludeModule('iblock');

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
		  $s = str_replace("@", "_", $s); // заменяем пробелы знаком минус
		  return $s; // возвращаем результат
	}

	$spisok_send_mail ='';
	
	$email_of = stripall("",$_REQUEST["email"]);
	
	$type_of = $_REQUEST["type-mail"];
	$emails_send = stripall("",$_REQUEST["emails-send"]);
	$emails_send  = true;

	//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt','1 || '.$can_send.' || '.$emails_send.'|| yes'."\n",FILE_APPEND);	
	
	$can_send = false;

	Switch($type_of){
		case 'send-subscribe':
			$name_of_add = ' ';
			$name_of = 'Подписка на новости';
			$can_send = true;
			$title_q = '';
		break;
	}
	$generate_mail_of = '';
	$file_not_save = '';

	if($can_send){

		$arFieldsAdd = array(
			"ACTIVE" => "N",
			"IBLOCK_ID" => 33,
			"DATE_ACTIVE_FROM" => ConvertTimeStamp(time(), 'FULL'),
			"NAME" => ConvertTimeStamp(time(), 'FULL') . ' ' .$name_of.' '.$email_of,
			"CODE" => translit($name_of) . '_' . time(),
			"PROPERTY_VALUES" => array(
				"EMAIL" => $email_of,
			)
		);

		$el = new CIBlockElement();
		$el_ID = $el->Add($arFieldsAdd);
	
		if (!$el_ID){

			$arResult["ERROR_MESSAGE"][] = GetMessage("INNET_FORM_ERROR_ADD_IBLOCK");

		}
		
	}

	if($email_of) $generate_mail_of .= 'E-mail: '.$email_of."\n".'<br>'."\n";
	
//	echo $generate_mail_of." Error: Ошибка отправки.\nНе все поля заполнены!";
//	die();

	//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt','9 || '.$can_send.' || '.$emails_send.'|| yes'."\n",FILE_APPEND);

	if($can_send&&$emails_send){
		
		if(trim($spisok_send_mail)=='') $spisok_send_mail = $spisok_mail;
		
		$replayTo = ($email_of?$email_of:$from);

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= "From: ".$from . "\r\n";
		$headers_out = $headers . "Reply-To: ".$replayTo . "\r\n";
		$newsubject = 'Подписка '.$name_of_add.$name_of.' от '.$fio_of;
		$newsubject = '=?UTF-8?B?'.base64_encode($newsubject).'?=';
		
		if($email_of!='alexgl2004@gmail.com'){
			$sended = mail($spisok_send_mail,$newsubject,$generate_mail_of,$headers_out);
		}else{
			$sended = mail($email_of,$newsubject,$generate_mail_of,$headers_out);
		}
		
		if(!$sended){
			echo 'Error: Ошибка отправки почты из-за сервера! Либо не весь список параметров!'."\n";
/*			
			echo "\n".'--------------------------sended----------------------------'."\n";
			echo $sended."\n";
			echo "\n".'--------------------------spisok_send_mail---------------------------'."\n";
			echo $spisok_send_mail."\n";
			echo "\n".'--------------------------newsubject---------------------------'."\n";
			echo $newsubject."\n";
//			echo "\n".'--------------------------generate_mail_of---------------------------'."\n";
//			echo $generate_mail_of."\n";
			echo "\n".'--------------------------headers_out---------------------------'."\n";
			echo $headers_out."\n";
*/			
			//mail-send.php?type-mail=send-zakaz&fio=test&phone=test
		}else{
			echo 'Success: Отправлено на '.$spisok_send_mail;
		};
	   
	}else{
		echo "Error: Ошибка отправки.\nНе все поля заполнены!".$can_send.'|'.$emails_send;
	}
	
	header('Location: '.$_SERVER['HTTP_REFERER'].(strpos($_SERVER['HTTP_REFERER'],'?')===false?'?':'&').'subscribed=1#subscribed');

?>