<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("wish", IsLoggedIn());

//checks
if (IsLoggedIn() == false)
{
	echo "<a>you have to be logged in.</a></br>";
	echo "<input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/>";
	fok();
}
else
{
    //dont allow to wish something new if old wishes are still pending
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $name = $_SESSION['Username'];
    $stmt = $rows = $db->query('SELECT * FROM Wishes WHERE wish_STATE = 1 AND wisher = ? ORDER BY wish_reward DESC LIMIT 10');
    $stmt->execute(array($name));
    $db = null;
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows)
    {
        echo "<a>You have pending confirms. Make sure to check them first.</a></br>";
        echo "<form class=\"form\"><input type=\"button\" value=\"Check confirms\" onclick=\"window.location.href='index.php'\"/></form>";
        fok();
    }
}

CheckAccountState($_SESSION['Username']);

function print_html_main($fail_reason)
{
	echo
	"
	<!DOCTYPE html>
	<html>
		<body>
			<h2>EggsChange Wish information</h2>
        		<form class=\"form\" method=\"post\" action=\"wishwish.php\">
                		<input id=\"wish_title\" type=\"text\" name=\"wish_title\"  placeholder=\"wish title\"></br>
                		<textarea id=\"wish_desc\" name=\"wish_desc\"  placeholder=\"wish description\"></textarea></br>
						<input id=\"wish_reward\" type=\"number\" name=\"wish_reward\"  placeholder=\"reward in points\"></br>
						<select name=\"wish_category\">
							<option value=\"0\" selected>No Category</option>
							<option value=\"1\">Fun</option>
							<option value=\"2\">Friends</option>
							<option value=\"3\">Sport</option>
							<option value=\"4\">Simple Task</option>
							<option value=\"5\">Question</option>
							<option value=\"6\">School</option>
							<option value=\"7\">Job</option>
							<option value=\"8\">Trade</option>
							<option value=\"9\">Social Networks</option>
							<option value=\"10\">Product</option>
                            <option value=\"11\">Music</option>
						</select></br></br>
						<a><abbr title=\"after how many days your wish gets deleted and you get your points back if its not fullfilled yet\">Expiration date</abbr></a></br>
						<select name=\"wish_expire\">
							<option value=\"0\">1 day</option>
							<option value=\"1\">2 days</option>
							<option value=\"2\">3 days</option>
							<option value=\"3\">4 days</option>
							<option value=\"4\" selected>5 days</option>
							<option value=\"5\">1 week</option>
							<option value=\"6\">2 weeks</option>
							<option value=\"7\">1 month</option>
							<option value=\"8\">3 months</option>
							<option value=\"9\">6 months</option>
							<option value=\"10\">1 year</option>
							<option value=\"11\">3 years</option>
						</select>
						</br></br>
                		<input type=\"submit\" value=\"Wish\" >
        		</form>
		</body>
	</html>
	";

//<input id=\"wish_proof\" type=\"text\" name=\"wish_proof\" placeholder=\"proof (video,foto,text,none)\"></br>

	if ($fail_reason != "none")
	{
		echo "<font color=\"red\">$fail_reason</font>";
	}
}


if (!empty($_POST['wish_title']) and !empty($_POST['wish_desc']) and !empty($_POST['wish_reward']))
{
	$wish_title = isset($_POST['wish_title'])? $_POST['wish_title'] : '';
	$wish_desc = isset($_POST['wish_desc'])? $_POST['wish_desc'] : '';
	$wish_reward = isset($_POST['wish_reward'])? $_POST['wish_reward'] : '';
/* special letters in wishes is ok i guess
	if (!preg_match('/^[a-z0-9]+$/i', $wish_title))
	{
		print_html_main("Only letters and numbers in wish title allowed");
		fok();
	}
*/

	$current_date = date_create(date("Ymd"));
	$wish_title = (string)$wish_title;
	$wish_desc = (string)$wish_desc;
	$wish_cat = "None";
	$wish_exp = date_create(date("Y-m-d"));
	
	if (!is_numeric($_POST['wish_category']))
	{
		print_html_main("Invalid category.");
		fok();
	}
	$wish_category=stripslashes(htmlspecialchars($_POST['wish_category']));
	$wish_category = (int)$wish_category;
	
	if ($wish_category == 0)
	{
		$wish_cat = "None";
	}
	else if ($wish_category == 1)
	{
		$wish_cat = "Fun";
	}
	else if ($wish_category == 2)
	{
		$wish_cat = "Friends";
	}
	else if ($wish_category == 3)
	{
		$wish_cat = "Sport";
	}
	else if ($wish_category == 4)
	{
		$wish_cat = "Simple Task";
	}
	else if ($wish_category == 5)
	{
		$wish_cat = "Question";
	}
	else if ($wish_category == 6)
	{
		$wish_cat = "School";
	}
	else if ($wish_category == 7)
	{
		$wish_cat = "Job";
	}
	else if ($wish_category == 8)
	{
		$wish_cat = "Trade";
	}
	else if ($wish_category == 9)
	{
		$wish_cat = "Social Networks";
	}
	else if ($wish_category == 10)
	{
		$wish_cat = "Product";
	}
	else if ($wish_category == 11)
	{
		$wish_cat = "Music";
	}
	else
	{
		print_html_main("Unknown category.");
		fok();
	}
	
	if (!is_numeric($_POST['wish_expire']))
	{
		print_html_main("Invalid expire time.");
		fok();
	}
	$wish_expire=stripslashes(htmlspecialchars($_POST['wish_expire']));
	$wish_expire = (int)$wish_expire;
	
	if ($wish_expire == 0)
	{
			date_add($wish_exp, date_interval_create_from_date_string('1 day'));
	}
	else if ($wish_expire == 1)
	{
			date_add($wish_exp, date_interval_create_from_date_string('2 days'));
	}
	else if ($wish_expire == 2)
	{
			date_add($wish_exp, date_interval_create_from_date_string('3 days'));
	}
	else if ($wish_expire == 3)
	{
			date_add($wish_exp, date_interval_create_from_date_string('4 days'));
	}
	else if ($wish_expire == 4)
	{
			date_add($wish_exp, date_interval_create_from_date_string('5 days'));
	}
	else if ($wish_expire == 5)
	{
			date_add($wish_exp, date_interval_create_from_date_string('7 days'));
	}
	else if ($wish_expire == 6)
	{
			date_add($wish_exp, date_interval_create_from_date_string('14 days'));
	}
	else if ($wish_expire == 7)
	{
			date_add($wish_exp, date_interval_create_from_date_string('30 days'));
	}
	else if ($wish_expire == 8)
	{
			date_add($wish_exp, date_interval_create_from_date_string('90 days'));
	}
	else if ($wish_expire == 9)
	{
			date_add($wish_exp, date_interval_create_from_date_string('180 days'));
	}
	else if ($wish_expire == 10)
	{
			date_add($wish_exp, date_interval_create_from_date_string('365 days'));
	}
	else if ($wish_expire == 11)
	{
			date_add($wish_exp, date_interval_create_from_date_string('1095 days'));
	}
	else
	{
		print_html_main("Unknown expire time.");
		fok();
	}
	
	
	if (strlen($wish_title) > 64)
	{
		print_html_main("Wish title too long.");
		fok();
	}
    if (strlen($wish_desc) > 256)
    {
        print_html_main("Wish description too long.");
        fok();
    }


	if (!is_numeric($wish_reward))
	{
		print_html_main("Wish reward has to be a number");
		fok();
	}

	if ($wish_reward < 1)
	{
		print_html_main("Reward has to be higer than zero"); //doesnt get triggered on zero because it says not all fields filled
		fok();
	}

	//check if wish_reward can be paid with own points
	$db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
    $stmt->execute(array($_SESSION['Username']));
    $db = null;
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows)
    {
        $points = $rows[0]['Points'];
		if ($points < $wish_reward)
		{
			print_html_main("Can't set reward to $wish_reward points.</br>You only have $points points.</br>Fullfill some wishes to get more points.");
			fok();
		}
    }
	else
    {
        print_html_main("Somethign went horrible wrong.");
        fok();
    }

	//pay the reward points
    /*
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Accounts SET Points = Points - ? WHERE Username = ?');
    $stmt->execute(array($wish_reward, $_SESSION['Username']));
    $db = NULL;
    */
    $pay_reason = "wished wish '$wish_title'";
    SendPoints("SERVER", $_SESSION['Username'], $wish_reward, $pay_reason);




/*
	$db = new PDO('sqlite:/home/chiller/EggsChange/EggsChange.db');
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
	$stmt->execute(array($username));
	$db = null;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// i guess wishtitles can be duplicated
	if ($rows)
	{
		print_html_main("wishtitle already exsits");
		fok();
	}
*/

	//increase total own wishes for statistics
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('UPDATE Accounts SET OwnWishesTotal = OwnWishesTotal + 1 WHERE Username = ?');
    $stmt->execute(array($_SESSION['Username']));
    $db = NULL;

    //CONVERT DATE TO INTEGER
	//$current_date_int = (int)date_format($current_date, "Ymd");
	//$wish_exp_int = (int)date_format($wish_exp, "Ymd");
    
    //CONVERT DATE TO STRING
    $current_date_str = $current_date->format('Y-m-d');
	$wish_exp_str = $wish_exp->format('Y-m-d');
	
	//anti cross site scripting
	$wish_desc = AntiXSS($wish_desc);
	$wish_title = AntiXSS($wish_title);
    
   	$db = new PDO(DATABASE_PATH);
   	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
   	$stmt = $db->prepare('INSERT INTO Wishes (wish_name, wish_desc, wisher, wish_reward, wish_category, wish_date, wish_exp_date) VALUES (?, ?, ?, ?, ?, ?, ?)');
  	$stmt->execute(array($wish_title, $wish_desc, $_SESSION['Username'], $wish_reward, $wish_cat, $current_date_str, $wish_exp_str));

    echo "Sucessfully wished a wish for $wish_reward</br>";
    echo "
    <script type=\"text/javascript\">
        window.setTimeout(function()
        {
           window.location.href='index.php';
        }, 2000);
    </script>
    ";
    echo "<form><input type=\"button\" value=\"okay\" onclick=\"window.location.href='index.php'\" /></form>";

}
else if (!empty($_POST['wish_title']) or !empty($_POST['wish_desc']) or !empty($_POST['wish_reward']))
{
	print_html_main("Title, description and reward required");
}
else //no name or pw given -> ask for it
{
	print_html_main("none");
}

fok();
?>
