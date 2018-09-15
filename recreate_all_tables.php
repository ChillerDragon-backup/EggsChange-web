<?php

require_once(__DIR__ . "/users_table.php");
require_once(__DIR__ . "/wishes_table.php");
require_once(__DIR__ . "/conflicts_table.php");
require_once(__DIR__ . "/points_table.php");
require_once(__DIR__ . "/login_table.php");
require_once(__DIR__ . "/invites_table.php");

echo "Users: </br>";
CreateTableUsers();
echo "Wishes: </br>";
CreateTableWishes();
echo "Conflicts: </br>";
CreateTableConflicts();
echo "Points: </br>";
CreateTablePoints();
echo "Login: </br>";
CreateTableLogin();
echo "Invites: </br>";
CreateTableInvites();

/*
echo "Users:</br>";
include 'users_table.php';
echo "Wishes:</br>";
include 'wishes_table.php';
echo "Conflicts:</br>";
include 'conflicts_table.php';
echo "Points:</br>";
include 'points_table.php';
*/



?>
