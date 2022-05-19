<?php namespace NotionVK\Bot\Dispatchers;

use NotionVK\Bot\DispatcherBase;
use NotionVK\Bot\SessionState;
use VK\Client\VKApiClient;

class DeletePageFromDatabaseDispatcher extends DispatcherBase
{

    const START_WITH = "#удалить страницу";
    private $database_id;
    private $page_title;

    public function __construct(string $query)
    {
        $this->database_id = $_SESSION["chosen_database_id"]; // $_SESSION["chosen_database_id"] must exist due to invariant of SessionState::DATABASE_SELECTED
        $exploded = explode(' ', $query, 3);
        if (count($exploded) < 3) {
            $this->page_title = null;
        } else {
            $this->page_title = $exploded[2];
        }
    }

    public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token)
    {
        $user_id = $message_object["message"]->from_id;
        if ($this->page_title == null) {
            SessionState::setPageDeleting($_SESSION["workspace_meta"], $_SESSION["notion_secret"], $_SESSION["chosen_database_id"]);
            $vk_api->messages()->send($access_token, array(
                "user_id" => $user_id,
                "peer_id" => $user_id,
                "random_id" => random_int(0, PHP_INT_MAX),
                "message" => "Введите заголовок удаляемой страницы",
            ));
        } else {
            $dispatcher = new DeletePageDispatcher($this->page_title);
            $dispatcher->dispatch($user_id, $vk_api, $access_token);
        }

    }

    public static function getDispatcherCommandName() //TODO: Delete copy-paste

    {
        return static::START_WITH;
    }

    public static function check(string $query)
    {
        $lowered = mb_strtolower($query);
        return str_starts_with($lowered, static::START_WITH) && $_SESSION["state"] >= SessionState::DATABASE_SELECTED;
    }

}
