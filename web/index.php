<?php

require('../vendor/autoload.php');
require ('nokogiri.php');
require ('schedule.php');

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
const CMD_SECOND = '2курс';
const CMD_SBS701 = 'СБС-701';
const CMD_SBB701 = 'СББ-701';
const CMD_SMB701 = 'СМБ-701';
$group = '2';

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

function week()
{
	$kbd = [
				 'one_time' => false,
				 'buttons' => [
				 [
						getBtn("ПН", COLOR_PRIMARY, CMD_PN),
				 		getBtn("ВТ", COLOR_PRIMARY, CMD_VT),
						getBtn("СР", COLOR_PRIMARY, CMD_SR),
						getBtn("ЧТ", COLOR_PRIMARY, CMD_CT),
						getBtn("ПТ", COLOR_PRIMARY, CMD_PT),
						getBtn("СБ", COLOR_PRIMARY, CMD_SB),
						getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN),
						 ]
				 	      ]
				 ];
	
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
				 $kbd = [
				 'one_time' => false,
				 'buttons' => [
						 [
						 //getBtn("1курс", COLOR_PRIMARY, CMD_NEXT),
				 		// getBtn("2курс", COLOR_PRIMARY, CMD_NEXT),
						 getBtn("3курс", COLOR_PRIMARY, CMD_SECOND),
						// getBtn("4курс", COLOR_PRIMARY, CMD_NEXT),
						 getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN),
					 	]
				 	      ]
				 ];
				 $send_message = 'Выберите курс:';
				
				//$send_message = schedule(0,0);
			 }
			elseif ($payload === CMD_ANEKDOT) {$send_message = anekdot();}
			elseif ($payload === CMD_WEATHER) {$send_message = weather();}
			elseif ($payload === CMD_SECOND) {
				 $kbd = [
				 'one_time' => false,
				 'buttons' => [
						 [
						 getBtn("СБС-701", COLOR_PRIMARY, CMD_SBS701),
						 getBtn("СББ-701", COLOR_PRIMARY, CMD_SBB701),
						 getBtn("СМБ-701", COLOR_PRIMARY, CMD_SMB701),
						// getBtn("4курс", COLOR_PRIMARY, CMD_NEXT),
						 getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN),
					 	]
				 	      ]
				 ];
				 $send_message = 'Выберите группу:';
			}
			elseif ($payload === CMD_SBS701) {$group=2; week();}//{$send_message = schedule(2,0);}
			elseif ($payload === CMD_SBB701) {//$group=3;	
			
			
			
							 				 $send_message = 'Гагага';
			
			/*
			$kbd = [
				 'one_time' => false,
				 'buttons' => [
				 [
						getBtn("ПН", COLOR_PRIMARY, CMD_PN),
				 		getBtn("ВТ", COLOR_PRIMARY, CMD_VT),
						getBtn("СР", COLOR_PRIMARY, CMD_SR),
						getBtn("ЧТ", COLOR_PRIMARY, CMD_CT),
						getBtn("ПТ", COLOR_PRIMARY, CMD_PT),
						getBtn("СБ", COLOR_PRIMARY, CMD_SB),
						getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN),
						 ]
				 	      ]
				 ];
				 
				 				 $send_message = 'Группа СББ';
				 */
			
			
			}//{$send_message = schedule(3,0);}
			elseif ($payload === CMD_SMB701) {$group=4;	week();}//{$send_message = schedule(4,0);}
			elseif ($payload === CMD_MAIN) {$send_message = 'Вы в главном меню';}
			elseif($payload === CMD_PN) {$send_message = schedule($group,0);}
			elseif($payload === CMD_VT) {$send_message = schedule($group,1);}
			elseif($payload === CMD_SR) {$send_message = schedule($group,2);}
			elseif($payload === CMD_CT) {$send_message = schedule($group,3);}
			elseif($payload === CMD_PT) {$send_message = schedule($group,4);}
			elseif($payload === CMD_SB) {$send_message = schedule($group,5);}
			
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
