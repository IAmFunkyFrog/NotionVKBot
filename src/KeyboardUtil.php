<?php namespace NotionVK\KeyboardUtil;

use NotionVK\Bot\Dispatchers\AddPageDispatcher;
use NotionVK\Bot\Dispatchers\DeletePageDispatcher;
use NotionVK\Bot\Dispatchers\ErrorDispatcher;
use NotionVK\Bot\Dispatchers\GetDatabaseDispatcher;
use NotionVK\Bot\Dispatchers\GetNotionSecretDispatcher;

function getUnitializedStateKeyboard()
{
    return json_encode(array(
        "one_time" => false,
        "buttons" => array(
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Авторизоваться",
                        "payload" => array("query" => GetNotionSecretDispatcher::START_WITH),
                    ),
                ),
            ),
        ),
        "inline" => false,
    ));
}

function getInitializedSecretStateKeyboard()
{
    return json_encode(array(
        "one_time" => false,
        "buttons" => array(
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Получить все базы данных",
                        "payload" => array("query" => GetDatabaseDispatcher::START_WITH),
                    ),
                    "color" => "primary",
                ),
            ),
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Помощь",
                        "payload" => array("query" => ErrorDispatcher::START_WITH),
                    ),
                ),
            ),
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Авторизоваться",
                        "payload" => array("query" => GetNotionSecretDispatcher::START_WITH),
                    ),
                ),
            ),
        ),
        "inline" => false,
    ));
}

function getDatabaseSelectedStateKeyboard()
{
    return json_encode(array(
        "one_time" => false,
        "buttons" => array(
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Добавить страницу",
                        "payload" => array("query" => AddPageDispatcher::START_WITH),
                    ),
                    "color" => "positive",
                ),
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Удалить страницу",
                        "payload" => array("query" => DeletePageDispatcher::START_WITH),
                    ),
                    "color" => "negative",
                ),
            ),
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Получить все базы данных",
                        "payload" => array("query" => GetDatabaseDispatcher::START_WITH),
                    ),
                    "color" => "primary",
                ),
            ),
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Помощь",
                        "payload" => array("query" => ErrorDispatcher::START_WITH),
                    ),
                ),
            ),
            array(
                array(
                    "action" => array(
                        "type" => "text",
                        "label" => "Авторизоваться",
                        "payload" => array("query" => GetNotionSecretDispatcher::START_WITH),
                    ),
                ),
            ),
        ),
        "inline" => false,
    ));
}

function getDatabaseOptionsInlineKeyboard($databaseOptions)
{
    $keyboard = [];
    foreach ($databaseOptions as $id => $name) {
        $keyboard[] = array(
            array(
                "action" => array(
                    "type" => "text",
                    "label" => $name,
                    "payload" => array("query" => GetDatabaseDispatcher::START_WITH . " $id"),
                ),
            ),
        );
    }
    return json_encode(array(
        "one_time" => false,
        "buttons" => $keyboard,
        "inline" => true,
    ));
}
