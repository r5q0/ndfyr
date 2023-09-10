<?php
include_once 'vendor/autoload.php';
R::setup('mysql:host=localhost;dbname=telegrambotCR', 'root', '');
$weblog = R::findOne('webvisits', 'telegram_id = ?', [$id]);
if (!$weblog) {
    $weblog = R::dispense('webvisits');
    $weblog->telegram_id = $id;
    $weblog->ip = $_SERVER['REMOTE_ADDR'];
    R::store($weblog);
}
if (isset($_GET['id']) && isset($_GET['tempkey'])) {
    $id = $_GET['id'];
    $tempkey = $_GET['tempkey'];
    $user = R::findOne('users', 'telegram_id = ?', [$id]);
    if ($user) {
        if ($user->tempkey == $tempkey) {
            $user->tokens = $user->tokens + 1;
            $user->tempkey = rand(10000000000000000, 99999000000000000);
            R::store($user);
            sendMessage($id, '1 token has been added to your account');
            header('Location: https://google.com');
        } else {
            header('Location: https://google.com');
        }
    } else {
        header('Location: https://google.com');
    }
}

function sendMessage($chatId, $text)
{
    $token = '6543867536:AAGV0XaAyLcqU8LXpJuji7TubC-18tik0ho';
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?parse_mode=markdown";
    $data = ['chat_id' => $chatId, 'text' => $text];
    $options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($data)]];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}
