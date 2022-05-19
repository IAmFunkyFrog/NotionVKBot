<?php namespace NotionVK\Bot\Dispatchers;

use NotionVK\Bot\DispatcherBase;
use NotionVK\Bot\SessionState;
use Notion\Blocks\ImageBlock;
use Notion\Blocks\ParagraphBlock;
use Notion\Notion;
use Notion\RichText;
use VK\Client\VKApiClient;

class AddPageDispatcher extends DispatcherBase
{

    const START_WITH = "#добавить страницу";
    const IMAGE_TYPES = ["s", "m", "x", "y", "z", "w"];
    private $database_id;
    private $new_page_title;
    private $new_page_content = "";
    private $notion_api;

    public function __construct(string $query)
    {
        $this->notion_api = new Notion($_SESSION["notion_secret"]); // $_SESSION["notion_secret"] must exist due to invariant of SessionState::DATABASE_SELECTED
        $this->database_id = $_SESSION["chosen_database_id"]; // $_SESSION["chosen_database_id"] must exist due to invariant of SessionState::DATABASE_SELECTED
        $exploded = explode("\n", $query, 2);
        $this->new_page_title = $exploded[0];
        if (count($exploded) > 1) {
            $this->new_page_content = $exploded[1];
        }

    }

    public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token)
    {
        $user_id = $message_object["message"]->from_id;

        if (strcmp($this->new_page_title, "") == 0) {
            $vk_api->messages()->send($access_token, array(
                "user_id" => $user_id,
                "peer_id" => $user_id,
                "random_id" => random_int(0, PHP_INT_MAX),
                "message" => "Нельзя создать страницу с пустым заголовком",
            ));
            return;
        }

        $database = $this->notion_api->database($this->database_id)->get();

        $page = $database->newPage();
        $page->name = $this->new_page_title;
        $page->showOnWebsite = true;

        if (strcmp($this->new_page_content, "") != 0) {
            $page->addBlock(new ParagraphBlock(RichText::fromPlainText($this->new_page_content)));
        }

        foreach ($message_object["message"]->attachments as $attachment) {
            $page->addBlock($this->getBlockFromAttachment($attachment));
        }

        foreach ($message_object["message"]->fwd_messages as $fwd_message) {
            static::processForwardMessage($fwd_message, $page);
        }

        $response = $page->save();
        file_put_contents('php://stderr', print_r($response, true));

        $vk_api->messages()->send($access_token, array(
            "user_id" => $user_id,
            "peer_id" => $user_id,
            "random_id" => random_int(0, PHP_INT_MAX),
            "message" => "Добавлена страница с заголовком: " . $this->new_page_title,
        ));

        SessionState::setDatabaseSelected($_SESSION["workspace_meta"], $_SESSION["notion_secret"], $_SESSION["chosen_database_id"]);
    }

    public static function getDispatcherCommandName()
    {
        return static::START_WITH;
    }

    public static function check(string $query)
    {
        return $_SESSION["state"] == SessionState::PAGE_ADDING || ($_SESSION["state"] >= SessionState::DATABASE_SELECTED && $_SESSION["state"] != SessionState::PAGE_DELETING && !str_starts_with($query, "#"));
    }

    private static function getBlockFromAttachment($attachment)
    {
        if (strcmp($attachment->type, "photo") == 0) {
            $best_url = null;
            $type = "";
            foreach ($attachment->photo->sizes as $photo_size) {
                if (array_search($type, static::IMAGE_TYPES) == false || array_search($photo_size->type, static::IMAGE_TYPES) > array_search($type, static::IMAGE_TYPES)) {
                    $type = $photo_size->type;
                    $best_url = $photo_size->url;
                }
            }
            return new ImageBlock($best_url);
        } else if (strcmp($attachment->type, "doc") == 0) {
            $title = $attachment->doc->title;
            $link = $attachment->doc->url;
            return new ParagraphBlock(RichText::fromLink($link, $title));
        } else if (strcmp($attachment->type, "link") == 0) {
            $title = $attachment->link->title;
            $link = $attachment->link->url;
            return new ParagraphBlock(RichText::fromLink($link, $title));
        } else {
            return new ParagraphBlock(RichText::fromPlainText("ОШИБКА: " . $attachment->type . " не поддерживается"));
        }
    }

    private static function processForwardMessage($fwd_message, $page)
    {
        if (strcmp($fwd_message->text, "") != 0) {
            $page->addBlock(new ParagraphBlock(RichText::fromPlainText($fwd_message->text)));
        }

        foreach ($fwd_message->attachments as $attachment) {
            $page->addBlock(static::getBlockFromAttachment($attachment));
        }

        if (isset($fwd_message->fwd_messages)) {
            foreach ($fwd_message->fwd_messages as $fwd_message_next) {
                static::processForwardMessage($fwd_message_next, $page);
            }
        }

    }

}
