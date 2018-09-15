<?php

/*******************
*                  *
*     include      *
*                  *
********************/
require_once(__DIR__ . "/usefull_functions.php");
require_once(__DIR__ . "/secrets.php");

/*******************
*                  *
*   PHP debugging  *
*                  *
********************/
ob_start();
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/*******************
*                  *
*      TIME        *
*                  *
********************/
date_default_timezone_set('Europe/Berlin');

/*******************
*                  *
* Global constants *
*                  *
********************/
//depending on server
const DATABASE_PATH_RAW = "/var/www/EggsChange/EggsChange.db";
const DATABASE_PATH = "sqlite:" . DATABASE_PATH_RAW;
const ABSOLUTE_DATABASE_PATH = DATABASE_PATH; //handle all absolut for simplicity

//configs
const NEEDED_JUDGES = 10;
const NEEDED_VOTE_DIFF = 5;
const NEEDED_REP_LVL_FOR_JUDGING = 1;
const MAX_INVITE_REWARD_STATE = 4;
const IS_ALLOWED_INVITES = true;
const IS_STYLE_TABLE = false;

?>
