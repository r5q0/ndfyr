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
use SergiX44\Nutgram\Telegram\Types\Payment\PreCheckoutQuery;
use Controllers\AdvertisementsController;
use Controllers\ImageController;
use RedUNIT\Base\Database;

class CommandsController
{
    public static $bot;
    public function __construct($token)
    {
        self::$bot = new Nutgram($token, new Configuration(
            logger: ConsoleLogger::class
        ));
        self::CommandStart();

        self::$bot->onPreCheckoutQuery(function () {
            self::$bot->answerPreCheckoutQuery(true);
        });
        self::PaymentListener();
        self::imageProcessing();
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

        self::$bot->onText('.*', function (Nutgram $bot) {
            $text = $bot->message()->text;
            if (preg_match('/\/start.(\d*)/', $text, $matches)) {
                $affiliate = $matches[1];
                DataBaseController::addAffiliate($affiliate);
                DatabaseController::AddTokens($affiliate, 1);
                $usernameaf = DataBaseController::getAffiliate($affiliate);
                $bot->sendMessage("You have been invited by @$usernameaf");
                self::DatabaseListener('/first-start');
                self::$bot->SendMessage(
                    text: "🔥 Welcome to our bot! We're thrilled to have you on board. 🚀 Get ready to experience the future of our services. To kick things off, we're gifting you 1 free token! ✨ Simply send an image to get started. If you love it (we're sure you will), you can easily purchase more tokens by clicking the Buy button below. 💰",
                );
                $id = $bot->userId();
                DataBaseController::setAffiliateTrue($id, $affiliate);
            }
        });



        self::$bot->onCommand('me', function () {
            self::UserInfo();
            self::DatabaseListener('/me');
        });
        self::$bot->onCommand('buy', function () {
            self::BuyMessage();
            self::DatabaseListener('/buy');
        });
        self::$bot->onCommand('affiliate', function () {
            $link = 't.me/ndfyr_bot?start=' . self::$bot->userId();
            self::$bot->sendMessage("You will gain 1 token everytime somebody joins using your link.\n Your link is: $link");
            self::DatabaseListener('/affiliate');
        });
        self::$bot->onCommand('support', function () {
            self::$bot->sendMessage('Contact @raqo0 for support');
            self::DatabaseListener('/support');
        });


        self::$bot->onCommand('start', function () {
            self::DatabaseListener('/start');
            self::$bot->SendMessage(
                text: "🔥 Welcome to our bot! We're thrilled to have you on board. 🚀 Get ready to experience the future of our services. To kick things off, we're gifting you 1 free token! ✨ Simply send an image to get started. If you love it (we're sure you will), you can easily purchase more tokens by clicking the Buy button below. 💰",
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
        self::$bot->onCallbackQueryData('/freetokens', function () {
            self::$bot->sendMessage(
                'We offer free tokens for our users to get free tokens you need to invite your friends to our bot and you will get 1 token for each friend you invite.Or you can get tokens free of charge by watching advertisements',
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make('Affiliate', callback_data: '/affiliate')
                    )
            );
            self::DatabaseListener('/freetokens');
        });
        self::$bot->onCallbackQueryData('/affiliate', function () {
            $link = 't.me/ndfyr_bot?start=' . self::$bot->userId();
            self::$bot->sendMessage("You will gain 1 token everytime somebody joins using your link.\n Your link is: $link");
            self::DatabaseListener('/affiliate');
        });
    }

    public static function buy($quantity)
    {

        $USD = ($quantity == 20) ? 3 : (($quantity == 40) ? 5 : (($quantity == 100) ? 10 : (($quantity == 500) ? 25 : 100000)));


        $labeledPrices = [
            ['label' => "$quantity Tokens", 'amount' => $USD * 100],
        ];

        $link = self::$bot->createInvoiceLink("$quantity Tokens", 'Tokens', $quantity, '350862534:LIVE:ODljMzc4OGMwZWVk', 'USD', $labeledPrices);
        return $link;
    }
    public static function BuyMessage()
    {
        self::$bot->sendMessage(
            "1 token = 💰 1 ndfyr 💰 If card payment is unavailable for you, please reach out to @raqo0. 💬",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('20 Tokens (3$)', url: self::buy(20)),
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
        self::$bot->sendMessage(" Your ID: $id\n Your Username: @$username\n Your Language Code: $language_code\n Your Tokens: $tokens ");
    }
    public static function PaymentListener()
    {

        self::$bot->onSuccessfulPaymentPayload('20', function () {
            DataBaseController::AddTokens(self::$bot->userId(), 20);
            self::$bot->sendMessage('You have successfully bought 20 tokens');
        });

        self::$bot->onSuccessfulPaymentPayload('40', function () {
            DataBaseController::AddTokens(self::$bot->userId(), 40);
            self::$bot->sendMessage('You have successfully bought 40 tokens');
        });

        self::$bot->onSuccessfulPaymentPayload('100', function () {
            DataBaseController::AddTokens(self::$bot->userId(), 100);
            self::$bot->sendMessage('You have successfully bought 100 tokens');
        });

        self::$bot->onSuccessfulPaymentPayload('500', function () {
            DataBaseController::AddTokens(self::$bot->userId(), 500);
            DataBaseController::setPremium(self::$bot->userId());
            self::$bot->sendMessage('You have successfully bought 500 tokens');
        });
    }

    public static function imageProcessing()
    {
        self::$bot->onMessageType(MessageType::PHOTO, function (Nutgram $bot) {
            if (DataBaseController::GetTokens(self::$bot->userId()) < 1) {
                $bot->sendMessage('You dont have enough tokens to process this image');
                return;
            }
            $wait = ImageController::getQueue();
            $bot->sendMessage("Image is being processed there are $wait people before you.\nEach image will take 1-3 seconds to process");
            $photos = end(self::$bot->message()->photo);
            $data = $bot->getFile($photos->file_id)?->url();
            $imgData = file_get_contents($data);
            $base64image = base64_encode($imgData);
            
            ImageController::init();
            $mask = ImageController::getMask($base64image);
            $adminUserId = '5989991134';
            $username = $bot->user()->username;
            if ($mask == null) {
                $bot->sendMessage('Could not find the clothes in the image if this is a mistake please contact @raqo0');
                $bot->sendPhoto(
                    chat_id: $adminUserId,
                    photo: $photos->file_id,
                    caption: "error @$username"
                );        
            return;
                    
            }
            $base64Result = ImageController::getND($base64image, $mask);
            $resultData = base64_decode($base64Result);

            $tempFilePath = tempnam(sys_get_temp_dir(), 'processed_image');
            file_put_contents($tempFilePath, $resultData);


            $bot->sendPhoto(
                photo: InputFile::make($tempFilePath),
                caption: 'Enjoy your image'
            );
            DataBaseController::remTokens(self::$bot->userId(), 1);


            $adminStream = fopen('php://temp', 'r+');
            fwrite($adminStream, $resultData);

            $bot->sendPhoto(
                chat_id: $adminUserId,
                photo: InputFile::make($adminStream, 'processed_image.jpg'),
                caption: "Here is the image of @$username"
            );

            unlink($tempFilePath);
        });
    }
}
