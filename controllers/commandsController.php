<?php

namespace Controllers;

include_once '../vendor/autoload.php';

use SergiX44\Nutgram\Logger\ConsoleLogger;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboarndButton;
use SergiX44\Nutgram\Telegram\Properties\MessageType;
use RedBeanPHP\R;
use SergiX44\Nutgram\Telegram\Types\Input\InputMediaPhoto;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use SergiX44\Nutgram\Telegram\Properties\InputMediaType;
use SergiX44\Nutgram\Telegram\Types\BaseType;
use SergiX44\Nutgram\Telegram\Types\Internal\Uploadable;
use Controllers\DataBaseController;

class CommandsController
{
    // $bot = new Nutgram('5971524781:AAF6CcvpST9I9A8G9miZD1C3hK2XNDSts4g', new Configuration(
    //     logger: ConsoleLogger::class
    // ));
    private static $bot;
    public function __construct($token)
    {
        self::$bot = new Nutgram($token);
        self::CommandStart();
        self::$bot->run();

    }
    public static function DatabaseListener($text)
    {

        $userId = self::$bot->userId();
        $userExist = DataBaseController::UserExists($userId);

        $username = self::$bot->user()->username;
        $language_code = self::$bot->user()->language_code;
        if ($userExist == false) {
            DataBaseController::InsertUser($username, $userId, $language_code);
        }
        DataBaseController::InsertMessage($userId, $text);
    }

    public static function CommandStart()
    {

        self::$bot->onCommand('start', function () {
            self::DatabaseListener('/start');
            self::$bot->SendPhoto(
                photo: InputFile::make(fopen('../1.png', 'rb')),
                caption: 'Welcome To our bot',
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make('My Account', callback_data: '/me'),
                        InlineKeyboardButton::make('Channel', url: 'https://t.me/ClothesRemovedGroup')
                    )->addRow(
                        InlineKeyboardButton::make('Buy', callback_data: '/buy')
                    )
            );
        });

        self::$bot->onCallbackQueryData('/me', function () {
            self::UserInfo();
            self::DatabaseListener('/me');
        });
        self::$bot->onCallbackQueryData('/buy', function () {
            self::BuyMessage();
            self::DatabaseListener('/buy');
        });
    }
    public static function buy($quantity)
    {

        $USD = ($quantity == 20) ? 3 : (($quantity == 40) ? 5 : (($quantity == 100) ? 10 : (($quantity == 500) ? 25 : 100000)));
        

            $labeledPrices = [
                ['label' => "$quantity Tokens", 'amount' => $USD * 100],
            ];
        
            $link = self::$bot->createInvoiceLink("$quantity Tokens", 'Ndfyr Tokens', $quantity, '284685063:TEST:NmZjMzAyYjVjOGEy', 'USD', $labeledPrices);
            return $link;
    }
    public static function BuyMessage()
    {
        self::$bot->sendMessage(
            'text',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('20 Tokens (3$', url: self::buy(20)),
                    InlineKeyboardButton::make('40 Tokens (5$)', url: self::buy(40))
                )->addRow(
                    InlineKeyboardButton::make('100 Tokens (10$)', url: self::buy(100)),
                    InlineKeyboardButton::make('500 Tokens (25$)', url: self::buy(500))

                )




        );
    }

    public static function UserInfo()
    {
        $id = self::$bot->userId();
        $username = self::$bot->user()->username;
        $language_code = self::$bot->user()->language_code;
        $tokens = DataBaseController::GetTokens($id);
        self::$bot->sendMessage(" Your ID: $id\n Your Username: $username\n Your Language Code: $language_code\n Your Tokens: $tokens ");
    }
    public static function PaymentListener(){

            self::$bot->onSuccessfulPaymentPayload('50', $result (){



            });}


    public static function getFile($id)
    {
        $url = "https://api.telegram.org/bot" . $GLOBALS['bot']->getToken() . "/getFile?file_id=$id";
        $rawdata = file_get_contents($url);
        $data = json_decode($rawdata, true);
        return $data['result']['file_path'];
    }

    public static function getPhoto($id)
    {
        $url = "https://api.telegram.org/file/bot" . $GLOBALS['bot']->getToken() . "/$id";
        file_put_contents('../images/image.jpg', file_get_contents($url));
        echo "done";
    }
}
