<?php namespace NotionVK\Bot\Dispatchers;

use function NotionVK\KeyboardUtil\getDatabaseOptionsInlineKeyboard;
use function NotionVK\KeyboardUtil\getDatabaseSelectedStateKeyboard;
use NotionVK\Bot\DispatcherBase;
use NotionVK\Bot\SessionState;
use Notion\Notion;
use VK\Client\VKApiClient;

class GetDatabaseDispatcher extends DispatcherBase
{

    const START_WITH = "#получить";
    private $database_id;
    private $notion_api;

    public function __construct(string $query)
    {
        $this->notion_api = new Notion($_SESSION["notion_secret"]); // $_SESSION["notion_secret"] must exist due to invariant of SessionState::INITIALIZED_SECRET
        $exploded = explode(' ', $query, 2);
        if (count($exploded) < 2) {
            $this->database_id = null;
        } else {
            $this->database_id = $exploded[1];
        }
    }

    public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token)
    {
        $user_id = $message_object["message"]->from_id;
        if ($this->database_id == null) {
            $databaseOptions = $this->notion_api
                ->database()
                ->ids();
            $vk_api->messages()->send($access_token, array(
                "user_id" => $user_id,
                "peer_id" => $user_id,
                "random_id" => random_int(0, PHP_INT_MAX),
                "message" => "Доступные базы данных:",
                "keyboard" => getDatabaseOptionsInlineKeyboard($databaseOptions),
            ));
            return;
        }

        $database = $this->notion_api->database($this->database_id)->query()->get();

        if ($database == null) {
            //Try to use $this->database_id as database name
            $databaseOptions = $this->notion_api
                ->database()
                ->ids();
            foreach ($databaseOptions as $id => $name) {
                if (strcmp($name, $this->database_id) == 0) {
                    $database = $this->notion_api->database($id)->query()->get();
                    $this->database_id = $id;
                    break;
                }
            }
        }

        if ($database == null) {
            $vk_api->messages()->send($access_token, array(
                "user_id" => $user_id,
                "peer_id" => $user_id,
                "random_id" => random_int(0, PHP_INT_MAX),
                "message" => "База данных " . $this->database_id . " не найдена",
            ));
            return;
        }

        $answer = "Выбрана база данных " . $this->database_id . ":<br>";
        foreach ($database->pages as $page) {
            $answer = $answer . $page->name . "<br>";
        }

        $vk_api->messages()->send($access_token, array(
            "user_id" => $user_id,
            "peer_id" => $user_id,
            "random_id" => random_int(0, PHP_INT_MAX),
            "message" => $answer,
            "keyboard" => getDatabaseSelectedStateKeyboard(),
        ));

        SessionState::setDatabaseSelected($_SESSION["workspace_meta"], $_SESSION["notion_secret"], $this->database_id);
    }

    public static function getDispatcherCommandName()
    {
        return static::START_WITH;
    }

    public static function check(string $query)
    {
        $lowered = mb_strtolower($query);
        return str_starts_with($lowered, static::START_WITH) && $_SESSION["state"] >= SessionState::INITIALIZED_SECRET;
    }

}
