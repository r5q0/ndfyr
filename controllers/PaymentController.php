<?php

namespace Controllers;

include_once 'vendor/autoload.php';

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

class PaymentController
{
    
    public static function buy($quantity, $currency, $bot)
    {

        if ($currency == 'USD') {
            $USD = ($quantity == 20) ? 3 : (($quantity == 40) ? 5 : (($quantity == 100) ? 10 : (($quantity == 500) ? 25 : 100000)));
        }
        if ($currency == 'GBP') {
            $USD = ($quantity == 20) ? 2.36 : (($quantity == 40) ? 3.94 : (($quantity == 100) ? 7.88 : (($quantity == 500) ? 19.7 : 100000)));
        }

        $labeledPrices = [
            ['label' => "$quantity Tokens", 'amount' => $USD * 100],
        ];

        $link = $bot->createInvoiceLink("$quantity Tokens", 'Tokens', $quantity, '350862534:LIVE:ODljMzc4OGMwZWVk', "$currency", $labeledPrices);
        return $link;
    }

  
    public static function PaymentListener($bot)
    {

        $bot->onSuccessfulPaymentPayload('20', function (Nutgram $bot) {
            DataBaseController::AddTokens($bot->userId(), 20);
            DataBaseController::setPremium($bot->userId());
            $bot->sendMessage('You have successfully bought 20 tokens');
        });

        $bot->onSuccessfulPaymentPayload('40', function (Nutgram $bot) {
            DataBaseController::AddTokens($bot->userId(), 40);
            DataBaseController::setPremium($bot->userId());
            $bot->sendMessage('You have successfully bought 40 tokens');
        });

        $bot->onSuccessfulPaymentPayload('100', function (Nutgram $bot) {
            DataBaseController::AddTokens($bot->userId(), 100);
            DataBaseController::setPremium($bot->userId());
            $bot->sendMessage('You have successfully bought 100 tokens');
        });

        $bot->onSuccessfulPaymentPayload('500', function (Nutgram $bot) {
            DataBaseController::AddTokens($bot->userId(), 500);
            DataBaseController::setPremium($bot->userId());
            $bot->sendMessage('You have successfully bought 500 tokens');
        });
    }
}