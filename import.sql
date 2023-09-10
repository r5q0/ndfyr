DROP DATABASE IF EXISTS `telegrambotCR`;
create database `telegrambotCR`;
use `telegrambotCR`;
create table if not exists `users` (
  `id` int(11) not null auto_increment,
  `username` varchar(255) not null,
  `tokens` int(11) default 3,
  `ip` varchar(255),
  `tempkey` varchar(255),
  `telegram_id` varchar(255) not null unique,
  primary key (`id`)
);
create table if not exists `messages` (
    `id` int(11) not null auto_increment,
  `telegram_id` varchar(255) not null,
  `message` text not null,
    `date` datetime not null default current_timestamp,
    primary key (`id`)
);
create table if not exists `webvisits` (
    `id` int(11) not null auto_increment,
    `telegram_id` varchar(255) not null,
    `ip` varchar(255) not null,
    `date` datetime not null default current_timestamp,
    primary key (`id`)
);