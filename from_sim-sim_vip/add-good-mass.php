<?

	define("NO_KEEP_STATISTIC", true); //Не учитываем статистику
	define("NOT_CHECK_PERMISSIONS", true); //Не учитываем права доступа
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	require($_SERVER["DOCUMENT_ROOT"]."/mail-params.php");
	
	global $USER;
	$postavschik = $USER->getID();
	CModule::IncludeModule('iblock');

	require($_SERVER["DOCUMENT_ROOT"]."/add-good-function.php");

	$path_for_user_dir = $_SERVER['DOCUMENT_ROOT'].'/users/user_'.$postavschik.'/';

	$can_upload = false;

	function try_get_picture($postavschik,$image_name){
		$first_part = $_SERVER['DOCUMENT_ROOT'].'/users/user_';
		$next_part = '/images/';
		$file_name = trim($image_name);

		$picture_is = '';

		if(file_exists($first_part.$postavschik.$next_part.$file_name)){
			$picture_is = $first_part.$postavschik.$next_part.$file_name;
			return $picture_is;
		}else{
			if(file_exists($first_part.$postavschik.$next_part.$file_name.'.jpg')){
				$picture_is = $first_part.$postavschik.$next_part.$file_name.'.jpg';
				return $picture_is;
			}
			if(file_exists($first_part.$postavschik.$next_part.$file_name.'.png')){
				$picture_is = $first_part.$postavschik.$next_part.$file_name.'.png';
				return $picture_is;
			}
			if(file_exists($first_part.$postavschik.$next_part.$file_name.'.gif')){
				$picture_is = $first_part.$postavschik.$next_part.$file_name.'.gif';
				return $picture_is;
			}			
		}
		
		return $picture_is;
	}

	if(isset($_FILES) && $_FILES){
		
		if($_FILES['GOODS_FILE']['name'] && $_FILES['GOODS_FILE']['size']){

			//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt','4 || '.$can_send.' || '.$emails_send.'|| yes'.print_r($_FILES['file'],1)."\n",FILE_APPEND);		
			
			$file_to_check = array(
			
				'name' => $_FILES['GOODS_FILE']['name'],
				'type' => $_FILES['GOODS_FILE']['type'],
				'tmp_name' => $_FILES['GOODS_FILE']['tmp_name'],
				'error' => $_FILES['GOODS_FILE']['error'],
				'size' => $_FILES['GOODS_FILE']['size']
				
			);
/*				
			echo '<pre>'.print_r($elem,1).'</pre>';
			echo '<br>------------<br>';
			
			echo '<pre>'.print_r($file_to_check,1).'</pre>';
			
			echo '<br>------------<br>';
			echo '<pre>'.print_r($_FILES,1).'</pre>';
*/
			if(check_uploaded_files($file_to_check,200000000,array("zip"))){
				
				if(!file_exists($path_for_user_dir)){
					
					mkdir($path_for_user_dir);
					mkdir($path_for_user_dir.'images/');
					
				}
				
				$name_link = $path_for_user_dir.'images/expand.zip';
				move_uploaded_file($_FILES['GOODS_FILE']['tmp_name'], $name_link);
				
				$zip = new ZipArchive;
				$res = $zip->open($name_link);
				
				if ($res === true) {
					
					$zip->extractTo($path_for_user_dir.'images/');
					$zip->close();
					
					rename($path_for_user_dir.'images/import.csv', $path_for_user_dir.'import.csv');
					$can_upload = true;
					
					unlink($name_link);
					
					array_map("unlink", glob($path_for_user_dir.'images/*.php'));
					array_map("unlink", glob($path_for_user_dir.'images/*.rar'));
					array_map("unlink", glob($path_for_user_dir.'images/*.exe'));
					array_map("unlink", glob($path_for_user_dir.'images/*.com'));
					array_map("unlink", glob($path_for_user_dir.'images/*.js'));
					array_map("unlink", glob($path_for_user_dir.'images/*.sh'));
					
				}else{
					
					$can_upload = false;

				}
				
			}

			if($can_upload){
			
				$file_csv = $path_for_user_dir.'import.csv';
//				echo $file_csv;die();

				if(file_exists($file_csv)){
				
					$fp = fopen($file_csv, 'r');
					
					$headers_cols = array();
					$tovars = array();
					$count_lines = 0;
					
					$razdelitel = ';';
					
					while (!feof($fp)){
						
						$count_lines += 1;
						$line_ = fgets($fp);
						$line_ = trim($line_);
//						$line_ = mb_convert_encoding($line_,'UTF-8');
						
						if($line_){
							$exloded_line_arr = explode($razdelitel,trim($line_,$razdelitel));
							
							if($count_lines==1){
								
								$headers_cols = $exloded_line_arr;
								
							}else if($count_lines==2){
								
							}else{

								$tovars[] = $exloded_line_arr;
								
							}
							
						}
						
					}
					
					$all_tovars = array();
					
					foreach($tovars as $k=>$elem){
						
						foreach($elem as $k_in=>$elem_in){
							
							switch($headers_cols[$k_in]){
								case 'ID':
									//$_REQUEST_GOODS[$k]['product-id'] = $elem_in;
									$_REQUEST_GOODS[$k]['product-id'] = 0;
								break;					
								case 'NAME':
									$_REQUEST_GOODS[$k]['GOOD_NAME'] = $elem_in;
								break;					
								case 'SECTION_ID':
									$_REQUEST_GOODS[$k]['GOOD_CAT'] = $elem_in;
								break;					
								case 'PREVIEW_PICTURE':
								
									$item_temp = (int)trim($elem_in);
									if(is_int($item_temp) && $item_temp){
										$_REQUEST_GOODS[$k]['GOOD_PICTURE_ID'][] = trim($elem_in);
									}else{
										$picture_is = try_get_picture($postavschik,$elem_in);
										if($picture_is){
											$_REQUEST_GOODS[$k]['GOOD_PICTURE_TEXT'][] = $picture_is;
										}
									}

								break;
								case 'PREVIEW_TEXT':
									$_REQUEST_GOODS[$k]['GOOD_DESCRIPTION'] = $elem_in;
								break;
								case 'PRICE_1':
									$_REQUEST_GOODS[$k]['GOOD_PRICE'] = $elem_in;
								break;
								case 'QUANTITY_RATIO':
									$_REQUEST_GOODS[$k]['GOOD_QUANTITY_RATIO'] = $elem_in;
								break;
								case 'PROCENT_SALE_100':
									$_REQUEST_GOODS[$k]['GOOD_SALE_100'] = $elem_in;
								break;
								case 'PROCENT_SALE_1000':
									$_REQUEST_GOODS[$k]['GOOD_SALE_1000'] = $elem_in;
								break;
								case 'SYSTEM_ARTICLE':
									$_REQUEST_GOODS[$k]['GOOD_SKU'] = $elem_in;
								break;
								case 'WEIGHT':
									$_REQUEST_GOODS[$k]['GOOD_WEIGHT'] = $elem_in;
								break;
								case 'SIZE':
									$arr_elems = explode(',',$elem_in);
									foreach($arr_elems as $item){
										$_REQUEST_GOODS[$k]['GOOD_SIZE'][] = trim($item);
									}
								break;
								case 'COLOR':
									$arr_elems = explode(',',$elem_in);
									foreach($arr_elems as $item){
										$_REQUEST_GOODS[$k]['GOOD_COLOR'][] = trim($item);
									}
								break;		
								case 'SYSTEM_IMAGES':
									$arr_elems = explode(',',$elem_in);
									foreach($arr_elems as $item){
										$item_temp = (int)trim($item);
										if(is_int($item_temp) && $item_temp){
											$_REQUEST_GOODS[$k]['GOOD_PICTURE_ID'][] = trim($item);
										}else{
											$picture_is = try_get_picture($postavschik,$item);

						
											
											if($picture_is){
												$_REQUEST_GOODS[$k]['GOOD_PICTURE_TEXT'][] = $picture_is;
											}
										}
									}
								break;
							}
							
							if(stripos($headers_cols[$k_in],'DOP')!==false){
								$_REQUEST_GOODS[$k]["GOOD_ADD_CHARACT_NAME"][$k_in] = $headers_cols[$k_in];
								$_REQUEST_GOODS[$k]["GOOD_ADD_CHARACT_NAME_ID"][$k_in] = $headers_cols[$k_in];
								$_REQUEST_GOODS[$k]["GOOD_ADD_CHARACT_VALUE"][$k_in] = $elem_in;
							}
							
			//				$all_tovars[$k][$headers_cols[$k_in]] = $elem[$k_in];
						}

			
						echo '<pre>';
				//			echo print_r($headers_cols,1);
							echo print_r($_REQUEST_GOODS[$k],1);
						echo '</pre>';
//die();
						add_good($postavschik,$_REQUEST_GOODS[$k],array(), 0);
						
					}
					
					$_SESSION['file_uploaded'] = true;
					
				
				}else{
					
//					echo $path_for_user_dir.'import.csv'.'||'.'Нет файла!';
					
					$_SESSION['file_uploaded'] = false;
					$_SESSION['file_uploaded_text'] = 'Архив не содержит import.csv';
					
				}
				
			}else{
				
				$_SESSION['file_uploaded'] = false;
				$_SESSION['file_uploaded_text'] = 'Файл не .zip архив';
				
			}
		
		}else{
			
			$_SESSION['file_uploaded'] = false;
			$_SESSION['file_uploaded_text'] = 'Ошибка имени файла';
			
		}
		
	}else{
		
		$_SESSION['file_uploaded'] = false;
		$_SESSION['file_uploaded_text'] = 'Ошибка загрузки файла';
		
	}
//	die();	
	header('Location: /personal/profile/add-mass-goods/');

//	add_good($_REQUEST,array(), 1);
	
?>