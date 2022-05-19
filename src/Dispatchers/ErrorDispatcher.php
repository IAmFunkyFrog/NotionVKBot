<?php namespace NotionVK\Bot\Dispatchers;

use NotionVK\Bot\DispatcherBase;
use NotionVK\Bot\SessionState;
use VK\Client\VKApiClient;

class ErrorDispatcher extends DispatcherBase
{
    const START_WITH = "#помощь";

    private $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token)
    {
        $user_id = $message_object["message"]->from_id;
        switch ($_SESSION["state"]) {
            case SessionState::UNITIALIZED:
                $vk_api->messages()->send($access_token, array(
                    "user_id" => $user_id,
                    "peer_id" => $user_id,
                    "random_id" => random_int(0, PHP_INT_MAX),
                    "message" => "Необходимо авторизоваться, воспользуйтесь командой: " . GetNotionSecretDispatcher::START_WITH,
                ));
                break;
            default:
                $vk_api->messages()->send($access_token, array(
                    "user_id" => $user_id,
                    "peer_id" => $user_id,
                    "random_id" => random_int(0, PHP_INT_MAX),
                    "message" => "Команды:<br>#авторизоваться - получить новую ссылку на авторизацию<br>#получить - показать список всех привязанных к интеграции баз данных<br>#получить <id> - получить базу данных с указанным id",
                ));
                break;
        }
    }

    public static function getDispatcherCommandName()
    {
        return static::START_WITH;
    }

    public static function check(string $query)
    {
        return true;
    }

}
