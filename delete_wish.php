<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("delete");

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

function print_html_main($message)
{
	echo
	"
			<h2> Delete wish </h2>
			<a>$message</a>
			<form>
				<input type=\"button\" value=\"Okay\" onclick=\"window.location.href='index.php'\" />
			</form>
	";
}

$wish_id = 0; //sql ids start with 1 so it doesnt show shit here 
if (!empty($_GET['id']))
{
$wish_id = $_GET['id'];
}
else
{
print_html_main("<font color=\"red\">Invalid wish id</font>");
fok();
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
	$state = $rows[0]['wish_STATE'];
	$reward = $rows[0]['wish_reward'];
    $description = $rows[0]['wish_desc'];
	$fullfiller = $rows[0]['wish_fullfiller'];
				
	if ($wisher == $_SESSION['Username'])
	{
		if ($fullfiller != "")
		{
			//print_html_main("<font color=\"red\">Error '$fullfiller' is currently working on that wish</font>");
			print_html_main("<font color=\"red\">Error someone is working on that wish already</font>");
			fok();
		}
		if ($state == 3)
		{
			print_html_main("<font color=\"red\">Error this wish is deleted already (state=$state)</font>");
			fok();
		}
		
		
		//DELETE WISH
        /*
		$db = new PDO(DATABASE_PATH);
		$stmt = $db->prepare('UPDATE Wishes SET wish_STATE = 3 WHERE ID = ?');
		$stmt->execute(array($wish_id));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
		if (DeleteWish($wish_id, "deleted by wisher") == -1)
		{
			print_html_main("<font color=\"red\">Failed to delete the wish</font>");
			fok();
		}
		
		//GIVE POINTS
        /*
		$db = new PDO(DATABASE_PATH);
		$stmt = $db->prepare('UPDATE Accounts SET Points = Points + ? WHERE Username = ?');
		$stmt->execute(array($reward, $wisher));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($rows)
		{
			print_html_main("<font color=\"red\">Failed to give points back</font>");
			fok();
		}
        */
        $send_reason = "deleted wish '$title'";
        SendPoints($_SESSION['Username'], "SERVER", $reward, $send_reason);
		
		print_html_main("Successfully deleted the wish '$title'</br> + $reward points");
		fok();
	}
	else
	{
		print_html_main("<font color=\"red\">You can only delete your own wishes</font>");
		fok();
	}
}
else
{
	print_html_main("<font color=\"red\">Something went wrong</font>");
	fok();
}
fok();
?>