<?php
namespace Controllers;
include_once '../vendor\autoload.php';
use Controllers\CommandsController;
use Controllers\AffiliateController;
use Controllers\DataBaseController;

class AdvertisementsController{

public static function getLinkClicksFly($id){
$tempkey = DataBaseController::randTemp($id);
$raw = file_get_contents("https://clicksfly.com/api?api=245362e1a50e0e07a022f4d9dbf58f30f642be80&url=localhost.com/index.php?id=$id?tempkey=$tempkey");
$clean = json_decode($raw, true);
return $clean['shortenedUrl'];
}
}