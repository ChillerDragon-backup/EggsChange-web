<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("wish");

if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
{
echo "<a>you have to be logged in.</a>";
echo "<input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/>";
fok();
}

$db = new PDO(DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
$stmt->execute(array($_SESSION['Username']));

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
    //SQL checks
    $state = $rows[0]['STATE'];
    $missionID = $rows[0]['missionID'];
    if ($state == 1) //currently on mission --> send the user to mission state
    {
        header("Location: complete.php?id=$missionID");
    }
}

$wish_id = 0; //sql ids start with 1 so it doesnt show shit here 
if (!empty($_GET['id']))
{
$wish_id = $_GET['id'];
}


$db = new PDO(DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

$stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
$stmt->execute(array($wish_id));

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
	$wisher = $rows[0]['wisher'];
    $title = $rows[0]['wish_name'];
	$reward = $rows[0]['wish_reward'];
    $description = $rows[0]['wish_desc'];
	echo "<h1>$title</h1>";
				
	if ($wisher == $_SESSION['Username'])
	{
		if (!empty($_GET['delete']) && $_GET['delete'] == "true")
		{
			echo "<font color=\"red\"><a>Do you really want to delete your wish?</br>It can't be fullfilled anymore and you get your points back.</a><br></font>";
			echo "<form class=\"form\"><input type=\"button\" value=\"delete\" onclick=\"window.location.href='delete_wish.php?id=$wish_id'\" /></form>";
		}
		else
		{
			echo "<a><strong>Points:</strong> $reward</a><br>";
			echo "<a><strong>Description:</strong></br>$description</br></br></a>";
			echo "<form class=\"form\"><input type=\"button\" value=\"delete\" onclick=\"window.location.href='main_wish.php?id=$wish_id&delete=true'\" /></form>";
		}
	}
	else
	{
		echo "<a><strong>Points:</strong> $reward</a><br>";
		echo "<a><strong>Description:</strong></br>$description</br></br></a>";
		echo "<form class=\"form\"><input type=\"button\" value=\"fullfill\" onclick=\"window.location.href='complete.php?id=$wish_id'\" /></form>";
	}			
}
else
{
	echo "<a>This wish doesnt exsist</a><br>";
}
?>
	<form class="form">
		<input type="button" value="Back" onclick="window.location.href='fullfillwish.php'" />
	</form>
	</body>
</html>
