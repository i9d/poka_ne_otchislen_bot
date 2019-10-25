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




function sendmessage_kbd($user_id, $message, $keyboard) {
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

function sendmessage($user_id, $message) {
	$request_params = array(
		'user_id' => $user_id, 
		'message' => $message, 
		'access_token' => getenv('VK_TOKEN'),
		'v' => '5.69',
		//'keyboard' => json_encode($keyboard, JSON_UNESCAPED_UNICODE)
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
			
			 
				switch($payload)
				{		

					case CMD_ANEKDOT:
						sendmessage($user_id, anekdot());
						break;
					
					case CMD_WEATHER:
						sendmessage($user_id, weather());
						break;
						
					case CMD_MAIN:
						$kbd = [
							'one_time' => false,
							'buttons' => [
								[getBtn("Расписание", COLOR_PRIMARY, CMD_SCHEDULE)],
								[getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT), getBtn("&#127783; Погода", COLOR_POSITIVE, CMD_WEATHER)],
										]
							];
						sendmessage_kbd($user_id, 'Вы в главном меню', $kbd);
						break;
						
					case CMD_SCHEDULE:
						$kbd = [
						'one_time' => true,
						'buttons' => [
							[getBtn("СБС-701", COLOR_PRIMARY, CMD_SBS701), getBtn("СББ-701", COLOR_PRIMARY, CMD_SBB701), getBtn("СМБ-701", COLOR_PRIMARY, CMD_SMB701)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите группу:', $kbd);
						break;
						
					/*__________________НИЖЕ МЯСО__________________*/
					case CMD_SBS701:
						$kbd = [
							'one_time' => false,
							'buttons' => [
							[getBtn("ПН", COLOR_PRIMARY, SBS_PN), getBtn("ВТ", COLOR_PRIMARY, SBS_VT), getBtn("СР", COLOR_PRIMARY, SBS_SR)],
							[getBtn("ЧТ", COLOR_PRIMARY, SBS_CT), getBtn("ПТ", COLOR_PRIMARY, SBS_PT), getBtn("СБ", COLOR_PRIMARY, SBS_SB)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите день недели:', $kbd);
						break;
						
						
					case CMD_SBB701:
						$kbd = [
							'one_time' => false,
							'buttons' => [
							[getBtn("ПН", COLOR_PRIMARY, SBB_PN), getBtn("ВТ", COLOR_PRIMARY, SBB_VT), getBtn("СР", COLOR_PRIMARY, SBB_SR)],
							[getBtn("ЧТ", COLOR_PRIMARY, SBB_CT), getBtn("ПТ", COLOR_PRIMARY, SBB_PT), getBtn("СБ", COLOR_PRIMARY, SBB_SB)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите день недели:', $kbd);
						break;
						
						
					case CMD_SMB701:
						$kbd = [
							'one_time' => false,
							'buttons' => [
							[getBtn("ПН", COLOR_PRIMARY, SMB_PN), getBtn("ВТ", COLOR_PRIMARY, SMB_VT), getBtn("СР", COLOR_PRIMARY, SMB_SR)],
							[getBtn("ЧТ", COLOR_PRIMARY, SMB_CT), getBtn("ПТ", COLOR_PRIMARY, SMB_PT), getBtn("СБ", COLOR_PRIMARY, SMB_SB)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите день недели:', $kbd);
						break;	
						
					
					
					/*__________________СБС__________________*/
					case SBS_PN:
						sendmessage($user_id, schedule(2,'ПН'));
						break;
						
					case SBS_VT:
						sendmessage($user_id, schedule(2,'ВТ'));
						break;	
						
					case SBS_SR:
						sendmessage($user_id, schedule(2,'СР'));
						break;
						
					case SBS_CT:
						sendmessage($user_id, schedule(2,'ЧТ'));
						break;	
						
					case SBS_PT:
						sendmessage($user_id, schedule(2,'ПТ'));
						break;
						
					case SBS_SB:
						sendmessage($user_id, schedule(2,'СБ'));
						break;	
						
						
						
					/*__________________СББ__________________*/
					case SBB_PN:
						sendmessage($user_id, schedule(3,'ПН'));
						break;

					case SBB_VT:
						sendmessage($user_id, schedule(3,'ВТ'));
						break;

					case SBB_SR:
						sendmessage($user_id, schedule(3,'СР'));
						break;

					case SBB_CT:
						sendmessage($user_id, schedule(3,'ЧТ'));
						break;

					case SBB_PT:
						sendmessage($user_id, schedule(3,'ПТ'));
						break;

					case SBB_SB:
						sendmessage($user_id, schedule(3,'СБ'));
						break;						
					
			
						
					/*__________________СМБ__________________*/
					case SMB_PN:
						sendmessage($user_id, schedule(4,'ПН'));
						break;
						
					case SMB_VT:
						sendmessage($user_id, schedule(4,'ВТ'));
						break;
						
					case SMB_SR:
						sendmessage($user_id, schedule(4,'СР'));
						break;
					case SMB_CT:
						sendmessage($user_id, schedule(4,'ЧТ'));
						break;

					case SMB_PT:
						sendmessage($user_id, schedule(4,'ПТ'));
						break;

					case SMB_SB:
						sendmessage($user_id, schedule(4,'СБ'));
						break;						
						
						
					default:
						$kbd = [
							'one_time' => false,
							'buttons' => [
								[getBtn("Расписание", COLOR_PRIMARY, CMD_SCHEDULE)],
								[getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT), getBtn("&#127783; Погода", COLOR_POSITIVE, CMD_WEATHER)],
										]
							];
						sendmessage_kbd($user_id, "{$user_name},я не очень умный бот, поэтому не понимаю, что ты пишешь. Используй кнопки", $kbd);
						break;
				}
			if($received_message == 'Начать')
			{
				$kbd = [
							'one_time' => false,
							'buttons' => [
								[getBtn("Расписание", COLOR_PRIMARY, CMD_SCHEDULE)],
								[getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT), getBtn("&#127783; Погода", COLOR_POSITIVE, CMD_WEATHER)],
										]
							];
						sendmessage_kbd($user_id, "Привет, {$user_name}! Я супер крутой бот 2999! Внизу появились кнопочки, выбери нужную и нажми на нее.", $kbd);
			}
			
			
			
			//sendmessage($user_id, $send_message, $kbd);
			return('OK');
			break;
		default:
			return('OK');
			break;
	}
});

$app->run();
