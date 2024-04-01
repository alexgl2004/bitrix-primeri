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

	if(!function_exists('check_uploaded_files')){
		function check_uploaded_files(
			$file_array,
			$max_allowed_file_size = 5000000,
			$allowed_extensions = array("zip", "rar", "doc", "docx", "rtf", "pdf", "txt", "xls", "xlsx", "jpg", "jpeg", "png", "bmp", "gif")
		){
			
	//		print_r($file_array);die();
			$errors = '';
			$name_of_uploaded_file = basename($file_array['name']);
			$type_of_uploaded_file = substr($name_of_uploaded_file, strrpos($name_of_uploaded_file, '.') + 1);
	//		echo print_r($file_array["size"],1);
			$size_of_uploaded_file = $file_array["size"]/1024;

			if($size_of_uploaded_file > $max_allowed_file_size ){
				$errors .= "\n Размер файла должен быть меньше $max_allowed_file_size";
			}
			
			//------ Проверяем расширение файла -----
			$allowed_ext = false;
			for($i=0; $i<sizeof($allowed_extensions); $i++){
			// сравниваем строки, если = 0, то строки идентичны (без учета регистра)
				if(strcasecmp($allowed_extensions[$i],$type_of_uploaded_file) == 0){
					$allowed_ext = true;
				}
			}
			
			if(!$allowed_ext){
				$errors .= "\n Расширение файла не соответствует требуемому. "."Поддерживаются следующие расширения: ".implode(', ',$allowed_extensions);
			}
			
			if($errors!=''){
	//			$mainframe->redirect('/', JText::_('Ошибка загрузки файла.'.$errors),'error');
				echo "Error: Ошибка загрузки файла.".$errors;
	//			die();
				return false;
			}else{
				return true;
			}
		
		}
		
	}
	
	function add_good($postavschik, $_REQUEST_GOODS, $_FILES_GOODS, $type_=1){

//		echo $postavschik;
//		echo '<pre>'.print_r($_REQUEST_GOODS,1).'</pre>';

		$spisok_send_mail ='';
		
//		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_mail.txt','1');
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_good.txt',print_r($_REQUEST_GOODS,1));

		$spisok_send_mail ='';
		
//		echo '<pre>'.print_r($_REQUEST_GOODS,1).'</pre>';die();
		
		$product_id = stripall("",$_REQUEST_GOODS["product-id"]);

		$name_of = stripall("",$_REQUEST_GOODS["GOOD_NAME"]);
		$price_1_of = stripall("",$_REQUEST_GOODS["GOOD_PRICE"]);
		$price_2_of = stripall("",$_REQUEST_GOODS["GOOD_SALE_100"]);
		$price_3_of = stripall("",$_REQUEST_GOODS["GOOD_SALE_1000"]);
		$quantity_ratio_of = stripall("",$_REQUEST_GOODS["GOOD_QUANTITY_RATIO"]);

		$good_sku_of  = stripall("",$_REQUEST_GOODS["GOOD_SKU"]);
		$good_weight_of  = stripall("",$_REQUEST_GOODS["GOOD_WEIGHT"]);
		$good_count_of  = stripall("",$_REQUEST_GOODS["GOOD_COUNT"]);

		$section_id_of  = stripall("",$_REQUEST_GOODS["GOOD_CAT"]);

		$good_picture_arr = array();
		$good_picture_id_arr = array();

		foreach($_REQUEST_GOODS["GOOD_PICTURE_ID"] as $k=>$elem){
			if($_REQUEST_GOODS["GOOD_PICTURE_ID"][$k]){
//				$good_picture_id_arr[] = CFile::GetPath($_REQUEST_GOODS["GOOD_PICTURE_ID"][$k]);
				$good_picture_id_arr[] = $_REQUEST_GOODS["GOOD_PICTURE_ID"][$k];
			}
		}
		
//		print_r($_REQUEST_GOODS["GOOD_PICTURE_ID"]);
		
		foreach($_REQUEST_GOODS["GOOD_PICTURE_TEXT"] as $k=>$elem){
			
			if($_REQUEST_GOODS["GOOD_PICTURE_TEXT"][$k]){
				
				$good_picture_arr[] = $_REQUEST_GOODS["GOOD_PICTURE_TEXT"][$k];
				$temp_explode = explode('/',$_REQUEST_GOODS["GOOD_PICTURE_TEXT"][$k]);
				end($temp_explode);
				$key_end = key($temp_explode);
				$names_files_arr[] = $temp_explode[$key_end];

			}
		}

//		print_r($names_files_arr);


		$size_of_arr = array();
		foreach($_REQUEST_GOODS["GOOD_SIZE"] as $k=>$elem){
			if($_REQUEST_GOODS["GOOD_SIZE"][$k] || (string)$_REQUEST_GOODS["GOOD_SIZE"][$k]=='0'){
				$size_of_arr[] = $_REQUEST_GOODS["GOOD_SIZE"][$k];
			}
		}
		
		$color_of_arr = array();
		foreach($_REQUEST_GOODS["GOOD_COLOR"] as $k=>$elem){
			if($_REQUEST_GOODS["GOOD_COLOR"][$k]){
				$color_of_arr[] = $_REQUEST_GOODS["GOOD_COLOR"][$k];
			}
		}
		
		end($color_of_arr);
		$key_color = key($color_of_arr);
		if(!$color_of_arr[$key_color]){
			array_shift($color_of_arr);
		}
//		echo '<pre>'.print_r($_REQUEST_GOODS,1).'</pre>';die();

		$small_description_of = stripall("",$_REQUEST_GOODS["GOOD_SMALL_DESCRIPTION"]);
		$description_of = stripall("",$_REQUEST_GOODS["GOOD_DESCRIPTION"]);
		
		$charact_of_arr = array();
		$charact_of_arr_id = array();
		
		foreach($_REQUEST_GOODS["GOOD_ADD_CHARACT_NAME"] as $k=>$elem){
			if($_REQUEST_GOODS["GOOD_ADD_CHARACT_NAME"][$k]){
				$charact_of_arr[] = $_REQUEST_GOODS["GOOD_ADD_CHARACT_NAME"][$k].'||'.$_REQUEST_GOODS["GOOD_ADD_CHARACT_VALUE"][$k];
			}
		}
		
		foreach($_REQUEST_GOODS["GOOD_ADD_CHARACT_NAME_ID"] as $k=>$elem){
			if($_REQUEST_GOODS["GOOD_ADD_CHARACT_NAME_ID"][$k]){
				$charact_of_arr_id[$_REQUEST_GOODS["GOOD_ADD_CHARACT_NAME_ID"][$k]] = $_REQUEST_GOODS["GOOD_ADD_CHARACT_VALUE"][$k];
			}
		}
		

		$emails_send = stripall("",$_REQUEST_GOODS["product-send"]);
		
		if($name_of){
			$can_send = true;
		}
		
		$generate_mail_of = '';
		$file_not_save = '';
		
	//	echo '<pre>'.print_r($_FILES,1).'</pre>';

	//	die();

		$files_of = array();
		
		if($good_picture_arr){
			foreach($good_picture_arr as $elem){
				if($elem){
					$temp_file = CFile::MakeFileArray($elem);
					$temp_file["MODULE_ID"] = 'main';
					$fid = CFile::SaveFile($temp_file, "main");

					$temp_file = CFile::MakeFileArray($fid);
					if($temp_file){
						$files_of[] = $temp_file;
					}

					unlink($elem);

				}
			}
		}

//		print_r($files_of);		
		if($good_picture_id_arr){
			foreach($good_picture_id_arr as $elem){
				if($elem){
					$temp_file = CFile::MakeFileArray($elem);
					$temp_file["MODULE_ID"] = 'main';
					$fid = CFile::SaveFile($temp_file, "main");

					$temp_file = CFile::MakeFileArray($fid);
					if($temp_file){
						$files_of[] = $temp_file;
						$files_of_t[] = $temp_file;
					}
					unlink($elem);
				}
			}
		}

/*		
		echo "<pre>----<br>";
			echo print_r($good_picture_id_arr,1);
			echo print_r($files_of_t,1);
		echo "<br>----</pre>";
*/		
		
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_good.txt',print_r($files_of,1),FILE_APPEND);
		
		if(isset($_FILES_GOODS) && $_FILES_GOODS && $type_==1){
			
			foreach($_FILES_GOODS['GOOD_PICTURE']['name'] as $k=>$elem){

				if($_FILES_GOODS['GOOD_PICTURE']['name'][$k] && $_FILES_GOODS['GOOD_PICTURE']['size'][$k]){

					//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt','4 || '.$can_send.' || '.$emails_send.'|| yes'.print_r($_FILES_GOODS['file'],1)."\n",FILE_APPEND);		
					
	//				echo '<pre>'.print_r($_FILES_GOODS,1).'</pre>';

					$file_to_check = array(
					
						'name' => $_FILES_GOODS['GOOD_PICTURE']['name'][$k],
						'type' => $_FILES_GOODS['GOOD_PICTURE']['type'][$k],
						'tmp_name' => $_FILES_GOODS['GOOD_PICTURE']['tmp_name'][$k],
						'error' => $_FILES_GOODS['GOOD_PICTURE']['error'][$k],
						'size' => $_FILES_GOODS['GOOD_PICTURE']['size'][$k]
						
					);
	/*				
					echo '<pre>'.print_r($elem,1).'</pre>';
					echo '<br>------------<br>';
					echo '<pre>'.print_r($file_to_check,1).'</pre>';
					echo '<br>------------<br>';
					echo '<pre>'.print_r($_FILES_GOODS,1).'</pre>';
	*/
					if(check_uploaded_files($file_to_check)){
						
						$name_link = $_SERVER["DOCUMENT_ROOT"]."/upload/tmp/".$_FILES_GOODS['GOOD_PICTURE']['name'][$k];
						move_uploaded_file($_FILES_GOODS['GOOD_PICTURE']['tmp_name'][$k], $name_link);
						$temp_file = CFile::MakeFileArray($name_link);
						
		//				echo '<pre>'.print_r($temp_file,1).'</pre>';
						
						$temp_file["MODULE_ID"] = 'main';
						$fid = CFile::SaveFile($temp_file, "main");
		//				echo $fid;

						$temp_file = CFile::MakeFileArray($fid);
						$files_of[] = $temp_file;
						
						unlink($name_link);
						
					}else{
		//				$file_to_check = array();
					}
					
				}
			}
			
		}


	//echo 'aaa';
		$emails_send = 1;
		//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_mail.txt','1',FILE_APPEND);
//		echo $can_send.'&&'.$emails_send;die();
	
		if($can_send&&$emails_send){
	/*
			echo $postavschik.'<br>';
			echo 'name_of '.$name_of.'<br>';
			echo 'price_1_of '.$price_1_of.'<br>';
			echo 'price_2_of '.$price_2_of.'<br>';
			echo 'price_3_of '.$price_3_of.'<br>';

			echo 'good_sku_of '.$good_sku_of.'<br>';
			echo 'good_weight_of '.$good_weight_of.'<br>';

			echo 'section_id_of '.$section_id_of.'<br>';

			echo 'size_of_arr '.'<pre>'.print_r($size_of_arr,1).'</pre>'.'<br>';
			
			echo 'color_of_arr '.'<pre>'.print_r($color_of_arr,1).'</pre>'.'<br>';

			echo 'description_of '.$description_of.'<br>';
			
			echo 'charact_of_arr '.'<pre>'.print_r($charact_of_arr,1).'</pre>'.'<br>';

			echo 'emails_send '.$emails_send.'<br>';
			
			echo 'files_of '.'<pre>'.print_r($files_of,1).'</pre>'.'<br>';
	*/		
	//		die();
			
			$prev_det_file = $files_of[0];
			array_shift($files_of);
			
//			print_r($files_of);
			$arFieldsAdd = array(
				"ACTIVE" => "Y",	
				"IBLOCK_ID" => 16,
				"IBLOCK_SECTION_ID" => ($section_id_of?$section_id_of:64),
				"DATE_ACTIVE_FROM" => ConvertTimeStamp(time(), 'FULL'),
//				"PREVIEW_PICTURE"=>$prev_det_file,
//				"DETAIL_PICTURE"=>$prev_det_file,

				"NAME" => $name_of,
				"CODE" => translit($name_of) . '_' .$postavschik.'_'.time().'_'.$price_1_of.'_'.rand(1,100),
				"PROPERTY_VALUES" => array(
					"STARTSHOP_QUANTITY" => ($good_count_of?$good_count_of:100),
					"STARTSHOP_QUANTITY_RATIO" => ($quantity_ratio_of?$quantity_ratio_of:1),
					"POSTAVSCHIK" => $postavschik,
					"STARTSHOP_PRICE_1" => $price_1_of,
					"STARTSHOP_PROCENT_2" => $price_2_of,
					"STARTSHOP_PROCENT_3" => $price_3_of,
					"STARTSHOP_CURRENCY_1" => 26,
					"SYSTEM_ARTICLE" => $good_sku_of,
					"WEIGHT" => $good_weight_of,
					"SIZE" => $size_of_arr,
					"COLOR" => $color_of_arr,
	/*					
					"CHARACT_IN_ONE" => $charact_of_arr,
					"DOP_FIELD_BRAND" => $charact_of_arr_id['DOP_FIELD_BRAND'],
					"SEASON" => $charact_of_arr_id['SEASON'],
					"DOP_FIELD_STELKA" => $charact_of_arr_id['DOP_FIELD_STELKA'],
					"DOP_FIELD_MATERIAL" => $charact_of_arr_id['DOP_FIELD_MATERIAL'],
					"DOP_FIELD_VN_MATERIAL" => $charact_of_arr_id['DOP_FIELD_VN_MATERIAL'],
					"DOP_KOL" => $charact_of_arr_id['DOP_KOL'],
					"DOP_VID_SPORT" => $charact_of_arr_id['DOP_VID_SPORT'],
					"DOP_COUNTRY" => $charact_of_arr_id['DOP_COUNTRY'],
	*/				
				)
			);

			if(trim(strip_tags($small_description_of))==''){
				$arFieldsAdd["PREVIEW_TEXT"] = ' ';//'<p>'.$small_description_of.'</p>';
			}else{
				$arFieldsAdd["PREVIEW_TEXT"] = $small_description_of;//nl2br($small_description_of);
			}
			
			if(trim(strip_tags($description_of))==''){
				$arFieldsAdd["DETAIL_TEXT"] = ' ';//'<p>'.$description_of.'</p>';
			}else{
				$arFieldsAdd["DETAIL_TEXT"] = $description_of;//nl2br($description_of);
			}			
			
			if($prev_det_file){
				$arFieldsAdd['PREVIEW_PICTURE'] = $prev_det_file;
			}
			
			if($prev_det_file){
				$arFieldsAdd['DETAIL_PICTURE'] = $prev_det_file;
			}
			
			if($charact_of_arr){
				$arFieldsAdd['CHARACT_IN_ONE'] = $charact_of_arr;
			}			
			
//			print_r($charact_of_arr_id);
			
			foreach($charact_of_arr_id as $k=>$elem){
				$arFieldsAdd["PROPERTY_VALUES"][$k] = $elem;
			}
			
			if(count($files_of)){
				$arFieldsAdd["PROPERTY_VALUES"]["SYSTEM_IMAGES"] = $files_of;
				$arFieldsAdd["PROPERTY_VALUES"]["NAMES_IMAGES"] = $names_files_arr;
			}else{
				$arFieldsAdd["PROPERTY_VALUES"]["NAMES_IMAGES"] = array();
				$arFieldsAdd["PROPERTY_VALUES"]["SYSTEM_IMAGES"] = array();
			}
			
			
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test_good.txt',print_r($arFieldsAdd,1),FILE_APPEND);
			
/*			
			echo '<pre>'.print_r($arFieldsAdd,1).'</pre>';
			die();
*/			
			
//			echo '<pre>'.print_R($arFieldsAdd,1).'</pre>';

			$el = new CIBlockElement();
			
			if((int)$product_id){
				
				$product_test = $el->getByID($product_id);
				if(!$product_test->getNext()){
					$product_id = 0;
				}
				
			}
			
//			echo $product_id;die();
			
			if((int)$product_id){
				
				$del_SYSTEM_IMAGES = array();

				$el->Update($product_id, array(
					"PREVIEW_PICTURE" => array('del' => 'Y'),
					"DETAIL_PICTURE" => array('del' => 'Y'),
				), false, false);

				
				$res_prop = $el->GetProperty(16,$product_id,array("sort" => "asc"),array("CODE" => "SYSTEM_IMAGES"));
				while ($ob = $res_prop->GetNext()){
					if($ob['VALUE']){
						$del_SYSTEM_IMAGES[$ob['ID']] = array('VALUE'=>array('del'=>'Y'));
					}
				}
				
				if(count($del_SYSTEM_IMAGES)){
					$el->SetPropertyValuesEx($product_id,16, $del_SYSTEM_IMAGES);
				}

//				echo '<pre>'.print_r($del_SYSTEM_IMAGES,1).'</pre>';die();
				
				$el_ID = $el->Update($product_id,$arFieldsAdd);

			}else{
//				echo '<pre>'.print_r($arFieldsAdd,1).'</pre>';
				$el_ID = $el->Add($arFieldsAdd);
//				echo '||'.$el_ID.'||';

			}
			
			if(!$el_ID){

				file_put_contents($_SERVER['DOCUMENT_ROOT'].'/switch/test_times/add-good-error.txt', date('Y-m-d H:i:s').")-----".print_r($arFieldsAdd,1)."\n",FILE_APPEND);
				
				return 
					array(
						'error'=>1,
						'message'=>'Товар не добавлен/обновлен!'."\n".$el->LAST_ERROR,
					);
			}
			
			$arSelect = Array("ID");
			$arFilter = Array("IBLOCK_ID"=>32,"PROPERTY_POSTAVSCHIK"=>$postavschik);
			$res = $el->GetList(Array(), $arFilter, false, Array("nTopCount"=>100000), $arSelect);
			$ob = $res->GetNextElement();
			$fields_ = $ob->GetFields();
			
			$el->SetPropertyValuesEx($fields_['ID'], 32, array('ISNEWGOODS'=>'1'));

			if(!$type_){
				
				return 
					array(
						'error'=>0,
						'message'=>'Успешно отправлен'
					);
					
			}

			if($type_==1){
				header('Location: /personal/profile/goods/');
			}
				
		}else{
			
			if(!$type_){
				
				return 
					array(
						'error'=>1,
						'message'=>'Ошибка отправки. Не все поля заполнены!'
					);
					
			}else{
				
				if($type_==1){
					echo "Error: Ошибка отправки.\nНе все поля заполнены!";
				}
				
			}
			
			if($type_==1){
				header('Location: /personal/profile/goods/');
			}
			
		}
	
//		echo '---------------------'.'<br>';
	
	}
	
	//	echo 'aaa';
?>