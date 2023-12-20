<?php

namespace Controllers;

require_once 'vendor/autoload.php';

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

    public static function ifUserExist($id)
    {
        $user = R::findOne('users', 'telegram = ?', [$id]);
        if ($user) {
            return true;
        } else {
            return false;
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
 
    public static function getData(){
        $users = R::findAll('users');
        return $users;
    }


}