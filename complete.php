<?php
//===============
// EggsChange
// complete.php
//===============
require_once(__DIR__ . "/global.php");
HtmlHeader("complete");

session_start();



if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
{
    echo "<a>you have to be logged in.</a>";
    echo "<input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></html>";
    fok();
}


if (!empty($_GET['id']))
{
    $wish_id = $_GET['id'];
}
else
{
    $wish_id = 0; //sql ids start with 1 so it doesnt show shit here
    echo "<p>Unknown wish</p>";
    echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form></html>";
    fok();
}

function print_html_main($fail_reason)
{
    global $wish_id;

/*
        <a>Sending a picture as proof helps.</a></br>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            Select image to upload:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload Image" name="submit">
        </form>
*/

echo "
        <form class=\"form\" method=\"post\" action=\"complete.php?id=$wish_id\">
			<textarea id=\"wish_desc\" rows=\"7\" cols=\"66\" name=\"done_txt\" placeholder=\"insert proof text and links\"></textarea></br></br>
            <input type=\"hidden\" name=\"DONE\" value=\"1\">
            <input type=\"submit\" value=\"done\" type/>
        </form>
        <form class=\"form\" method=\"post\" action= \"complete.php?id=$wish_id\">
            <input type=\"hidden\" name=\"GIVE_UP\" value=\"1\">
            <input type=\"submit\" value=\"give up\" type/>
        </form>
";

    if ($fail_reason != "none")
    {
        echo "<font color=\"red\">$fail_reason</font>";
    }

}

if (isset($_POST['GIVE_UP']))
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Wishes SET wish_fullfiller = "" WHERE ID = ?');
    $stmt->execute(array($wish_id));
    $db = NULL;
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Accounts SET STATE = 0 WHERE Username = ?');
    $stmt->execute(array($_SESSION['Username']));
	$db = NULL;

	header("Location: index.php");
	exit();
}

if (isset($_POST['DONE']))
{
	$done_txt = $_POST['done_txt'];

	$done_txt = (string)$done_txt;

	if (strlen($done_txt) > 512)
	{
		print_html_main("proof text too long."); //TODO: in this case all the wish info disappears but all the needed stuff is still there
		fok();
	}
    
    $current_date = date_create(date("Y-m-d H:i:s"));
    $current_date_str = $current_date->format('Y-m-d H:i:s'); 


    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Wishes SET wish_STATE = 1, wish_done_txt = ?, wish_done_date = ? WHERE ID = ?');
    $stmt->execute(array($done_txt, $current_date_str, $wish_id));
    $db = NULL;
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Accounts SET STATE = 0, OtherWishesTotal = OtherWishesTotal + 1 WHERE Username = ?');
    $stmt->execute(array($_SESSION['Username']));
    $db = NULL;

    header("Location: index.php");
    exit();
}


    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
    $stmt->execute(array($wish_id));
	$db = NULL;

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows)
    {
        $name = $rows[0]['wish_fullfiller'];
		$wisher = $rows[0]['wisher'];

		if ($wisher == $_SESSION['Username'])
		{
                echo "<p>You can't fullfill your own wish.</p>";
                echo "<form><input type=\"button\" value=\"back\" onclick=\"window.location.href='index.php'\" /></form></html>";
                fok();

		}
        else if (empty($name)) //nobody fullfilling this wish yet --> set current visitor as new fullfiller
		{
            
            $current_date = date_create(date("Y-m-d H:i:s"));
            $current_date_str = $current_date->format('Y-m-d H:i:s');
            
    		$db = new PDO(DATABASE_PATH);
    		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    		$stmt = $db->prepare('UPDATE Wishes SET wish_fullfiller = ?, wish_fullfill_date = ? WHERE ID = ?');
    		$stmt->execute(array($_SESSION['Username'], $current_date_str, $wish_id));
			$db = NULL;

            $db = new PDO(DATABASE_PATH);
            $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
            $stmt = $db->prepare('UPDATE Accounts SET missionID = ?, STATE = 1 WHERE Username = ?');
            $stmt->execute(array($wish_id, $_SESSION['Username']));

			echo "<p>You accecpted the wish and got set as new fullfiller</p>";
		}
		else //sombody is already fullfilling --> check if its the current visitor
		{
			if ($name != $_SESSION['Username'])
			{
				echo "<p>Sombody else is working on this wish already</p>";
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
	echo "<h1>$title</h1>";
	echo "<a>Reward: $reward points</a></br>";
	echo "<a>Description:</a> <p class=\"big_txt\">$description</p></br></br>";
	print_html_main("none");
	fok();
?>
