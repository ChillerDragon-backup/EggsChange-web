<?php
session_start(); //hacky workaround on the warning (destroyed uninitialzied session)
session_destroy(); //clear all stuff on register
session_start();

require_once(__DIR__ . "/global.php");
HtmlHeader("register", false);

function print_html_main($fail_reason)
{
    if (IS_ALLOWED_INVITES == true)
    {
		
	?>
			<script src="https://www.google.com/recaptcha/api.js" async defer></script>
			<script type="text/javascript">
					var onloadCallback = function() 
					{
						grecaptcha.render('html_element', {'sitekey' : '6Lehb3AUAAAAAA9qX_Jb6B5bhZP4MUuyYORvnhOa-gus'});
					};
			</script>
	<?php
	echo
	"
			<h2> EggsChange Register</h2>
				<div class=\"login-form\">
					<form method=\"post\" action=\"register.php\" enctype=\"multipart/form-data\">
						<div id=\"html_element\"></div>
							<input id=\"username\" type=\"text\" name=\"username\"  placeholder=\"username\"></br>
							<input id=\"password\" type=\"password\" name=\"password\" placeholder=\"password\"></br>
							<input id=\"repeate_password\" type=\"password\" name=\"repeate_password\" placeholder=\"repeate password\"></br>
                            <a><abbr title=\"Add the Username of a friend to reward him with points after you got active on this platform.\">Optional: Who told you about EggsChange?</abbr><a></br>
                            <input id=\"invitor\" type=\"text\" name=\"invitor\" placeholder=\"Who told you about it? (Username)\"></br>
							</br>
							<div id=\"recaptcha\">
								<div class=\"g-recaptcha\" data-sitekey=\"6Lehb3AUAAAAAA9qX_Jb6B5bhZP4MUuyYORvnhOa-gus\"></div>
							</div>
							<input type=\"submit\" value=\"Register\" >
					</form>
				</div>
			<form>
				<input type=\"button\" value=\"Got an account -> Login\" onclick=\"window.location.href='login.php'\" />
			</form>
	";
    }
    else 
    {
	echo
	"
			<h2> EggsChange Register</h2>
				<div class=\"login-form\">
					<form method=\"post\" action=\"register.php\" enctype=\"multipart/form-data\">
						<div id=\"html_element\"></div>
							<input id=\"username\" type=\"text\" name=\"username\"  placeholder=\"username\"></br>
							<input id=\"password\" type=\"password\" name=\"password\" placeholder=\"password\"></br>
							<input id=\"repeate_password\" type=\"password\" name=\"repeate_password\" placeholder=\"repeate password\"></br>
							</br>
							<div id=\"recaptcha\">
								<div class=\"g-recaptcha\" data-sitekey=\"6Lehb3AUAAAAAA9qX_Jb6B5bhZP4MUuyYORvnhOa-gus\"></div>
							</div>
							<input type=\"submit\" value=\"Register\" >
					</form>
				</div>
			<form>
				<input type=\"button\" value=\"Got an account -> Login\" onclick=\"window.location.href='login.php'\" />
			</form>
	";   
    }

	
	/*
	echo
	"
			<h2> EggsChange Register</h2>
        		<form method=\"post\" action=\"register.php\">
				<div id=\"html_element\"></div>
                		<input id=\"username\" type=\"text\" name=\"username\"  placeholder=\"username\"></br>
                		<input id=\"password\" type=\"password\" name=\"password\" placeholder=\"password\"></br>
				</br>
                		<input type=\"submit\" value=\"Register\" >
        		</form>
			<form>
				<input type=\"button\" value=\"Got an account -> Login\" onclick=\"window.location.href='login.php'\" />
			</form>
	";
	*/

	if ($fail_reason != "none")
	{
		echo "<font color=\"red\">$fail_reason</font>";
	}
}


if (!empty($_POST['username']) and !empty($_POST['password']) and !empty($_POST['repeate_password']))
{      
	$username = isset($_POST['username'])? $_POST['username'] : '';
	$password = isset($_POST['password'])? $_POST['password'] : '';
	$repeate_password = isset($_POST['repeate_password'])? $_POST['repeate_password'] : '';


	$username = (string)$username;
	$password = (string)$password;
	$repeate_password = (string)$repeate_password;
	
	if ($repeate_password != $password)
	{
		print_html_main("Passwords have to be the same");
		fok();
	}
	
	if (empty($_POST["g-recaptcha-response"]))
	{
		print_html_main("make sure to click the captcha");
		fok();
	}
	
	$response = $_POST["g-recaptcha-response"];
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => SECRET_CAPTCHA_KEY,
		'response' => $_POST["g-recaptcha-response"]
	);
	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data),
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
		)
	);
	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);
	$captcha_success=json_decode($verify);
	if ($captcha_success->success==false) {
		print_html_main("Detected bot");
		fok();
	} else if ($captcha_success->success==true) {
		//echo "<p>proofed human!</p>";
	}
	else
	{
		print_html_main("Something went horrible wrong. Please contact an admin.");
		fok();
	}

	if (strlen($username) > 32)
    {
        print_html_main("Username too long.");
        fok();
    }
    if (strlen($password) > 64)
    {
        print_html_main("Password too long.");
        fok();
    }



	if (!preg_match('/^[a-z0-9]+$/i', $username))
	{
		print_html_main("Only letters and numbers in username allowed");
		fok();
	}

	$db = new PDO(DATABASE_PATH);
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
	$stmt->execute(array($username));
	$db = null;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($rows || $username == "SERVER") //block the name SERVER because it is needed for the transaction func
	{
		print_html_main("Username already exsits");
		fok();
	}
	else
	{
        $current_date = date("Y-m-d H:i:s");
        
        //=======================================
        // F R I E N D invite C O N N E C T I O N
        //=======================================
        if (!empty($_POST['invitor']))
        {
            $invitor = isset($_POST['invitor'])? $_POST['invitor'] : '';
            $invitor = (string)$invitor;
            if (strlen($invitor) > 32)
            {
                print_html_main("Friend name too long.");
                fok();
            }
            if (!preg_match('/^[a-z0-9]+$/i', $invitor))
	        {
                print_html_main("Invalid friend name (only letters and numbers allowed)");
                fok();
            }
        
        
            $db = new PDO(DATABASE_PATH);
            $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
            $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
	        $stmt->execute(array($invitor));
	        $db = null;
	        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if (!$rows || $invitor == "SERVER")
            {
                print_html_main("Unknown friend name.");
                fok();
            }
        
            //ADD invite connection to database
    	    $db = new PDO(DATABASE_PATH);
    	    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    	    $stmt = $db->prepare('INSERT INTO Invites (Invited, Invitor, TimeStamp) VALUES (?, ?, ?)');
   		    $stmt->execute(array($username, $invitor, $current_date));
            
            //Increment invites on invitor
    	    $db = new PDO(DATABASE_PATH);
    	    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    	    $stmt = $db->prepare('UPDATE Accounts SET InvitedFriends = InvitedFriends + 1 WHERE Username = ?');
   		    $stmt->execute(array($invitor));
            
        }
        
        
        //=================================
        // A D D Account to D A T A B A S E
        //=================================
    	$db = new PDO(DATABASE_PATH);
    	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    	$stmt = $db->prepare('INSERT INTO Accounts (Username, Password, IP, RegisterDate) VALUES (?, ?, ?, ?)');
   		$stmt->execute(array($username, $password, $_SERVER['REMOTE_ADDR'], $current_date));
		//print_html_main("sucessfully created an account");


        $_SESSION['Username'] = $username;
        echo "Registered account '$username'</br>";
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
	}
}
else if (!empty($_POST['username']) or !empty($_POST['password']))
{
	print_html_main("All fields are required");
}
else //no name or pw given -> ask for it
{
	print_html_main("none");
}
fok();
?>
