<?php

header('Content-Type: application/json; Charset=UTF-8');
date_default_timezone_set('Europe/Istanbul');

define('MYSQL_HOST', 'localhost');
define('MYSQL_DB', 'bookstore');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('API_KEY', '8f9c9cbdb0c57a4bc4d630a5297ac180830c348e899c9b5a22091460c4f19527'); // API Key sabiti
include 'db.php';
