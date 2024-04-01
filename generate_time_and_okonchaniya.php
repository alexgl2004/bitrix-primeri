<?

    /**
     * Функция для получения сообщения о том, что элемент будет доступен через N часов
     * 
     */

	$MESS = array();
	$MESS ['SUP_MARK_ENABLED_HOURS'] = "Будет доступно через #H час#E";
	$MESS ['SUP_MARK_ENABLED_HOURS_END'] = "|a|ов";

	function GetMessage($id_mess){
        global $MESS;
		return $MESS [$id_mess];
	}

//***Выше имитация GetMessage из битрикса */

	$now_time = time();//Текущее время

    $from_date = '10.02.2024 12.02.2024';//Время создания элемента

	$originalTime = new DateTimeImmutable(date('Y-m-d H:i:s', $now_time));
	$targedTime = new DateTimeImmutable(date('Y-m-d H:i:s', strtotime($from_date) ));
	$interval = $originalTime->diff($targedTime);//Время между датами

	$Hour_days = (int)($interval->format("%a")*24);
	$Hours = (int)$interval->format("%H") + $Hour_days; //Количество часов, между датами $now_time и $from_date
	
	$Hours = 65;//Тестовая вставка, для проверки работоспособности и правильного окончания слова часов

	$Hours_to = 72 - $Hours;
	if($Hours_to>=0){
		$Hours_last_elem = (int)substr( (string)$Hours_to,-1 );
		$end_of_hours_arr = explode('|',GetMessage("SUP_MARK_ENABLED_HOURS_END"));
		$Hours_to_text = ($Hours_last_elem == 1?$end_of_hours_arr[0]:($Hours_last_elem < 5?$end_of_hours_arr[1]:$end_of_hours_arr[2]));
		$Hours_to_text = str_replace(['#H','#E'],[$Hours_to,$Hours_to_text], GetMessage("SUP_MARK_ENABLED_HOURS"));
	}

    echo $Hours_to_text;

?>