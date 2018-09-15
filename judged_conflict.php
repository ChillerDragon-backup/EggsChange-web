<?php
//===============
// EggsChange
// judged_conflict.php
//===============
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("conflicts");

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

$wish_id = 0; //sql ids start with 1 so it doesnt show shit here
if (!empty($_GET['id']) && $_GET['id'] > 0)
{
    $wish_id = $_GET['id'];
}
else
{
	echo "<a>Invalid wish id.</a></br>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
	fok();
}

$right = "fullfiller";
if (!empty($_GET['right']))
{
	$right = $_GET['right'];
	if ($right != "fullfiller" && $right != "wisher")
	{
		echo "<a>Error: invalid right value</a></br>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
		fok();
	}
}
else
{
	echo "<a>Error: you have to choose who is right.</a></br>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
	fok();
}

function PointsRewardVoters($looser_username, $winner_amount, $id, $wish_name)
{    
    $looser_points = CountPoints($looser_username);
    //echo "Loosers points ($looser_points) have to be shared to ($winner_amount) vote winners</br>";
    
    if ($looser_points > $winner_amount)
    {
        //echo "User has enough points to give 1 to every voter</br>";
            
        $db = new PDO(DATABASE_PATH);
        $stmt = $db->prepare('SELECT * FROM Conflicts WHERE wish_ID = ?');
        $stmt->execute(array($id));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        if ($rows)
        {
            foreach ($rows as $row)
            {
                $voter_username = $row['judger'];
                //echo "Giving 1 point to '$voter_username' reason '$reason'</br>";
                $error_send = SendPoints($voter_username, $looser_username, 1, "Conflict judge '" . $wish_name . "'");
                if ($error_send != 0)
                    echo "error: $error_send</br>";
            }
        }
    }
}

function VoterReputation($id, $winner, $looser_username, $wish_name)
{
    $db = new PDO(DATABASE_PATH);
    $stmt = $db->prepare('SELECT * FROM Conflicts WHERE wish_ID = ?');
   	$stmt->execute(array($id));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $winner_amount = 0;
    
    if ($rows)
    {
        foreach ($rows as $row)
        {
            $voter_name = $row['judger'];
            if ($row['IsRight'] == $winner)
            {
                //increment winner voter reputation
                $db = new PDO(DATABASE_PATH);
                $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
                $stmt = $db->prepare('UPDATE Accounts SET REPp = REPp + 1 WHERE Username = ?');
                $stmt->execute(array($voter_name));
                $db = NULL;
                
                $winner_amount++;
            }
            else
            {
                //decrement looser voter reputation
                $db = new PDO(DATABASE_PATH);
                $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
                $stmt = $db->prepare('UPDATE Accounts SET REPm = REPm + 1 WHERE Username = ?');
                $stmt->execute(array($voter_name));
                $db = NULL;
            }
        }       
        PointsRewardVoters($looser_username, $winner_amount, $id, $wish_name);
        $del_reason = "[" . $looser_username . "] lost conflict";
        DeleteWish($id, $del_reason);
    }
    else
    {
        die("Error updating voter reputation.</br>");
    }
}

function RewardWinnerPunishLooser($id, $winner)
{
	$db = new PDO(DATABASE_PATH);
    $stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
   	$stmt->execute(array($id));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $winner_name = "none";
    $looser_name = "none";
    
    if ($rows)
    {
        if ($winner == "wisher")
        {
            $winner_name = $rows[0]['wisher'];
            $looser_name = $rows[0]['wish_fullfiller'];
        }
        else if ($winner == "fullfiller")
        {
            $winner_name = $rows[0]['wish_fullfiller'];
            $looser_name = $rows[0]['wisher'];
        }
        else
            die("Either wisher or fullfiller has to win.");
        
        $reward = $rows[0]['wish_reward'];
        
        //Give Winner the reward
        /*
    	$db = new PDO(DATABASE_PATH);
    	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    	$stmt = $db->prepare('UPDATE Accounts SET Points = Points + ? WHERE Username = ?');
    	$stmt->execute(array($reward, $winner_name));
    	$db = NULL;
        */
        SendPoints($winner_name, "SERVER", $reward, "Won conflict on wish '" . $rows[0]['wish_name'] . "'");
        
        //decrement loosers reputation
    	$db = new PDO(DATABASE_PATH);
    	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    	$stmt = $db->prepare('UPDATE Accounts SET REPm = REPm + 1 WHERE Username = ?');
    	$stmt->execute(array($looser_name));
    	$db = NULL;
        
        //a win for the fullfiller counts as normal accept
        if ($winner == "fullfiller")
        {
            $db = new PDO(DATABASE_PATH);
            $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
            $stmt = $db->prepare('UPDATE Accounts SET OtherWishesAccepted = OtherWishesAccepted + 1 WHERE Username = ?');
            $stmt->execute(array($winner_name));
            $db = NULL;
        }
        
        VoterReputation($id, $winner, $looser_name, $rows[0]['wish_name']);
    }
    else
    {
        die("error rewarding winner</br>");
    }
}

function EvaluateWinner($id)
{
	$db = new PDO(DATABASE_PATH);

    $stmt = $db->prepare('SELECT * FROM Conflicts WHERE wish_ID = ?');
   	$stmt->execute(array($id));

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                   
    $wisher_value = 0;
    $fullfiller_value = 0;
    
    if ($rows)
    {
        foreach ($rows as $row)
        {
            if ($row['IsRight'] == "wisher")
                $wisher_value += $row['VoteValue'];
            else if ($row['IsRight'] == "fullfiller")
                $fullfiller_value += $row['VoteValue'];
            else
                die("<a>Error something went wrong</a></br>");
        }
        
        echo "<h3>Results</h3><a>Wisher: $wisher_value</br>Fullfiller: $fullfiller_value</br><a>";
        
        if (is_numeric($fullfiller_value) && is_numeric($wisher_value)) 
        {
            $vote_diff = abs( $fullfiller_value - $wisher_value );
            
            if ($vote_diff < NEEDED_VOTE_DIFF)
            {
                //echo "<a>Vote difference ($vote_diff) too low... collecting more votes.</a></br>";
                fok();
            }
            
            if ($fullfiller_value > $wisher_value)
            {
                RewardWinnerPunishLooser($id, "fullfiller");
            }
            else
            {
                RewardWinnerPunishLooser($id, "wisher");
            }
        }
        else
        {
            die("<a>Error calculating winner (not numeric).<a></br>");
        }
    }
    else
    {
        echo "<a>Error calculating winner.<a></br>";
    }
}

function CheckEnoughJudges($id)
{
	$db = new PDO(DATABASE_PATH);

    $stmt = $db->prepare('SELECT COUNT(*) AS TotalJudges FROM Conflicts WHERE wish_ID = ?');
   	$stmt->execute(array($id));

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                   
    if ($rows)
    {
        $total_judges = $rows[0]['TotalJudges'];
        $total_judges = (int)$total_judges;
        echo "<a>Total judges on this wish: $total_judges</a></br>";
        
        if ($total_judges > NEEDED_JUDGES)
        {
            echo "<a>$total_judges/" . NEEDED_JUDGES . " judges reached.</a></br>";
            EvaluateWinner($id);
        }
    }
}

function DecisionDecided($right, $id)
{	
	$db = new PDO(DATABASE_PATH);

    $stmt = $db->prepare('INSERT INTO Conflicts (wish_ID, judger, IsRight, VoteValue) VALUES (?, ?, ?, ?)');
   	$stmt->execute(array($id, $_SESSION['Username'], $right, CalculateVoteValue($_SESSION['Username'])));

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($rows)
	{
		echo "<font color=\"red\">Something went wrong.</font>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
	}
	else
	{
		echo "<h1>Conflict successfully judged</h1>";
		echo "<a>Thank you for improving EggsChange.</a></br>";
		echo "<a>You supported the <strong>$right</strong>.</a></br>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
	}
    CheckEnoughJudges($id);
}


$db = new PDO(DATABASE_PATH);

$stmt = $db->prepare('SELECT * FROM Wishes WHERE ID = ?');
$stmt->execute(array($wish_id));

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
	$wisher = $rows[0]['wisher'];
	$fullfiller = $rows[0]['wish_fullfiller'];
	$title = $rows[0]['wish_name'];
	$state = $rows[0]['wish_STATE'];
	
	if ($state != 4)
	{
		echo "<a>This wish isn't involved in any conflict.</br></a>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
		fok();
	}
	
	if ($_SESSION['Username'] == $wisher || $_SESSION['Username'] == $fullfiller)
	{
		echo "<a>You are involved in this wish.</br></a>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
		fok();
	}
	
    //Check if judged already
    $db = new PDO(DATABASE_PATH);

    $stmt = $db->prepare('SELECT * FROM Conflicts WHERE wish_ID = ? AND judger = ?');
    $stmt->execute(array($wish_id, $_SESSION['Username']));

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rows)
    {
        echo "<a>You already judged this wish</br></a>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
        fok();
    }
    
	if (!empty($_GET['sure']) && $_GET['sure'] == "yes")
	{
		DecisionDecided($right, $wish_id);
		fok();
	}
	
	echo "<h1>Conflict '$title'</h1>";
	echo "<a>Are you sure you did everything correct?</a></br>";
	if ($right == "wisher")
	{
		echo "<a>Your said the <strong>wisher</strong> is right.</a></br>";
	}
	else if ($right == "fullfiller")
	{
		echo "<a>Your said the <strong>fullfiller</strong> is right.</a></br>";
	}
	else
	{
		echo "<a>Erronus values.</a></br>";
		echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
		fok();
	}
    
    echo "<a></br>Make sure to doublecheck and only judge after good research.</br>If you choose wrong this will harm your reputation.</br>If you are right this will increase your reputation and reward you with points.</a></br>";
	
	echo "<form><input type=\"button\" value=\"Yes i am sure\" onclick=\"window.location.href='judged_conflict.php?id=$wish_id&right=$right&sure=yes'\"/></form>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
}
else
{
	echo "<a>This wish doesn't exist.</a>";
	echo "<form><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
	fok();
}

fok();
?>