<?

	$spisok_mail = 'sim-sim.vip@yandex.ru';
	
	function stripall($name_,$elem_){
		
		$elem_ = (is_array($elem_)?implode(', ',$elem_):$elem_);
//		$elem_ = mb_convert_encoding($elem_,'windows-1251','UTF-8');
		//$elem_ = mb_convert_encoding($elem_,'UTF-8','windows-1251');
		return (trim($elem_)!=''?(trim($name_)!=''?$name_.': ':'').addslashes(strip_tags($elem_)).(trim($name_)!=''?"\n":''):'');
		
	}
	
	function convert_utf8($elem_){
		
		$elem_ = mb_convert_encoding($elem_,'windows-1251', 'UTF-8');
		//$elem_ = mb_convert_encoding($elem_,'UTF-8','windows-1251');
		return trim($elem_);
		
	}

?>