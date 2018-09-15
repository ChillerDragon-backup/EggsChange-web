<?php
session_start();
require_once(__DIR__ . "/global.php");

if ($_SESSION['IsLogged'] != "online")
{
    echo "<a>you have to be logged in.</a></br>";
    echo "<form><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
    die();
}

CheckAccountState($_SESSION['Username']);

SendPoints($_SESSION['Username'], "SERVER", 999, "cheated");
?>

<html>
	<head>
		<title>Eggs Change cheat points</title>
		<link rel="stylesheet" href="login.css" type="text/css"></link>
	</head>
	<body>
		<h1>CHEAT</h1>
		<form class="form">
		<input type="button" value="Back" onclick="window.location.href='index.php'" />
		</form>
	</body>
</html>
