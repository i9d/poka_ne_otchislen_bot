<?php

require('../vendor/autoload.php');
require ('nokogiri.php');

$app = new Silex\Application();
$app['debug'] = true;
// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));
// Our web handlers
$app->get('/', function() use($app) {
 return 'ну типа работает....!!!!какбэээ!!!!!!!!!!!!!!!!!!!!!!';
});

const COLOR_NEGATIVE = 'negative';
const COLOR_POSITIVE = 'positive';
const COLOR_DEFAULT = 'default';
const COLOR_PRIMARY = 'primary';
const CMD_ID = 'ID';
const CMD_SCHEDULE = 'SCHEDULE';
const CMD_ANEKDOT = 'ANEKDOT';
const CMD_WEATHER = 'WEATHER';

function getBtn($label, $color, $payload = '') {
    return [
        'action' => [
            'type' => 'text',
            "payload" => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'label' => $label
        ],
        'color' => $color
    ];
}

function anekdot() {
	$html = file_get_contents('https://www.anekdot.ru/random/anekdot/');
	preg_match('~<div class="text">(.*?)</div>~', $html, $anekdotik);
	return $anekdotik[1];
}

function weather() {
	$html = file_get_contents('https://yandex.ru/pogoda/omsk');
	preg_match('~<span class="temp__value">(.*?)</span>~', $html, $gradus);
//	preg_match('~<div class="link__condition day-anchor i-bem" data-bem='{"day-anchor":{"anchor":12}}'>(.*?)</div>~', $html, $description);
	$weather = "Погода в Омске: {$gradus[1]}°С";// {$description[1]}";
	return $weather;
}

function schedule($group, $day)
{
	$string = '';
	$day = 'ВТ';
	$group = 3;
	
	$day_arr = array('ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ');
	$indexOfDayArr = array_search($day, $day_arr);  
	$html = file_get_contents('http://fkn.univer.omsk.su/academics/Schedule/schedule3_1.htm');
	$saw = new nokogiri($html);
	$table = $saw->get('table'); 
	$tr = $table->get('tr');
	
	$str = 0;		//строка
	$t = 1; 	//время
	
//	var_dump( $tr->toArray()[$str]['td'][$group]["#text"] );
//	echo count( $tr->toArray()[$str]['td'][$group]["#text"] );
	/*__________________________________________*/ // Ищем нужный день (индекс строки, где нужный день)
	while (cell($tr, $str, 0) !== $day)
	{$str++;}
	
	/*__________________________________________*/ //Парсим группу, выводим группу и день
	$egroup = cell($tr, 0, $group);
	$string.= $egroup;
	$string.= "\n";
	$string.= $day;
	$string.= "\n";
	/*__________________________________________*/

	$time = cell($tr, $str, $t);	//парсим время
	$span = colspan($tr, $str, $group);
	$lesson = cell($tr, $str, $group-$span);	//парсим пару
	if($lesson !== Null)	//если  пара есть, выводим время и пару
	{
		$string .= $time;
		$string .= "\n";
		$string .= $lesson;
		$string .= "\n";
	}

	$group--;	//сдвиг влево из-за того, что был день недели
	$t--;
	$str++;		//следующая строка
	
	while (cell($tr, $str, 0) !== $day_arr[$indexOfDayArr+1])
	{
		$time = cell($tr, $str, $t);
		$span = colspan($tr, $str, $group);
		$lesson = cell($tr, $str, $group-$span);
		if($lesson !== Null)
		{
		$string .= $time;
		$string .= "\n";
		$string .= $lesson;
		$string .= "\n";
		}
		$str++;
	} 
	
	return $string;
	
}

function sendmessage($user_id, $message, $keyboard) {
	$request_params = array(
		'user_id' => $user_id, 
		'message' => $message, 
		'access_token' => getenv('VK_TOKEN'),
		'v' => '5.69',
		'keyboard' => json_encode($keyboard, JSON_UNESCAPED_UNICODE)
	);
	$get_params = http_build_query($request_params); 
	file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 
}


$app->post('/', function() use($app) {
	$data = json_decode(file_get_contents('php://input'));
	if(!$data)
	if($data->secret !== getenv('VK_CONFIRMATION_CODE') && $data->type !== 'confirmation')
	return 'invalid_token';
	
	switch($data->type)
	{
		case 'confirmation':
			return getenv('VK_CONFIRMATION_CODE');
			break;
		
		case 'group_leave':
			$token = getenv('VK_TOKEN');
			$user_id = $data->object->user_id; 
			$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.69")); 
			$user_name = $user_info->response[0]->first_name; 
			sendmessage($user_id, "{$user_name}, мы будем ждать твоего возращения!", 0);
			return('OK');
			break;
			
		case 'message_new':
			$token = getenv('VK_TOKEN');
			$user_id = $data->object->user_id; 
			$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.69")); 
			$user_name = $user_info->response[0]->first_name; 
			$received_message = $data->object->body;
			$payload = $data->object->payload;
			if ($payload) { $payload = json_decode($payload, true); }
			$kbd = [
				 'one_time' => false,
				 'buttons' => [
					 [getBtn("Покажи мой ID", COLOR_PRIMARY, CMD_ID), getBtn("&#128284;Расписание(скоро)", COLOR_NEGATIVE, CMD_SCHEDULE)],
					 [getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT), getBtn("&#127783; Погода", COLOR_POSITIVE, CMD_WEATHER)],
				 ]
			 ];
			 if ($payload === CMD_ID) {$send_message = "Ваш id {$user_id}";}
			 elseif ($payload === CMD_SCHEDULE) {
				/* $kbd = [
				 'one_time' => false,
				 'buttons' => [
						 [
						 getBtn("1курс", COLOR_PRIMARY, CMD_NEXT),
				 		 getBtn("2курс", COLOR_PRIMARY, CMD_NEXT),
						 getBtn("3курс", COLOR_PRIMARY, CMD_NEXT),
						 getBtn("4курс", COLOR_PRIMARY, CMD_NEXT),
						 getBtn("Главное меню", COLOR_DEFAULT, CMD_NEXT),
					 	]
				 	      ]
				 ];*/
				// $send_message = 'Написано же, СКОРО. Чего ты жмешь сюда? Теперь бот сломан';
				
				$send_message = schedule(0,0);
				
				
				
			 }
			elseif ($payload === CMD_ANEKDOT) {$send_message = anekdot();}
			elseif ($payload === CMD_WEATHER) {$send_message = weather();}

			
			elseif($user_id == '272968093')
			{$send_message = 'вышел отсудава розбiйник';}
			elseif($received_message == 'Начать')
			{$send_message = "Привет, {$user_name}! Я супер крутой бот 2999! Внизу появились кнопочки, выбери нужную и нажми на нее.";}
			else
			{$send_message = "{$user_name},я не очень умный бот, поэтому не понимаю, что ты пишешь. Используй кнопки";}
			
			sendmessage($user_id, $send_message, $kbd);
			return('OK');
			break;
		default:
			return('OK');
			break;
	}
});

$app->run();
