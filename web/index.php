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
 return getenv('VK_CONFIRMATION_CODE');
});

$app->post('/', function() use($app) {
	$data = json_decode(file_get_contents('php://input'));
	if(!$data) //пустой
	if($data->secret !== getenv('VK_CONFIRMATION_CODE') && $data->type !== 'confirmation')
		return 'sovsemneok'; //если токен не совпадает
	
	switch($data->type)
	{
		case 'confirmation':
			return getenv('VK_CONFIRMATION_CODE');//VK_CONFIGURATION_CODE:
			break;
		
		/*case 'message_new':
		
			$user_id = $data->object->user_id; 
			$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.0")); 
			$user_name = $user_info->response[0]->first_name; 
			
	
			$request_params = array(
				'user_id' => $user_id, 
				'message' => 'Привет, {$user_name}! Я очень умный бот(нет)',
				'acces_token' => $token,
				'v' => '5,69'
			);
			$get_params = http_build_query($request_params); 
			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 
			echo('ok');
		//////
			break;*/
			
			/Если это уведомление о новом сообщении... 
case 'message_new': 
//...получаем id его автора 
$user_id = $data->object->user_id; 
//затем с помощью users.get получаем данные об авторе 
$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.0")); 

//и извлекаем из ответа его имя 
$user_name = $user_info->response[0]->first_name; 

//С помощью messages.send отправляем ответное сообщение 
$request_params = array( 
'message' => "Hello, {$user_name}!", 
'user_id' => $user_id, 
'access_token' => $token, 
'v' => '5.0' 
); 

$get_params = http_build_query($request_params); 

file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 

//Возвращаем "ok" серверу Callback API 

echo('ok'); 

break; 
	}
	
	
});

$app->run();
