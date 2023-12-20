<?php

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
use Controllers\AdminController;
use Controllers\ReturnController;

$config = new Configuration(
    pollingTimeout: 100,
    logger: ConsoleLogger::class
);

$bot = new Nutgram('6944027645:AAEoYuEPT2C3m0sbdJ7CHSTBhpJb2XY09No', $config);




$bot->oncallbackquerydata('USD', function (Nutgram $bot) {
    ReturnController::USD($bot);
});
$bot->oncallbackquerydata('GBP', function (Nutgram $bot) {
    ReturnController::GBP($bot);
});
$bot->onCallbackQueryData('/start', function (Nutgram $bot) {
    ReturnController::startMessageEdit($bot);
});

$bot->onCallbackQueryData('/buy', function (Nutgram $bot) {
    ReturnController::Buy($bot);
});
$bot->onCallbackQueryData('/me', function (Nutgram $bot) {
    ReturnController::me($bot);
});
$bot->onCommand('start', function (Nutgram $bot) {
    ReturnController::start($bot);
});


$bot->onCommand('send {text}', function (Nutgram $bot, $text) {
    AdminController::send($bot, $text);
});

$bot->onCommand('give {name} {amount}', function (Nutgram $bot, $name, $amount) {
    AdminController::give($name, $amount, $bot);
});

$bot->onCommand('stats', function (Nutgram $bot) {
    AdminController::stats($bot);
});

$bot->onMessageType(MessageType::PHOTO, function (Nutgram $bot) {
    if (DataBaseController::GetTokens($bot->userId()) < 1) {
        $bot->sendMessage('You dont have enough tokens to process this image');
        return;
    }

    $admins = ['5330922158', '5989991134'];
    $support = '6915367476';


    $wait = ImageController::getQueue();
    $bot->sendMessage("Image is being processed there are $wait people before you.\nEach image will take 1-3 seconds to process");
    $photo = end($bot->message()->photo);
    $data = $bot->getFile($photo->file_id)?->url();
    $baseImage = base64_encode(file_get_contents($data));

    $mask = ImageController::getMask($baseImage);
    $username = $bot->user()->username;
    if ($mask == null) {
        $bot->sendMessage('Could not find the clothes in the image if this is a mistake please contact @antitrust2');
        $bot->sendPhoto(
            chat_id: $support,
            photo: $photo->file_id,
            caption: "could not find clothes @$username"
        );
        return;
    }
    $bot->sendPhoto(
        chat_id: $support,
        photo: $photo->file_id,
        caption: "base image @$username"
    );
    $Nude = base64_decode(ImageController::getND($baseImage, $mask));
    $decodedMask = base64_decode($mask);
    $NudePath = tempnam(sys_get_temp_dir(), 'nude');
    file_put_contents($NudePath, $Nude);
    $MaskPath = tempnam(sys_get_temp_dir(), 'mask');
    file_put_contents($MaskPath, $decodedMask);
    $pid = pcntl_fork();
    if ($pid == -1) {
        $bot->sendMessage('Error processing image. Please try again.');
    } elseif ($pid) {
        $bot->sendMessage('Image processing started. You will be notified when it\'s done.');
    }
    else {
    if (in_array($bot->userId(), $admins)) {
        
        $WaterMarkedImage = base64_decode(addWatermarkToImage($baseImage, 't.me/ndfyr_bot'));
        $watermarkedPath = tempnam(sys_get_temp_dir(), 'watermarked');
        file_put_contents($watermarkedPath, $WaterMarkedImage);
        $bot->sendPhoto(
            photo: InputFile::make($MaskPath),
            caption: 'Enjoy your image'
        );
        $bot->sendPhoto(
            photo: InputFile::make($watermarkedPath),
            caption: 'Enjoy your image'
        );
        unlink($watermarkedPath);
        unlink($NudePath);
        unlink($MaskPath);
        return;
    }
    DataBaseController::remTokens($bot->userId(), 1);
    $bot->sendPhoto(
        photo: InputFile::make($NudePath),
        caption: 'Enjoy your image'
    );

    $bot->sendPhoto(
        chat_id: $support,
        photo: InputFile::make($MaskPath),
        caption: "Here is the image of @$username"
    );
    $bot->sendPhoto(
        chat_id: $support,
        photo: InputFile::make($NudePath),
        caption: "Here is the image of @$username"
    );


    unlink($NudePath);
    unlink($MaskPath);
    posix_kill(getmypid(), SIGCHLD);
    exit();


}
});

function addWatermarkToImage($base64Image, $watermarkText)
{
    $imageData = base64_decode($base64Image);
    $image = imagecreatefromstring($imageData);

    $watermarkColor = imagecolorallocate($image, 255, 165, 0); // Orange color

    // Calculate font size based on image size
    $imageWidth = imagesx($image);
    $font = max(ceil($imageWidth / 20), 10); // Adjust the divisor and minimum font size as needed

    // Calculate watermark position based on image size
    $x = max(ceil($imageWidth / 50), 10); // Adjust the divisor and minimum X-coordinate as needed
    $y = max(ceil($imageWidth / 50), 10); // Adjust the divisor and minimum Y-coordinate as needed

    // Add text watermark to the image
    imagestring($image, $font, $x, $y, $watermarkText, $watermarkColor);

    // Save the watermarked image to a new base64 string
    ob_start();
    imagepng($image);
    $watermarkedBase64 = base64_encode(ob_get_clean());

    imagedestroy($image);

    return $watermarkedBase64;

}


$bot->run();
