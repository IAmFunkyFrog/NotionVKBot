<?php namespace NotionVK;

require_once __DIR__ . "/vendor/autoload.php";

use NotionVK\Bot\BotServerHandler;

$rawdata = file_get_contents('php://input');
$data = json_decode($rawdata);

if (!isset($data->object->message->from_id)) {
    echo 'error';
    return;
}

session_id($data->object->message->from_id);
session_start();

$handler = new BotServerHandler();
$handler->parse($data);
