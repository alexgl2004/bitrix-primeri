<?php
	header('Content-Type: text/html; charset=utf-8');
	$send = mail("alexgl2004@gmail.com", "ТЕСТ", "ТЕСТ",
					"MIME-Version: 1.0\r\n"
					."Content-type: text/html; utf-8\r\n"
					."From: info@sim-sim.vip\r\n"
					."Reply-To: info@sim-sim.vip\r\n"
//					."From: sim-sim.vip@yandex.ru\r\n"
//					."Reply-To: sim-sim.vip@yandex.ru\r\n"
					."X-Mailer: PHP/" . phpversion());
	if($send){
		echo 'Отправлено';
	}else{
		echo 'ОБЛОМ!<br>|'.$send;
	}
?>