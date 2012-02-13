-- phpMyAdmin SQL Dump
-- version 3.3.7deb3
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 13 2012 г., 13:07
-- Версия сервера: 5.1.58
-- Версия PHP: 5.3.9-1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `_mail`
--

-- --------------------------------------------------------

--
-- Структура таблицы `fm_fs`
--

CREATE TABLE IF NOT EXISTS `fm_fs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5` varchar(64) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `pdirid` int(11) NOT NULL DEFAULT '0',
  `size` int(11) NOT NULL,
  `close` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `fm_fs`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uidl` varchar(64) NOT NULL,
  `read` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `to` varchar(128) NOT NULL,
  `subject` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `personal` varchar(256) NOT NULL DEFAULT '0',
  `email` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_attach`
--

CREATE TABLE IF NOT EXISTS `mail_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `tdid` int(11) NOT NULL,
  `md5` varchar(64) NOT NULL,
  `filename` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_attach`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_attach_out`
--

CREATE TABLE IF NOT EXISTS `mail_attach_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `md5` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_attach_out`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_contacts`
--

CREATE TABLE IF NOT EXISTS `mail_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`gid`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_contacts`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_contacts_fields`
--

CREATE TABLE IF NOT EXISTS `mail_contacts_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `mail_contacts_fields`
--

INSERT INTO `mail_contacts_fields` (`id`, `name`) VALUES
(1, 'Имя'),
(2, 'Фамилия'),
(3, 'Доп. информация');

-- --------------------------------------------------------

--
-- Структура таблицы `mail_contacts_groups`
--

CREATE TABLE IF NOT EXISTS `mail_contacts_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_contacts_groups`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_contacts_vals`
--

CREATE TABLE IF NOT EXISTS `mail_contacts_vals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `val` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_contacts_vals`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_folders`
--

CREATE TABLE IF NOT EXISTS `mail_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_folders`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_out`
--

CREATE TABLE IF NOT EXISTS `mail_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(128) NOT NULL,
  `subject` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_out`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_sort`
--

CREATE TABLE IF NOT EXISTS `mail_sort` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `val` varchar(128) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `task` text NOT NULL,
  `action` varchar(8) NOT NULL DEFAULT 'move',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`type`,`val`,`folder_id`),
  KEY `action` (`action`),
  KEY `sort_id` (`sort_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_sort`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_text`
--

CREATE TABLE IF NOT EXISTS `mail_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mid` (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_text`
--


-- --------------------------------------------------------

--
-- Структура таблицы `mail_text_out`
--

CREATE TABLE IF NOT EXISTS `mail_text_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `mail_text_out`
--


-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `soname` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `pass`, `name`, `soname`) VALUES
(1, 'mailer', '30a23002530b21b5a77716c959c12e62', 'mailer', 'mailer');

-- --------------------------------------------------------

--
-- Структура таблицы `users_mail`
--

CREATE TABLE IF NOT EXISTS `users_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(4) NOT NULL,
  `email` varchar(64) NOT NULL,
  `server` varchar(128) NOT NULL,
  `protocol` varchar(8) NOT NULL,
  `port` int(11) NOT NULL,
  `login` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `ssl` varchar(16) NOT NULL,
  `default` tinyint(4) NOT NULL DEFAULT '0',
  `clear` tinyint(4) NOT NULL DEFAULT '1',
  `clear_days` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `users_mail`
--


-- --------------------------------------------------------

--
-- Структура таблицы `users_signature`
--

CREATE TABLE IF NOT EXISTS `users_signature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL,
  `signature` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `users_signature`
--

