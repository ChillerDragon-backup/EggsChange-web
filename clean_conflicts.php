<?php
//===============
// EggsChange
// clean_conflicts.php
//===============
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("clean conflicts", false);

if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
{
	echo "<a>you have to be logged in.</a></br>";
	echo "<form><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
	fok();
}

//Check valid account state
CheckAccountState($_SESSION['Username']);

//Check for judging permission
$rep_lvl = GetReputationLevel($_SESSION['Username']);
if ($rep_lvl < NEEDED_REP_LVL_FOR_JUDGING)
{
    echo "<a>You need atleast reputation level " . NEEDED_REP_LVL_FOR_JUDGING . " to do this.</br> Your are level $rep_lvl</a></br>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
    fok();
}


$db = new PDO(DATABASE_PATH);

$rows = $db->query('SELECT * FROM Wishes WHERE wish_STATE = 4 ORDER BY RANDOM()');

if ($rows)
{
	foreach ($rows as $row)
	{	
		$wisher = $row['wisher'];
		$fullfiller = $row['wish_fullfiller'];
        $wish_id = $row['ID'];
		
		if ($_SESSION['Username'] == $wisher || $_SESSION['Username'] == $fullfiller)
			continue;
            
        
        //Check if judged already
        $db = new PDO(DATABASE_PATH);
        $stmt = $db->prepare('SELECT * FROM Conflicts WHERE wish_ID = ? AND judger = ?');
        $stmt->execute(array($wish_id, $_SESSION['Username']));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows)
        {
            //echo "<a>You already judged this wish</br></a>";
            //echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
            continue;
        }
	
		$title = $row['wish_name'];
		$proof = $row['wish_proof'];
		$anti_proof = $row['wish_notdone_txt'];
		$desc = $row['wish_desc'];
		$wish_id = $row['ID'];
        $wish_date = $row['wish_date'];
        $accept_date = $row['wish_fullfill_date'];
        $done_date = $row['wish_done_date'];
		echo "<h1>$title</h1>";
        echo "<a><strong>Wished at:</strong> $wish_date</br><strong>Accepted as fullfiller at:</strong> $accept_date</br><strong>Finished wish by fullfiller:</strong> $done_date</br></br></a>";
		echo "<a><strong>Description (wisher):</strong></br>$desc</br></a>";
		echo "<a><strong>Proof (fullfiller):</strong></br>$proof</br></a>";
		echo "<a><strong>Anti proof (wisher):</strong></br>$anti_proof</br></a>";
		echo "</br></br>";
		echo "<form><input type=\"button\" value=\"Fullfiller is right\" onclick=\"window.location.href='judged_conflict.php?id=$wish_id&right=fullfiller'\"/>  ";
		echo "<input type=\"button\" value=\"Wisher is right\" onclick=\"window.location.href='judged_conflict.php?id=$wish_id&right=wisher'\"/></form>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
		fok();
	}
	
	echo "<a>You are involved and or judged all conflicts already.</br></a>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
}
else
{
	echo "<a>yay no conflicts.</a></br>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
}

fok();
?>