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
use Controllers\PaymentController;

class ReturnController
{

    public static function USD($bot)
    {
        $bot->editMessageText(
            text: 'How many tokens do you want to buy?',
            reply_markup: InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make('20 Tokens (3$)', url: PaymentController::buy(20, 'USD', $bot)),
                InlineKeyboardButton::make('40 Tokens (5$)', url: PaymentController::buy(40, 'USD', $bot))
            )->addRow(
                InlineKeyboardButton::make('100 Tokens (10$)', url: PaymentController::buy(100, 'USD', $bot)),
                InlineKeyboardButton::make('500 Tokens (25$)', url: PaymentController::buy(500, 'USD', $bot))
            )->addRow(
                InlineKeyboardButton::make('Back', callback_data: '/start'),
            )
        );
    }

    public static function GBP($bot)
    {
        $bot->editMessageText(
            text: 'How many tokens do you want to buy?',
            reply_markup: InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make('20 Tokens (3$)', url: PaymentController::buy(20, 'GBP', $bot)),
                InlineKeyboardButton::make('40 Tokens (5$)', url: PaymentController::buy(40, 'GBP', $bot))
            )->addRow(
                InlineKeyboardButton::make('100 Tokens (10$)', url: PaymentController::buy(100, 'GBP', $bot)),
                InlineKeyboardButton::make('500 Tokens (25$)', url: PaymentController::buy(500, 'GBP', $bot))
            )
                ->addRow(
                    InlineKeyboardButton::make('Back', callback_data: '/start'),
                )
        );
    }


    public static function startMessageEdit($bot)
    {
        $bot->editMessageText(
            text: "***Welcome to the NDFY Bot*** 🤖\n" .
                "***1 image = 1 token***\n" .
                "This bot will help you to remove the clothes from any image.\n" .
                "To get started, send an image to the bot.\n" .
                "If you have any questions, please contact @antitrust0\n",
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('My Account', callback_data: '/me'),
                    InlineKeyboardButton::make('Support', url: 't.me/antitrust0')
                )->addRow(
                    InlineKeyboardButton::make('Buy', callback_data: '/buy')
                )
        );
    }

    public static function start($bot)
    {
        $userId = $bot->userId();
        $userExist = DataBaseController::UserExists($userId);
        $username = $bot->user()->username;
        $language_code = $bot->user()->language_code;
        if ($userExist == false) {
            DataBaseController::InsertUser($username, $userId, $language_code);
        }

        $bot->sendMessage(
            text: "***Welcome to the NDFY Bot*** 🤖\n" .
                "***1 image = 1 token***\n" .
                "This bot will help you to remove the clothes from any image.\n" .
                "To get started, send an image to the bot.\n" .
                "If you have any questions, please contact @antitrust0\n",
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('My Account', callback_data: '/me'),
                    InlineKeyboardButton::make('Support', url: 't.me/antitrust0')
                )->addRow(
                    InlineKeyboardButton::make('Buy', callback_data: '/buy')
                )
        );
    }


    public static function me($bot)
    {
        $id = $bot->userId();
        $username = $bot->user()->username;
        $tokens = DataBaseController::GetTokens($id);
        $bot->editMessageText(
            text: "Your ID: `$id`\n" .
                "Your Username: `@$username`\n " .
                "Your Tokens: `$tokens`\n ",
            parse_mode: 'MarkdownV2',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('Back', callback_data: '/start'),
                )
        );
    }
    public static function Buy($bot)
    {
        $bot->editMessageText(
            "🌍 `Choose your currency` 🌍\n" .
                "💳 If you cannot pay with a credit card or if your currency is not listed, or if you prefer to pay with cryptocurrency, please contact @antitrust0 for alternative payment options.\n",
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('USD', callback_data: 'USD'),
                    InlineKeyboardButton::make('GBP', callback_data: 'GBP'),
                )->addRow(
                    InlineKeyboardButton::make('Back', callback_data: '/start'),
                )

        );
    }
}
