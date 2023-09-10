<?php

namespace Controllers;

use RedBeanPHP\R;

R::setup('mysql:host=localhost;dbname=telegrambotCR', 'root', '');
class commandsController
{
    private static $token = '6543867536:AAGV0XaAyLcqU8LXpJuji7TubC-18tik0ho';
    private static $offset = 0;

    public static function listen()
    {
        while (true) {
            $updates = json_decode(file_get_contents("https://api.telegram.org/bot" . self::$token . "/getUpdates?offset=" . self::$offset), true);
            if (!empty($updates['result'])) {
                foreach ($updates['result'] as $update) {
                    $telegram_id = $update['message']['from']['id'];
                    $user = R::findOne('users', 'telegram_id = ?', [$telegram_id]);
                    if (!$user) {
                        self::$offset = $update['update_id'] + 1;
                        self::createUser($update);
                    }
                    if (isset($update['message']['photo'])) {
                        $photo = $update['message']['photo'][3]['file_id'];
                        $fileId = self::getFile($photo);
                        self::getPhoto($fileId);

                        self::$offset = $update['update_id'] + 1;
                    }

                    if (isset($update['message']['text'])) {

                        $messages = R::dispense('messages');
                        $messages->telegram_id = $update['message']['from']['id'];
                        $messages->message = $update['message']['text'];
                        R::store($messages);

                        if (strpos($update['message']['text'], '/') === 0) {
                            $messageText = $update['message']['text'];
                            $chatId = $update['message']['from']['id'];
                            self::commandHandler($chatId, $messageText);
                            self::$offset = $update['update_id'] + 1;
                        }
                    }
                }
            }
            sleep(1);
        }
    }
    public static function createUser($data)
    {
        $userid = $data['message']['from']['id'];
        $username = $data['message']['from']['username'];
        $user = R::dispense('users');
        $user->telegram_id = $userid;
        $user->username = $username;
        R::store($user);
    }

    public static function commandHandler($chatId, $methodName)
    {
        $methodName = str_replace('/', '', $methodName);
        method_exists(__CLASS__, $methodName) ? self::$methodName($chatId) : self::sendMessage($chatId, "Command not recognized: $methodName");
    }

    public static function start($chatId)
    {
        self::sendMessage(
            $chatId,
            "Your Account Status\n━━━━━━━━━\nID:\nName:\nUsername:  \nTokens:\nPlan: Free User\nAntispam: *Enabled* \nLanguage: English\n━━━━━━━━━\n\nBot Status: `Online`"


        );
    }

    public static function sendMessage($chatId, $text)
    {
        $url = "https://api.telegram.org/bot" . self::$token . "/sendMessage?parse_mode=markdown";
        $data = ['chat_id' => $chatId, 'text' => $text];
        $options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($data)]];
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

    public static function getFile($id)
    {
        $url = "https://api.telegram.org/bot" . self::$token . "/getFile?file_id=$id";
        $rawdata = file_get_contents($url);
        $data = json_decode($rawdata, true);
        return $data['result']['file_path'];
    }
    public static function getPhoto($id)
    {
        $url = "https://api.telegram.org/file/bot" . self::$token . "/$id";
        file_put_contents('../images/image.jpg', file_get_contents($url));
        echo "done";
    }
    public static function freeToken($id)
    {
        $tempkey = rand(10000, 99999);
        r::exec("UPDATE users SET tempkey = $tempkey WHERE telegram_id = $id");
        $link = file_get_contents("http://adfoc.us/api/?key=4a950ed379959bde2fe57166af3ede54&url=http://clothesremoved.com/link.php?id=$id&tempkey=$tempkey");
        self::sendMessage($id, $link);
    }

}

