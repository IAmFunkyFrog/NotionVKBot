<?php

// FIXME refactor this file

require_once __DIR__ . "/vendor/autoload.php";

use function NotionVK\KeyboardUtil\getInitializedSecretStateKeyboard;
use GuzzleHttp\Client;
use NotionVK\Bot\BotServerHandler;
use NotionVK\Bot\SessionState;
use VK\Client\VKApiClient;

$CLIENT_ID = "NOT DEFINED";
$BASIC_AUTH_ENCODED = "NOT DEFINED";

function auth_with_code()
{
    global $BASIC_AUTH_ENCODED;
    session_start();
    $client = new Client();

    $user_id = $_SESSION["user_id"];

    session_destroy();
    session_abort();

    session_id($user_id);
    session_start();

    $response = $client->post('https://api.notion.com/v1/oauth/token', array(
        'headers' =>
        [
            'Authorization' => "Basic $BASIC_AUTH_ENCODED",
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode(array(
            'grant_type' => 'authorization_code',
            'code' => $_GET["code"],
        )),
    ));

    $body = $response->getBody()->getContents();
    $decoded = json_decode($body);
    SessionState::setInitializedSecret($body, $decoded->access_token);

    echo "Вы успешно авторизованы" . "<br>";
    echo $user_id . "<br>";

    try {
        $vk_api = new VKApiClient();
        $vk_api->messages()->send(BotServerHandler::ACCESS_TOKEN, array(
            "user_id" => $user_id,
            "peer_id" => $user_id,
            "random_id" => random_int(0, PHP_INT_MAX),
            "message" => "Вы успешно авторизованы",
            "keyboard" => getInitializedSecretStateKeyboard(),
        ));
    } catch (Exception $e) {
        var_dump($e);
    }

}

function prepare_auth()
{
    session_start();
    global $CLIENT_ID;
    $_SESSION["user_id"] = $_GET["user_id"];
    $redirect_uri = "https://api.notion.com/v1/oauth/authorize?owner=user&client_id=$CLIENT_ID&response_type=code";
    header('Location: ' . $redirect_uri);
}

if (!isset($_GET["code"]) && isset($_GET["user_id"])) {
    prepare_auth();
} else if (isset($_GET["code"])) {
    auth_with_code();
} else {
    //Should not reach
    echo "error";
}
