PHP MySQL Session Handler
========================

Installation
----------------------------

First you need to create a table in your database:

    CREATE TABLE `session_handler_table` (
    `id` varchar(255) NOT NULL,
    `data` mediumtext NOT NULL,
    `timestamp` int(255) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


Then have a look at example.php
Easy!