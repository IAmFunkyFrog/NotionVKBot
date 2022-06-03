<?php namespace NotionVK\Bot\Dispatchers;

use NotionVK\Bot\DispatcherBase;
use VK\Client\VKApiClient;

class GetNotionSecretDispatcher extends DispatcherBase
{

    const START_WITH = "#авторизоваться";

    public function __construct()
    {

    }

    public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token)
    {
        $user_id = $message_object["message"]->from_id;
        $redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/notion_auth.php?user_id=$user_id"; //TODO: fix double code with file notion_auth.php
        $vk_api->messages()->send($access_token, array(
            "user_id" => $user_id,
            "peer_id" => $user_id,
            "random_id" => random_int(0, PHP_INT_MAX),
            "message" => "Перейдите по ссылке для авторизации:<br>" . $redirect_uri,
        ));
    }

    public static function getDispatcherCommandName()
    {
        return static::START_WITH;
    }

    public static function check(string $query)
    {
        $lowered = mb_strtolower($query);
        return str_starts_with($lowered, static::START_WITH);
    }

}
