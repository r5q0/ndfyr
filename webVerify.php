<?php
include_once 'vendor/autoload.php';
use RedBeanPHP\R;
R::setup('mysql:host=localhost;dbname=ndfyr', 'root', '');
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
    $user = R::findOne('users', 'telegram = ?', [$id]);
    if ($user) {
        if ($user->tempkey == $tempkey) {
            $user->tokens = $user->tokens + 0.1;
            $user->tempkey = rand(10000000000000000, 99999000000000000);
            R::store($user);
            header('Location: https://google.com');
        } else {
            header('Location: https://google.com');
        }
    } else {
        header('Location: https://google.com');
    }
}

