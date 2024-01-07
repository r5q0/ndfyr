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
use Controllers\PlisioClient;
class PaymentController
{
    private $plisio;
    private $secretKey = '7k8NWTbkxr1nJ9JSJWDhFr-ClKSM_wbHYrO4P34TbW8bcpZhDb52cWljoGtKQ5Sk'; 

    public function plisioClientSetup() 
    {

        $this->plisio = new PlisioClient('7k8NWTbkxr1nJ9JSJWDhFr-ClKSM_wbHYrO4P34TbW8bcpZhDb52cWljoGtKQ5Sk');
    }


    public function createInvoice($userId, $quantity)
    {
        $this->plisioClientSetup(); //Initialize Plisio instance.

        
        $USD = ($quantity == 20) ? 3 : (($quantity == 40) ? 5 : (($quantity == 100) ? 10 : (($quantity == 500) ? 25 : 1000)));

    
        $rand = rand(0, 99999999);
        $json = json_encode(['userId' => $userId, 'random' => "$rand", 'quantity' => $quantity]);
        $invoiceData = array (
            'order_number' => $json, //Order number should be uniq for each order.
            'order_name' => "tokens",
            'source_amount' => $USD, //Order amount in source currency.
            'source_currency' => "USD", //For example: 'USD'.
            'cancel_url' => 'https://t.me/ndfyr_bot', //Url to which Plisio will redirect customer in a case of a cancelled order.
            'callback_url' => 'https://imagefy.xyz/api/telegram', //Url to which Plisio will send order related info about status changes.
            'success_url' => 'https://t.me/ndfyr_bot', //Url to which Plisio will redirect customer in a case of successful order.
            'email' => "gpushop@proton.me", //Customer email. If not specified - customer will be prompted to enter email on Plisio side.
            'plugin' => 'ndfyrimgfyr', //Specify uniq name related to your shop, so it'll be easier to track issues with your invoices if any occurs.
            'version' => 'v1' //Specify plugin version.
        );
        
           $response = $this->plisio->createTransaction($invoiceData);
           $invoiceUrl = $response['data']['invoice_url'];
           return $invoiceUrl;
        }






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