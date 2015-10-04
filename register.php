<?php
include 'Telegram.php';

$API_KEY = '';  // The token to access the Telegram HTTP API
$BOT_NAME = 'psbbbot';

$telegram = new Telegram($API_KEY, $BOT_NAME);

$result = $telegram->setWebhook($_GET['url']);
print_r($result);
?>
