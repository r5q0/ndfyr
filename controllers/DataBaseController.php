<?php

namespace Controllers;

require_once '/home/server/pr/ndfyr/vendor/autoload.php';

use RedBeanPHP\R;

R::setup('mysql:host=localhost;dbname=ndfyr', 'root', '');
class DataBaseController
{

    public static function UserExists($telegram)
    {
        $user = R::findOne('users', 'telegram = ?', [$telegram]);
        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    public static function AddTokens($id, $amount)
    {
        $user = R::findOne('users', 'telegram = ?', [$id]);
        $user->tokens = $user->tokens + $amount;
        R::store($user);
    }

    public static function InsertMessage($telegram, $logg)
    {
        $message = R::dispense('messages');
        $message->telegram = $telegram;
        $message->message = $logg;
        R::store($message);
    }


    public static function InsertUser($username, $telegram, $language_code)
    {
        $user = R::dispense('users');
        $user->username = $username;
        $user->telegram = $telegram;
        $user->language = $language_code;
        R::store($user);
    }
    public static function GetTokens($telegram)
    {
        $user = R::findOne('users', 'telegram = ?', [$telegram]);
        return $user->tokens;
    }
    public static function randTemp($id)
    {
        $user = R::findOne('users', 'telegram = ?', [$id]);
        $user->tempkey = rand(10000000000000000, 99999000000000000);
        R::store($user);
        return $user->tempkey;
    }

    public static function ifUserExist($id)
    {
        $user = R::findOne('users', 'telegram = ?', [$id]);
        if ($user) {
            return true;
        } else {
            return false;
        }
    }
    public static function setAffiliateTrue($id, $affiliate)
    {
        if (self::UserExists($id) == false && self::UserExists($affiliate) == true) {
            $username = CommandsController::$bot->user()->username;
            $language_code = CommandsController::$bot->user($id)->language_code;
            DataBaseController::InsertUser($username, $id, $language_code);
            $user = R::findOne('users', 'telegram = ?', [$id]);
            $user->affiliate = true;
            R::store($user);

            $user2 = R::findOne('users', 'telegram = ?', [$affiliate]);
            $user2->tokens = $user2->tokens + 1;
            R::store($user2);
        }
    }
    public static function setPremium($id){
        $user = R::findOne('users', 'telegram = ?', [$id]);
        $user->premium = true;
        R::store($user);
    }
    public static function remTokens($id, $amount)
    {
        $user = R::findOne('users', 'telegram = ?', [$id]);
        $user->tokens = $user->tokens - $amount;
        R::store($user);
    }

    public static function getAffiliate($id)
    {
        $user = R::findOne('users', 'telegram = ?', [$id]);
        return $user->username;
    }
    public static function addAffiliate($id){
        $user = R::findOne('users', 'telegram = ?', [$id]);
        $user->affiliatecount = $user->affiliatecount + 1;
        R::store($user);
    }
    
}