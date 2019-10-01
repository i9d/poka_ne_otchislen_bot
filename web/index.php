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
 return '0b9df2d1';
});

$app->post('/', function() use($app) {
	$data = json_decode(file_get_contents('php://input'));
	if(!$data) //пустой
	if($data->secret !== getenv('VK_CONFIGURATION_CODE:') && $data->type !== 'confirmation')
		return 'sovsemneok'; //если токен не совпадает
	
	switch($data->type)
	{
		case 'confirmation':
		//	return getenv('VK_CONFIGURATION_CODE:');//VK_CONFIGURATION_CODE:
			return '0b9df2d1';
			break;
		
		case 'message_new':
		
		
		//////
			break;
	}
	
	
 return "oh fignya kakayato";
});

$app->run();
