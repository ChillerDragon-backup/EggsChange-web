<?php
//===============
// EggsChange
// account.php
//===============

session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("account", true);

if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
{
	echo "<a>you have to be logged in.</a></br>";
	echo "<form><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
	fok();
}

//Check valid account state
CheckAccountState($_SESSION['Username']);

//init vars
$username = "error";
$mail = "error";
$invited_friends = 0;
$OwnWishesTotal = 0;
$OtherWishesTotal = 0;
$RegisterDate = "error";

function GetUserData()
{
    global $username;
    global $mail;
    global $invited_friends;
    global $OwnWishesTotal;
    global $OtherWishesTotal;
    global $RegisterDate;
    $username = $_SESSION['Username'];
    $db = new PDO(DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Accounts WHERE Username = ?');
    $stmt->execute(array($username));
    $db = NULL;
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows)
    {
        $mail = $rows[0]['Mail'];
        $invited_friends = $rows[0]['InvitedFriends'];
        $OwnWishesTotal = $rows[0]['OwnWishesTotal'];
        $OtherWishesTotal = $rows[0]['OtherWishesTotal'];
        $RegisterDate = $rows[0]['RegisterDate'];
    }
}

function PrintMailUpdateOutput($message, $error)
{
    if ($error == true)
    {
        echo "<font color=\"red\">$message</font></br>";
    }
    else
    {
        echo "<font color=\"green\">$message</font></br>";
    }
    echo "</br></br>";
}

function UpdateMail($mail)
{
    $mail = (string)$mail;
    if (strlen($mail) < 5)
    {
        PrintMailUpdateOutput("Mail to short.", true);
        return;
    }
    if (strlen($mail) > 512)
    {
        PrintMailUpdateOutput("Mail too long.", true);
        return;
    }
    if (filter_var($mail, FILTER_VALIDATE_EMAIL)) 
    {
        PrintMailUpdateOutput("Mail successfully updated to '$mail'.", false);
        $db = new PDO(DATABASE_PATH);
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $stmt = $db->prepare('UPDATE Accounts SET Mail = ? WHERE Username = ?');
        $stmt->execute(array($mail, $_SESSION['Username']));
        //update mail and all variables after change:
        GetUserData();
        return;
    }
    else
    {
        PrintMailUpdateOutput("Invalid mail.", true);
        return;
    }
}


GetUserData();

//echo "<h1>$username</h1>";

echo "<h2>Mail</h2>";

if (!empty($_POST['mail']))
{
    UpdateMail($_POST['mail']);
}

if (empty($mail))
{
    echo "<a>Warning no mail set yet. Set an mail to be able to restore password on loose.</br></a>";
    echo "
				<form method=\"post\" action=\"account.php\" enctype=\"multipart/form-data\">
				    <input id=\"mail\" type=\"text\" name=\"mail\"  placeholder=\"Set e-mail\">
				    <input type=\"submit\" value=\"Set e-mail\" >
                </form>
    ";
}
else
{
    echo "<a>Mail: $mail </br></a>";
    echo "
            <div class=\"login-form\">
				<form method=\"post\" action=\"account.php\" enctype=\"multipart/form-data\">
				    <input id=\"mail\" type=\"text\" name=\"mail\"  placeholder=\"new e-mail\">
				    <input type=\"submit\" value=\"Change e-mail\" >
                </form>
            </div>
    ";
}

echo "<h2>Stats</h2>";

echo "<a>Wishes wished: $OwnWishesTotal</br>Wishes tried to fullfill: $OtherWishesTotal</br></a>";

echo "</br><form class=\"form\"><input type=\"button\" value=\"Logout\" onclick=\"window.location.href='logout.php'\" /></form>";
fok();
?>
