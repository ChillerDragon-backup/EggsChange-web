<?php
session_start(); //so no warning when session gets destoryed later on
session_destroy(); //clear all before new login
session_start();

require_once(__DIR__ . "/global.php");
require_once(__DIR__ . "/ClientInfos.php");
HtmlHeader("login");

function print_html_main($fail_reason)
{
	echo
	"
			<div class=\"wrapper\">
			<h2> EggsChange login </h2>
        		<form method=\"post\" action=\"login.php\">
                		<input id=\"username\" type=\"text\" name=\"username\"  placeholder=\"username\"></br>
                		<input id=\"password\" type=\"password\" name=\"password\" placeholder=\"password\"></br>


				</br>
                		<input type=\"submit\" value=\"Login\" >
        		</form>
			<form>
				<input type=\"button\" value=\"No account? -> Register\" onclick=\"window.location.href='register.php'\" />
			</form>
			</div>
	";

	if ($fail_reason != "none")
	{
		echo "<font color=\"red\">$fail_reason</font>";
	}
}


if (!empty($_POST['username']) and !empty($_POST['password']))
{
	$username = isset($_POST['username'])? $_POST['username'] : '';
	$password = isset($_POST['password'])? $_POST['password'] : '';

	$db = new PDO(DATABASE_PATH);
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ? and Password = ?');
	$stmt->execute(array($username, $password));

	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($rows)
	{
        $name = $rows[0]['Username'];
		$_SESSION['Username'] = $name;
		echo "Logged in as '$name' </br>";
		$_SESSION['IsLogged'] = "online";
		echo "
			<script type=\"text/javascript\">
    				window.setTimeout(function()
				{
    					window.location.href='index.php';
    				}, 2000);
			</script>
		";
		echo "<form><input type=\"button\" value=\"okay\" onclick=\"window.location.href='index.php'\" /></form>";
        
        //Get Date
        $current_date = date_create(date("Y-m-d H:i:s"));
        $current_date_str = $current_date->format('Y-m-d H:i:s');
        
        //Get City
        $ip = $_SERVER['REMOTE_ADDR'];
        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
        //echo "City: " . $details->city;
        
        //Get Operating system
        $user_os = getOS($_SERVER['HTTP_USER_AGENT']);
        
        //Get Browser
        $user_browser = getBrowser($_SERVER['HTTP_USER_AGENT']);
        
	    $db = new PDO(DATABASE_PATH);
	    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	    $stmt = $db->prepare('UPDATE Accounts SET LastLogin = ? WHERE Username = ?');
	    $stmt->execute(array($current_date_str, $username));
        $db = NULL;
        
        //Add login to login history
	    $db = new PDO(DATABASE_PATH);
	    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	    $stmt = $db->prepare('INSERT INTO Login (Username, TimeStamp, IP, Location, Browser, OS, OtherDetails) VALUES (?, ?, ?, ?, ?, ?, ?)');
	    $stmt->execute(array($username, $current_date_str, $_SERVER['REMOTE_ADDR'], $details->city, $user_browser, $user_os, $_SERVER['HTTP_USER_AGENT']));
        $db = NULL;
	}
	else
	{
		print_html_main("wrong username or password");
		$_SESSION['IsLogged'] = "failed";
	}
}
else if (!empty($_POST['username']) or !empty($_POST['password']))
{
	print_html_main("both fields are required");
}
else //no name or pw given -> ask for it
{
	print_html_main("none");
}
fok();
?>
