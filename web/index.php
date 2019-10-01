<?php
require('../vendor/autoload.php');

$VK_CONFIGURATION_CODE = '0b9df2d1';
$VK_SECRET_TOKEN='superkluch9999999pyattrisem';
$VK_TOKEN='0736a0cdc42087343c259845f58b9a7b12ea38ff65515261bffd0e34bdd6bd509d1380c46013536150b82';

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));


// Our web handlers

$app->get('/', function() use($app) {
 return $VK_CONFIGURATION_CODE;
});

$app->post('/', function() use($app) {
	$data = json_decode(file_get_contents('php://input'));
	if(!$data) //пустой
	if($data->secret !== $VK_CONFIGURATION_CODE) && $data->type !== 'confirmation')
		return 'sovsemneok'; //если токен не совпадает
	
	switch($data->type)
	{
		case 'confirmation':
			return $VK_CONFIGURATION_CODE;//VK_CONFIGURATION_CODE:
			break;
		
		case 'message_new':
		
		
		//////
			break;
	}
	
	
 return "oh fignya kakayato";
});

$app->run();
