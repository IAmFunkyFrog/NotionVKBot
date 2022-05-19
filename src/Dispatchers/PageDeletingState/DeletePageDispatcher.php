<?php namespace NotionVK\Bot\Dispatchers;

use NotionVK\Bot\DispatcherBase;
use NotionVK\Bot\SessionState;
use Notion\Notion;
use VK\Client\VKApiClient;

class DeletePageDispatcher extends DispatcherBase
{

    const START_WITH = "#удалить страницу";
    private $database_id;
    private $page_title;
    private $notion_api;

    public function __construct(string $query)
    {
        $this->notion_api = new Notion($_SESSION["notion_secret"]); // $_SESSION["notion_secret"] must exist due to invariant of SessionState::DATABASE_SELECTED
        $this->database_id = $_SESSION["chosen_database_id"]; // $_SESSION["chosen_database_id"] must exist due to invariant of SessionState::DATABASE_SELECTED
        $this->page_title = $query;
    }

    public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token)
    {
        $user_id = $message_object["message"]->from_id;
        $database = $this->notion_api->database($this->database_id)->query()->get();

        $found_page = null;

        foreach ($database->pages as $page) {
            if (strcmp($this->page_title, $page->name) == 0) {
                $found_page = $page;
                break;
            }
        }

        if ($found_page == null) {
            $vk_api->messages()->send($access_token, array(
                "user_id" => $user_id,
                "peer_id" => $user_id,
                "random_id" => random_int(0, PHP_INT_MAX),
                "message" => "Не найдена страница с заголовком: " . $this->page_title,
            ));
        } else {
            $found_page->archive();

            $vk_api->messages()->send($access_token, array(
                "user_id" => $user_id,
                "peer_id" => $user_id,
                "random_id" => random_int(0, PHP_INT_MAX),
                "message" => "Удалена страница с заголовком: " . $this->page_title,
            ));
        }

        SessionState::setDatabaseSelected($_SESSION["workspace_meta"], $_SESSION["notion_secret"], $_SESSION["chosen_database_id"]);
    }

    public static function getDispatcherCommandName() //TODO: Delete copy-paste

    {
        return static::START_WITH;
    }

    public static function check(string $query)
    {
        return $_SESSION["state"] == SessionState::PAGE_DELETING;
    }

}
