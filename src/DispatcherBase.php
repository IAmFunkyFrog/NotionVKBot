<?php namespace NotionVK\Bot;

use NotionVK\Bot\Dispatchers\AddPageDispatcher;
use NotionVK\Bot\Dispatchers\DeletePageDispatcher;
use NotionVK\Bot\Dispatchers\DeletePageFromDatabaseDispatcher;
use NotionVK\Bot\Dispatchers\ErrorDispatcher;
use NotionVK\Bot\Dispatchers\GetDatabaseDispatcher;
use NotionVK\Bot\Dispatchers\GetNotionSecretDispatcher;
use NotionVK\Bot\Dispatchers\PatchDatabaseDispatcher;
use VK\Client\VKApiClient;

abstract class DispatcherBase
{
    abstract public function dispatch(array $message_object, VKApiClient $vk_api, string $access_token);
    abstract public static function check(string $query);
    abstract public static function getDispatcherCommandName();

    public static function makeDispatcher(string $query)
    {
        if (GetDatabaseDispatcher::check($query)) {
            return new GetDatabaseDispatcher($query);
        } else if (PatchDatabaseDispatcher::check($query)) {
            return new PatchDatabaseDispatcher($query);
        } else if (GetNotionSecretDispatcher::check($query)) {
            return new GetNotionSecretDispatcher($query);
        } else if (DeletePageFromDatabaseDispatcher::check($query)) {
            return new DeletePageFromDatabaseDispatcher($query);
        } else if (AddPageDispatcher::check($query)) {
            return new AddPageDispatcher($query);
        } else if (DeletePageDispatcher::check($query)) {
            return new DeletePageDispatcher($query);
        }

        return new ErrorDispatcher($query);
    }
}
