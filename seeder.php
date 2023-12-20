    <?php
    require_once '/home/server/pr/ndfyr/vendor/autoload.php';

    use RedBeanPHP\R;
    // Establish a database connection
    R::setup('mysql:host=localhost;dbname=ndfyr', 'root', '');
    R::exec('CREATE DATABASE IF NOT EXISTS `ndfyr`');
    R::freeze(true);
    R::exec('DROP TABLE IF EXISTS `users`');
    R::exec('DROP TABLE IF EXISTS `messages`');
    R::exec('CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(255) NOT NULL,
        `tokens` Float DEFAULT 1,
        `language` VARCHAR(255),
        `telegram` varchar(255) NOT NULL UNIQUE,
        `affiliate` boolean DEFAULT false,
        `affiliatecount` INT DEFAULT 0,
        dateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    R::exec('CREATE TABLE IF NOT EXISTS `messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `telegram` INT(255) NOT NULL , 
        `message` TEXT NOT NULL,
        `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    ?>
