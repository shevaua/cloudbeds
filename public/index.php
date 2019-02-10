<?php

define('PROJECT_PATH', realpath(__DIR__. '/../')); 
define('APP_PATH', realpath(PROJECT_PATH . '/app/'));
define('APP_ENV', 'production');

require_once APP_PATH . '/Application.php';

$app = Application::getInstance();

$app->run();
