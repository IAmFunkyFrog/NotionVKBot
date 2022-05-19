<?php namespace NotionVK\Bot;

use NotionVK\Bot\DispatcherBase;
use VK\CallbackApi\Server\VKCallbackApiServerHandler;
use VK\Client\VKApiClient;
use \Exception;

class BotServerHandler extends VKCallbackApiServerHandler
{
    const GROUP_ID = 212106635;
    const CONFIRMATION_TOKEN = 'NOT DEFINED';
    const ACCESS_TOKEN = 'NOT DEFINED';
    private $vk_api;

    public function __construct()
    {
        $this->vk_api = new VKApiClient();
    }

    public function confirmation(int $group_id, ?string $secret)
    {
        if ($group_id === static::GROUP_ID) {
            echo static::CONFIRMATION_TOKEN;
        }
    }

    public function messageNew(int $group_id, ?string $secret, array $object)
    {
        try {
            if ($object["message"]->from_id != $object["message"]->peer_id) {
                echo 'ok';
                return;
            }

            $query = "";
            if (!isset($object["message"]->payload)) {
                $query = $object["message"]->text;
            } else {
                $decoded = json_decode($object["message"]->payload);
                $query = $decoded->query;
            }

            $dispatcher = DispatcherBase::makeDispatcher($query);
            $dispatcher->dispatch($object, $this->vk_api, static::ACCESS_TOKEN);

            echo 'ok';
        } catch (Exception $e) {
            file_put_contents('php://stderr', print_r($e, true));
        }
    }
}
