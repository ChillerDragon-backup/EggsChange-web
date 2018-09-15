<?php
//===============
// EggsChange
// confirm.php
//===============
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("confirm");

if (!empty($_GET['id']))
{
$wish_id = $_GET['id'];
}
else
{
$wish_id = 0; //sql ids start with 1 so it doesnt show shit here
echo "<p>Unknown wish</p>";
echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form>";
fok();
}


function print_html_main($fail_reason)
{
global $wish_id;
echo "
        <form class=\"form\" method=\"post\" action=\"confirm.php?id=$wish_id\">
            <input type=\"hidden\" name=\"DECLINE\" value=\"1\">
            <input type=\"submit\" value=\"decline\" type/>
        </form>
        <form class=\"form\" method=\"post\" action=\"confirm.php?id=$wish_id\">
            <input type=\"hidden\" name=\"ACCEPT\" value=\"1\">
            <input type=\"submit\" value=\"accept\" type/>
        </form>
		</br><form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form>
";

    if ($fail_reason != "none")
    {
        echo "<font color=\"red\">$fail_reason</font>";
    }
}

if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
{
	echo "<a>you have to be logged in.</a>";
	echo "<form><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
	fok();
}

if (isset($_POST['DELETE_DECLINE']))
{
	$notdone_txt = $_POST['notdone_txt'];

	$notdone_txt = (string)$notdone_txt;
	if (strlen($notdone_txt) > 512)
	{
		print_html_main("reverse proof text too long.");
		fok();
	}

	//anti cross site scripting
	$notdone_txt = AntiXSS($notdone_txt);
	
    //set wish to state 4 which is the decline state
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Wishes SET wish_STATE = 4, wish_notdone_txt = ? WHERE ID = ?');
    $stmt->execute(array($notdone_txt, $wish_id));
    $db = NULL;

	//fetch the username and wish reward (commented out because we give points back after conflict merging)
    /*
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
    $stmt->execute(array($wish_id));
    $db = NULL;

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows)
    {
        $reward = $rows[0]['wish_reward'];
		$user = $rows[0]['wisher'];

    	//give points back to wisher
    	$db = new PDO(DATABASE_PATH);
    	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    	$stmt = $db->prepare('UPDATE Accounts SET Points = Points + ? WHERE Username = ?');
    	$stmt->execute(array($reward, $user));
    	$db = NULL;

	}
	else
	{
		echo "something went wrong.";
		echo "<input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/>";
		fok();
	}
    */

    header("Location: index.php");
    exit();
}


if (isset($_POST['ACCEPT']))
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
    $stmt->execute(array($wish_id));
    $db = NULL;

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows)
    {
		$reward = $rows[0]['wish_reward'];
		$fullfiller = $rows[0]['wish_fullfiller'];
        $title = $rows[0]['wish_name'];

        //Old code didnt save the transaction
        /*
	    $db = new PDO(DATABASE_PATH);
    	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
   		$stmt = $db->prepare('UPDATE Accounts SET Points = Points + ?, OtherWishesAccepted = OtherWishesAccepted + 1 WHERE Username = ?');
		$stmt->execute(array($reward, $fullfiller));
	    $db = NULL;
        */
        
        //Do the points transaction
        SendPoints($fullfiller, "SERVER", $reward, "fullfilled wish '" . $title . "'");
        
        //AND increment the accepts
        $db = new PDO(DATABASE_PATH);
    	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
   		$stmt = $db->prepare('UPDATE Accounts SET OtherWishesAccepted = OtherWishesAccepted + 1 WHERE Username = ?');
		$stmt->execute(array($fullfiller));
	    $db = NULL;
    }
    else
    {
        echo "<p>Something went wrong.</p>";
        echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form>";
        fok();
    }

    $current_date = date_create(date("Y-m-d H:i:s"));
    $current_date_str = $current_date->format('Y-m-d H:i:s');
    
	//move wish to archive
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Wishes SET wish_STATE = 2, archive_date = ? WHERE ID = ?');
    $stmt->execute(array($current_date_str, $wish_id));
    $db = NULL;

	//upgrade wisher reputation
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Accounts SET REPp = REPp + 1, OwnWishesAccepted = OwnWishesAccepted + 1 WHERE Username = ?');
    $stmt->execute(array($_SESSION['Username']));
    $db = NULL;


    header("Location: index.php");
    exit();
}


if (isset($_POST['DECLINE']) == 1)
{
    echo "
        <h1>DECLINE</h1>

        <a>Do you really want to decline the wish? Another person claimed that he fullfilled the wish.</br>
        By declining it you start an conflict. And after investigating one of you two gets a bad reputation.</br>
        Choose Between:</br>
        - ACCEPT (to accept the wish and all people are happy)</br>
        - DECLINE (decline the wish and delete the wish)</br></a>

        <a>A good proof of your unfullfilled wish can save your reputation.</a></br>
        <form method=\"post\" action=\"confirm.php?id=$wish_id\">
			<textarea id=\"wish_desc\" rows=\"5\" cols=\"80\" name=\"notdone_txt\"></textarea></br></br>
            <input type=\"hidden\" name=\"DECLINE\" value=\"0\">
            <input type=\"hidden\" name=\"DELETE_DECLINE\" value=\"1\">
            <input type=\"submit\" value=\"DECLINE (delete)\" type/>
        </form>
        <form method=\"post\" action=\"confirm.php?id=$wish_id\">
            <input type=\"hidden\" name=\"DECLINE\" value=\"0\">
            <input type=\"hidden\" name=\"ACCEPT\" value=\"1\">
            <input type=\"submit\" value=\"ACCEPT\" type/>
        </form>
    ";

/*
        <form action=\"upload.php\" method=\"post\" enctype=\"multipart/form-data\">
            Select image to upload:
            <input type=\"file\" name=\"fileToUpload\" id=\"fileToUpload\">
            <input type=\"submit\" value=\"Upload Image\" name=\"submit\">
        </form>
*/
}
else
{
	$db = new PDO(DATABASE_PATH);
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
	$stmt->execute(array($wish_id));
	$db = NULL;

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($rows)
	{
		$name = $rows[0]['wisher'];
		if (empty($name))
		{
			echo "<p>Something went horrible wrong</p>";
			echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form>";
			fok();
		}
		else
		{
			if ($name != $_SESSION['Username'])
			{
				echo "<p>This is not your wish</p>";
				echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form>";
				fok();
			}
		}
	}
	else
	{
		echo "<p>Invalid wish.</p>";
		echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form>";
		fok();
	}


	//all checks done --> print wish infos agian
	$title = $rows[0]['wish_name'];
	$reward = $rows[0]['wish_reward'];
	$description = $rows[0]['wish_desc'];
	$done_txt = $rows[0]['wish_done_txt'];
	echo "<h1>Confirm Wish</h1>";
	echo "<a><strong>Name:</strong> $title</a></br>";
	echo "<a><strong>Points:</strong> $reward</a></br>";
	echo "<a><strong>Description:</strong></br>$description</br></a>";
	echo "<a><strong>Proof:</strong></br>$done_txt</br></a>";

	/*
	echo "<a>Press ACCEPT to give the points to the person who fullfilled your wish.</br>
		only press DECLINE if your wish didn't get fullfilled.</a></br></br>";
	*/
	print_html_main("none");
}
fok();
?>


