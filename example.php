<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$config = new Phapper\Config([
    'username'      => getenv('REDDIT_USERNAME'),
    'password'      => getenv('REDDIT_PASSWORD'),
    'appId'         => getenv('REDDIT_APPID'),
    'appSecret'     => getenv('REDDIT_APP_SECRET'),
    'oauthEndpoint' => getenv('REDDIT_OATH_ENDPOINT'),
    'basicEndpoint' => getenv('REDDIT_BASIC_ENDPOINT'),
    'userAgent'     => 'Test app using Phapper by /u/'.getenv('REDDIT_USERNAME').' (Phapper 1.0)',
    'debug'         => (bool)getenv('debug'),
]);

$reddit = new Phapper\Phapper($config);
var_dump($reddit->getMe());