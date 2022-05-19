<?php namespace NotionVK;

function require_all($dir)
{
    foreach (scandir($dir) as $filename) {
        $path = $dir . '/' . $filename;
        if (is_file($path)) {
            require_once $path;
        } else if ($filename != "." && $filename != "..") {
            require_all($path);
        }
    }
}

require_once __DIR__ . "/vendor/autoload.php";
require_all(__DIR__ . "/src");

use NotionVK\Bot\BotServerHandler;
use NotionVK\Bot\SessionState;

$rawdata = file_get_contents('php://input');
$data = json_decode($rawdata);

if (!isset($data->object->message->from_id)) {
    echo 'error';
    return;
}

session_id($data->object->message->from_id);
session_start();

if (!isset($_SESSION["state"])) {
    SessionState::setUnitialized();
}

$handler = new BotServerHandler();
$handler->parse($data);
