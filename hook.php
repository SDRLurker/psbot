<?php
include 'Telegram.php';
include 'Baseball.php';

$API_KEY = '';  // The token to access the Telegram HTTP API
$BOT_NAME = 'psbbbot'; 

$telegram = new Telegram($API_KEY, $BOT_NAME);
$msg = $telegram->getWebhook();

$game = new Baseball($msg, $telegram);
$game->process();
?>
