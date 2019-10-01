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
		
		case 'message_new':
			$request_params = array)
				'user_id' => $data->object->user_id,
				'message' => 'Привет! Я очень умный бот(нет)',
				'acces_token => getenv('VK_TOKEN'),
				'v' = '5,69'
			);
		
		//////
			break;
	}
	
	
 return "oh fignya kakayato";
});

$app->run();
