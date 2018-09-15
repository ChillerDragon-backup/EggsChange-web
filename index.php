<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("home", IsLoggedIn()); //only print menu when logged in

if (IsLoggedIn() == false)
{
    echo "<h1> EggsChange </h1>";
    echo "<a>you have to be logged in.</a><br>";
    echo "<form class=\"form\"><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
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
        
        //all good print main
        $name = $rows[0]['Username'];
		$points = $rows[0]['Points'];
		echo "<strong>$name</strong><br>";
		echo "<a><abbr title=\"Collect points due fullfilling wishes and use them to wish your own.\">points: <a href=\"TransactionHistory.php\">$points</a></a></abbr></br>";
	}
	else
	{
		echo "<a>Invalid account</a></br>";
		echo "<form class=\"form\"><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
		fok();
	}


$rep_lvl = GetReputationLevel($_SESSION['Username']);
echo "<a><abbr title=\"Collect positive reputation due accepting wishes you wished.\">Reputation level: $rep_lvl</abbr></br></a>";
if ($rep_lvl > 0)
{
    echo "<a></br><strong>Judge Conflicts</strong></br>Judge conflicts to earn reputation and points if you made the correct decision.</br>Help to make EggsChange a trusty place.</br></a>";
    echo "<input type=\"button\" value=\"Judge Conflicts\" onclick=\"window.location.href='clean_conflicts.php'\"/>";
}

$db = new PDO(DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$name = $_SESSION['Username'];
$stmt = $rows = $db->query('SELECT * FROM Wishes WHERE wish_STATE = 1 AND wisher = ? ORDER BY wish_reward DESC LIMIT 20');
$stmt->execute(array($name));
$db = null;
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
	echo "<br><strong>pending confirms: </strong><br>";
    foreach ($rows as $row)
    {
        if (!empty($row['wish_name']))
        {
			$title = $row['wish_name'];
			$reward = $row['wish_reward'];
			$id = $row['ID'];
            echo "<a href=\"confirm.php?id=$id\">[$reward] $title </a></br>";
		}
    }
}
else
{
    //echo "</br><a>no pending confirms</a>";
}


$db = new PDO(DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$name = $_SESSION['Username'];
$stmt = $rows = $db->query('SELECT * FROM Wishes WHERE wish_STATE = 1 AND wish_fullfiller = ? ORDER BY wish_reward DESC LIMIT 20');
$stmt->execute(array($name));
$db = null;
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
	echo "<br><strong>pending rewards: </strong><br>";
    foreach ($rows as $row)
    {
        if (!empty($row['wish_name']))
        {
			$title = $row['wish_name'];
			$reward = $row['wish_reward'];
			$id = $row['ID'];
			echo "<a>[$reward] $title </a><br>";
        }
    }
}
else
{
	//echo "</br><a>no pending rewards</a>";
}

fok();
?>

