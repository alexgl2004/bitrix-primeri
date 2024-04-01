<?
	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	use Bitrix\Main\Mail\Event;

	require($_SERVER["DOCUMENT_ROOT"]."/mail-params.php");
	//require_once($_SERVER['DOCUMENT_ROOT'] . '/custom_mail.php');


	$strEmail = COption::GetOptionString('main','email_from');

	$spisok_send_mail = $strEmail;

// echo"<pre>";print_r($fields);echo"</pre>";	



	CModule::IncludeModule('iblock');

	$name_of = stripall("",$_REQUEST["name"]);
	$phone_of = stripall("",$_REQUEST["phone"]);
	$email_of = stripall("",$_REQUEST["email"]);

	$fam_of = stripall("",$_REQUEST["fam"]);
	$ot_of = stripall("",$_REQUEST["ot"]);
	$region_of = stripall("",$_REQUEST["region"]);
	$pricin_of = stripall("",$_REQUEST["pricin"]);
	$coment_of = stripall("",$_REQUEST["coment"]);
	$dtr_of = stripall("",$_REQUEST["dtr"]);


	
	$emails_send = $_REQUEST["itsnospam"];
	
	$emails_send = 1;//Временная заглушка

	$type_of = $_REQUEST["type-mail"];
	
//	$typelink_of = stripall("",$_REQUEST["typelink"]);
//	$typetitle_of = stripall("",$_REQUEST["typetitle"]);
	
	$can_send = false;

	$generate_mail_of = '';
	

	if($name_of){
		$generate_mail_of .= 'Имя: '.$name_of."\n<br>";
		$_SESSION['order-user']['name'] = $name_of;
	}
		if($fam_of){
		$generate_mail_of .= 'Фамилия: '.$fam_of."\n<br>";
		$_SESSION['order-user']['fam'] = $fam_of;
	}
	if($ot_of){
		$generate_mail_of .= 'Отчество: '.$ot_of."\n<br>";
		$_SESSION['order-user']['ot'] = $ot_of;
	}	
		if($dtr_of){
		$generate_mail_of .= 'Дата рождения: '.$dtr_of."\n<br>";
		$_SESSION['order-user']['dtr'] = $dtr_of;
	}
	if($phone_of){
		$generate_mail_of .= 'Телефон: '.$phone_of."\n<br>";
		$_SESSION['order-user']['phone'] = $phone_of;
	}
	
	if($email_of){
		$generate_mail_of .= 'E-mail: '.$email_of."\n<br>";
		$_SESSION['order-user']['email'] = $email_of;
	}

	if($region_of){
		$generate_mail_of .= 'Регион: '.$region_of."\n<br>";
		$_SESSION['order-user']['redion'] = $region_of;
	}	
	if($pricin_of){
		$generate_mail_of .= 'Причина: '.$pricin_of."\n<br>";
		$_SESSION['order-user']['pricin'] = $pricin_of;
	}	
	if($coment_of){
		$generate_mail_of .= 'Коментарий: '.$coment_of."\n<br>";
		$_SESSION['order-user']['coment'] = $coment_of;
	}	


	$name_of1=$name_of;
	
	Switch($type_of){
		case 'mail-send':
			$section_id = 0;
			$name_of_add = ' Запрос с сайта';
			$name_of = 'Запрос с сайта';
			$can_send = true;
			$title_q = ' ';
		break;
	}

	if($can_send&&$emails_send){

		$el = new CIBlockElement;
		$i=0;
		if(count($_FILES['file-2']['name'])>1){

			foreach ($_FILES['file-2']['name'] as $filesx){
	                echo"<pre>";print_r($filesx);echo"</pre>";
	            $f = array(
					"name" => $_FILES['file-2']['name'][$i],
					"size" => $_FILES['file-2']['size'][$i],
					"tmp_name" => $_FILES['file-2']['tmp_name'][$i],
					"type" => $_FILES['file-2']['type'][$i],
			        );

	           	$fid[] = CFile::SaveFile($f, "");  
	           	$i=$i+1;    

			}
		}else{
	            $f = array(
					"name" => $_FILES['file-2']['name'][0],
					"size" => $_FILES['file-2']['size'][0],
					"tmp_name" => $_FILES['file-2']['tmp_name'][0],
					"type" => $_FILES['file-2']['type'][0],
			        );
	            $fid = CFile::SaveFile($f, ""); 
		}

	echo"<pre>--fid";print_r($fid);echo"</pre>";		 

		$fields = array(
				'IBLOCK_ID' => 24,
				'IBLOCK_SECTION_ID' => $section_id,
				'NAME' => $name_of.': '.$fam_of.' '.$name_of1.' '. $ot_of.', тел: '.$phone_of,
				'ACTIVE' => "Y",
				'SEARCHABLE_CONTENT' => $name_of,
				'CODE' => $type_of.'_'.date('Y_m_d_H:i:s').'__'.Cutil::translit($fam_of,"ru",$arParams),
				'PROPERTY_VALUES' => Array(
					'NAME_'=>$name_of1,
					'FAM'=>$fam_of,
					'PHONE' => $phone_of,
					'EMAIL' => $email_of,
					'REGION' => $region_of,
					'OT' => $ot_of,
					'PRICIN' => $pricin_of,
					'COMENT' => $coment_of,
					'DTR' => $dtr_of,
    				'FILEC' => $fid
				)
		);
       $el->Add($fields);

	
/********************************************************/
// echo"<pre>";print_r($_REQUEST);echo"</pre>";
echo"<pre>";print_r($_FILES);echo"</pre>";

// echo $_SERVER['DOCUMENT_ROOT'] . '/custom_mail.php';
// echo $dtr_of."|".$coment_of."|".$pricin_of."|".$region_of."|".$name_of ;


			$arMa=array(		
					'NAME_'=>$name_of1,
					'FAM'=>$fam_of,
					'PHONE' => $phone_of,
					'EMAIL' => $email_of,
					'REGION' => $region_of,
					'OT' => $ot_of,
					'PRICIN' => $pricin_of,
					'COMENT' => $coment_of,
					'DTR' => $dtr_of,
    				//'FILEC' => $fid
				);

// Event::send(array(
//     "EVENT_NAME" => "FORM_MAIN_FILE",
//     'MESSAGE_ID' => 11,
//     "LID" => "s1",
//     "C_FIELDS" => array(
//         "EMAIL" => "sanaglya@ya.ru",
//         "EMAIL_FROM" => "postmaster@h912269301.nichost.ru",
//         "EMAIL_TO" =>"sanaglya@ya.ru",
//         $arMa
//     ),
// )); 



// CEvent::Send(
// 	"FORM_MAIN_FILE",
// 	"s1",
// 	$arMa,
// 	"N"
// );



die();
/***********************************************/
		

		if(trim($spisok_send_mail)=='') $spisok_send_mail = $spisok_mail;
		
			bxmail($spisok_send_mail,'Получен'.$name_of_add.' от '.$fam_of,$generate_mail_of,"Content-type:text/html");
			if($email_of){
				bxmail($email_of,'Отправлен'.$name_of_add.' от вас, '.$fam_of,$generate_mail_of,"Content-type:text/html");
			}

			echo 'Success';//'Добавлен элемент, ID: ' . $elem_ID;
			
	}else{
		echo "Error: Ошибка отправки.\nНе все поля заполнены!";
	}
//echo 'aaaaaaaaaaaaaaaaa';
?>