<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("Transactions");

if (empty($_SESSION['IsLogged']) || $_SESSION['IsLogged'] != "online")
{
    echo "<h1> EggsChange </h1>";
    echo "<a>you have to be logged in.</a></br>";
    echo "<form class=\"form\"><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
    fok();
}


$db = new PDO(ABSOLUTE_DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$stmt = $db->prepare('SELECT Points FROM Accounts WHERE Username = ?');
$stmt->execute(array($_SESSION['Username']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$db = NULL;

$balance = 0;

if ($rows)
{
    $balance = $rows[0]['Points'];
}

echo "<h1>Points Transactions</h2>";
echo "<a>Total balance: <strong>$balance</strong></a></br>";

echo "<h2>Incoming (last 20)</h2>";

$db = new PDO(ABSOLUTE_DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$stmt = $db->prepare('SELECT * FROM Points WHERE Reciver = ? ORDER BY TransactionDate DESC LIMIT 20');
$stmt->execute(array($_SESSION['Username']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
    foreach ($rows as $row)
    {
        $send = $row['Sender'];
        $reason = $row['Reason'];
        $date = $row['TransactionDate'];
        $value = $row['Points'];
        
        //working but usernames should be private
        /*
        if ($send == "SERVER")
        {
            echo "+$value ($reason) [$date]</br>";
        }
        else
        {
            echo "+$value from user '$send' ($reason) [$date]</br>";
        }
        */
        echo "+$value ($reason) [$date]</br>";
    }
}
else
{
    echo "You didn't have any incoming transactions yet.</br>";
}
    
echo "<h2>Outgoing (last 20)</h2>";

$db = new PDO(ABSOLUTE_DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$stmt = $db->prepare('SELECT * FROM Points WHERE Sender = ? ORDER BY TransactionDate DESC LIMIT 20');
$stmt->execute(array($_SESSION['Username']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
    foreach ($rows as $row)
    {
        $recv = $row['Reciver'];
        $reason = $row['Reason'];
        $date = $row['TransactionDate'];   
        $value = $row['Points'];
    
        //Working but usernames should be private
        /*
        if ($recv == "SERVER")
        {
            echo "-$value ($reason) [$date]</br>";
        }
        else
        {
            echo "-$value to user '$recv' ($reason) [$date]</br>";
        }
        */
        echo "-$value ($reason) [$date]</br>";
    }
}
else
{
    echo "You didn't have any outgoing transactions yet.</br>";
}

echo "<form class=\"form\"><input type=\"button\" value=\"Back\" onclick=\"window.location.href='index.php'\"/></form>";
fok();
?>