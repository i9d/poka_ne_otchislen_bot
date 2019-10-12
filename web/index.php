<?php
require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;
// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));
// Our web handlers
$app->get('/', function() use($app) {
 return 'ну типа работает....!!!!какбэээ!';
});

const COLOR_NEGATIVE = 'negative';
const COLOR_POSITIVE = 'positive';
const COLOR_DEFAULT = 'default';
const COLOR_PRIMARY = 'primary';
const CMD_ID = 'ID';
const CMD_SCHEDULE = 'SCHEDULE';
const CMD_ANEKDOT = 'ANEKDOT';

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
					 [getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT)],
				 ]
			 ];
			 if ($payload === CMD_ID) {$send_message = "Ваш id {$user_id}";}
			 elseif ($payload === CMD_SCHEDULE) {
				 $send_message = 'Написано же, СКОРО. Чего ты жмешь сюда? Теперь бот сломан';
				 $kbd = [
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
				 ];
			 }
			elseif ($payload === CMD_ANEKDOT) {$send_message = anekdot();}
			
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
