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
 return 'ну типа работает....';
});



$app->post('/', function() use($app) {
	$data = json_decode(file_get_contents('php://input'));
	if($data->secret !== getenv('VK_CONFIRMATION_CODE') && $data->type !== 'confirmation')
		return 'invalid_token';
	
	switch($data->type)
	{
		case 'confirmation':
			return getenv('VK_CONFIRMATION_CODE');//VK_CONFIGURATION_CODE
			break;
		
		
		case 'message_new':
			$token = getenv('VK_TOKEN');
			$user_id = $data->object->user_id; 
			$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.69")); 
			$user_name = $user_info->response[0]->first_name; 
			
			$received_message = $data->object->text;
			/*
			if($received_message == 'Начать')
			{
				$send_message = "Привет, {$user_name}! Я супер крутой бот 2999!"; //Внизу появились кнопочки, выбери нужную и нажми на нее.";
			}
		//	button_type($received_message);
			else
			{
				$send_message = "{$user_name},я не очень умный бот, поэтому не понимаю, что ты пишешь.";// Пожалуйста, используй кнопки, если их нет, напиши !кнопки";
			}
			*/
			$request_params = array(
				'user_id' => $user_id, 
				'message' => "Привет, {$user_name}! Я очень умный бот(нет)", 
				'access_token' => getenv('VK_TOKEN'),
				'v' => '5.69'
			);

			$get_params = http_build_query($request_params); 
			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 
			return('OK');
		//////
			break;

		default:
			return('OK');
			break;
	}
	
	
	
});

/*
function button_type($received_message)
{
	switch($received_message)
	{
		case '
	
	return 
}
*/
$app->run();
