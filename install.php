<?php
//cpanel-accts table
$sql = 'CREATE TABLE `cpanel-accts` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `cpanel_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `cpanel_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `cpanel_host` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `cpanel_domain` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `cpanel_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `root` int(11) NOT NULL,
 `cron_key` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

//cpanel-tokens table
$sql1 = 'CREATE TABLE `cpanel-tokens` (
 `cpid` int(11) NOT NULL,
 `token` text COLLATE utf8_unicode_ci NOT NULL,
 `expires` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

//cpanel-ftp table
$sql2 = 'CREATE TABLE `cpanel-ftp` (
 `cpid` int(11) NOT NULL,
 `ftp_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `ftp_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `path` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

//create
$sql3 = 'CREATE TABLE `cpanel-dbusers` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `cpid` int(11) NOT NULL,
 `db` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `dbuser` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 `dbpass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';