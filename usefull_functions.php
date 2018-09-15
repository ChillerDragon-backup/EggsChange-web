<?php
require_once(__DIR__ . "/global.php");

function fok()
{
	HtmlFooter();
	die();
}

function HtmlFooter()
{
?>
	</body>
</html>
<?php
}

function HtmlHeader($title, $menu = false)
{
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<title><?php echo "EggsChange - " . $title; ?></title>
  
	<!-- Icons -->
	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon-precomposed" sizes="60x60" href="apple-touch-icon-60x60.png" />
	<link rel="apple-touch-icon-precomposed" sizes="120x120" href="apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon-precomposed" sizes="76x76" href="apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="apple-touch-icon-152x152.png" />
	<link rel="icon" type="image/png" href="favicon-196x196.png" sizes="196x196" />
	<link rel="icon" type="image/png" href="favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
	<link rel="icon" type="image/png" href="favicon-128.png" sizes="128x128" />
	<meta name="application-name" content="&nbsp;"/>
	<meta name="msapplication-TileColor" content="#FFFFFF" />
	<meta name="msapplication-TileImage" content="mstile-144x144.png" />
	<meta name="msapplication-square70x70logo" content="mstile-70x70.png" />
	<meta name="msapplication-square150x150logo" content="mstile-150x150.png" />
	<meta name="msapplication-wide310x150logo" content="mstile-310x150.png" />
	<meta name="msapplication-square310x310logo" content="mstile-310x310.png" />
 </head>
  <body>
<?php 
	if ($menu == true) 
	{ 
?>
	<a href="index.php">[Home]</a><a href="fullfillwish.php">[Fullfill]</a><a href="wishwish.php">[Wish]</a><a href="account.php">[Account]</a><br><br>
<?php
	}
}

function CountPoints($username)
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT Points FROM Accounts WHERE Username = ?');
    $stmt->execute(array($username));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rows)
    {
        return $rows[0]['Points'];
    }
    return -1;
}

function SendPoints($recv, $send, $amount, $reason)
{
    //check self send 
    if ($recv == $send)
        return -1;
    
    //only send positive amounts
    if ($amount < 1)
        return -2;
    
    //Remove points
    if ($send != "SERVER") //SERVER has unlimited points
    {
        $sender_points_total = CountPoints($send);
        if ($sender_points_total < $amount)
        {
            //echo "Sender '$send' only has ($sender_points_total) points</br>";
            return -3;
        }
        
        $db = new PDO(DATABASE_PATH);
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $stmt = $db->prepare('UPDATE Accounts SET Points = Points - ? WHERE Username = ?');
        $stmt->execute(array($amount, $send));
        $db = NULL;
    }

    //Give points
    if ($recv != "SERVER") //SERVER has no account to transfer points to
    {
        $db = new PDO(DATABASE_PATH);
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $stmt = $db->prepare('UPDATE Accounts SET Points = Points + ? WHERE Username = ?');
        $stmt->execute(array($amount, $recv));
        $db = NULL;
    }
    
    //get timestamp in string format
    $current_date = date_create(date("Y-m-d H:i:s"));
    $current_date_str = $current_date->format('Y-m-d H:i:s');
    
    //Store transaction to transaction database
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('INSERT INTO Points (Reciver, Sender, Points, Reason, TransactionDate) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(array($recv, $send, $amount, $reason, $current_date_str));
    $db = NULL;
    
    return 0;
}


function GetAccountAge($username) //Totally working but unused yet
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
    $stmt->execute(array($username));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rows)
    {
        $register_date = new DateTime($rows[0]['RegisterDate']);
        $current_date = date_create(date("Y-m-d"));
        $interval = $current_date->diff($register_date);
        
        return $interval->days;
        
        //working but only interesting for debugging
        /*
        $register_date_str = $register_date->format('Y-m-d');
        $current_date_str = $current_date->format('Y-m-d');
        echo "<a>Total days: $interval->days</a></br>";
        echo "<a>Your registerd " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days  ago </br></a>";
        echo "<a>Register date=$register_date_str </br></a>";
        echo "<a>Today date=$current_date_str </br></a>";
        */
    }
    return -1;
}

function GetReputationLevel($username) //levls:   0=None 1=1week/10REPp 2=2weeks/20REPp 3=1month/50REPp 4=3month/200REPp 5=2month/250rep_diff
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
    $stmt->execute(array($username));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rows)
    {
        $register_date = new DateTime($rows[0]['RegisterDate']);
        $current_date = date_create(date("Y-m-d"));
        $interval = $current_date->diff($register_date);
        $AccAge = $interval->days;
        $rep_plus = $rows[0]['REPp'];
        $rep_minus = $rows[0]['REPm'];
        $rep = $rep_plus - $rep_minus;
        
        if ($AccAge > 60 && $rep > 250)
            return 5;
        if ($AccAge > 90 && $rep_plus > 200)
            return 4;
        if ($AccAge > 30 && $rep_plus > 50)
            return 3;
        if ($AccAge > 14 && $rep_plus > 20)
            return 2;
        if ($AccAge > 7 && $rep_plus > 10)
            return 1;
    }
    
    return 0; //no reputation at all
}

function CalculateVoteValue($username)
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
    $stmt->execute(array($username));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $value = 0;
    
    if ($rows)
    {
        $rep_plus = $rows[0]['REPp'];
        $rep_minus = $rows[0]['REPm'];
        $rep_diff = abs( $rep_plus - $rep_minus );
         

        
        if ($rep_diff < 5) //no real difference
        {
            $value = 5;
        }
        else if ($rep_diff < 20)
        {
            if ($rep_plus > $rep_minus)
                $value = 6;
            else
                $value = 5;
        }
        else if ($rep_diff < 40)
        {
            if ($rep_plus > $rep_minus)
                $value = 7;
            else
                $value = 4;
        }
        else if ($rep_diff < 80)
        {
            if ($rep_plus > $rep_minus)
                $value = 10;
            else
                $value = 3;
        }
        else if ($rep_diff < 500)
        {
            if ($rep_plus > $rep_minus)
                $value = 8;
            else
                $value = 2;
        }
        else //500+ difference
        {
            if ($rep_plus > $rep_minus)
                $value = 12;
            else
                $value = 1;
        }
    }
    
    return $value;
}

function DeleteWish($id, $reason)
{
    $current_date = date_create(date("Y-m-d H:i:s"));
    $current_date_str = $current_date->format('Y-m-d H:i:s');    

	$db = new PDO(DATABASE_PATH);
    $stmt = $db->prepare('UPDATE Wishes SET wish_STATE = 3, delete_reason = ?, archive_date = ? WHERE ID = ?');
   	$stmt->execute(array($reason, $current_date_str, $id));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db = NULL;
    
    //echo "deleted with reason: $reason </br>";
    
    if ($rows)
        return -1;
    return 0;
}

function AntiXSS($str)
{
	$str = filter_var($str, FILTER_SANITIZE_STRING);
	$str = htmlspecialchars($str);
	return $str;
}

function IsLoggedIn()
{
	if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
		return false;
	return true;
}

function CheckAccountState($username)
{
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
    $stmt->execute(array($username));

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
	else
	{
		echo "<a>Invalid account</a></br>";
		echo "<form class=\"form\"><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
		fok();
	}
}

?>
