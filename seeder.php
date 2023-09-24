<?php
require_once 'vendor/autoload.php';
use RedBeanPHP\R;
// Establish a database connection
R::setup('mysql:host=localhost;dbname=ndfyr', 'root', '');
R::exec('CREATE DATABASE IF NOT EXISTS `ndfyr`');
R::freeze(true);
R::exec('CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `tokens` Float DEFAULT 1,
    `ip` VARCHAR(255),
    `language` VARCHAR(255),
    `telegram` varchar(255) NOT NULL UNIQUE,
    `tempkey` varchar(255) UNIQUE,
    `premium` boolean DEFAULT false,
    dateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

R::exec('CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `telegram` INT(255) NOT NULL , /* Change data type to INT */
    `message` TEXT NOT NULL,
    `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

R::exec('CREATE TABLE IF NOT EXISTS `adVisits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `telegram` INT(255) NOT NULL, /* Change data type to INT */
    `Linkversite` INT(5) DEFAULT 0,
    `AdFoc` INT(5) DEFAULT 0,
    `Flight` INT(5) DEFAULT 0
)');

// Create 'webvisits' table
R::exec('CREATE TABLE IF NOT EXISTS `webvisits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `telegram` INT(255) NOT NULL, /* Change data type to INT */
    `ip` VARCHAR(255) NOT NULL,
    `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

)');
?>
