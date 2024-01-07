<?php

namespace Controllers;

require_once '/home/server/pr/ndfyr/vendor/autoload.php';

use SergiX44\Nutgram\Logger\ConsoleLogger;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboarndButton;
use SergiX44\Nutgram\Telegram\Properties\MessageType;
use RedBeanPHP\R;
use SergiX44\Nutgram\Telegram\Types\Input\InputMediaPhoto;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use SergiX44\Nutgram\Telegram\Properties\InputMediaType;
use SergiX44\Nutgram\Telegram\Types\BaseType;
use SergiX44\Nutgram\Telegram\Types\Internal\Uploadable;
use Controllers\DataBaseController;
use SergiX44\Nutgram\Telegram\Types\Payment\PreCheckoutQuery;
use Controllers\AdvertisementsController;
use Controllers\ImageController;
use RedUNIT\Base\Database;

class AdminController
{

    public static $admins = ['5330922158', '5989991134', "6915367476"];

    public static function send($bot, $text)
    {
        if (in_array($bot->userId(), self::$admins)) {
            $users = R::findAll('users');
            foreach ($users as $user) {
                $id = $user->telegram;
                $bot->sendMessage($text, $id);
            }
        } else {
            $bot->sendMessage('You are not an admin');
        }
    }


    public static function give($name, $amount, $bot)
    {

        if (in_array($bot->userId(), self::$admins)) {
            $user = R::findOne('users', 'username = ?', [$name]);
            $user->tokens = $user->tokens + $amount;
            R::store($user);
        } else {
            $bot->sendMessage('You are not an admin');
        }
    }

    public static function stats($bot)
    {

        if (in_array($bot->userId(), self::$admins)) {
            $users = DataBaseController::getData();
            $count = count($users);
            $bot->sendMessage("Total Users: $count");
        } else {
            $bot->sendMessage('You are not an admin');
        }
    }
}
