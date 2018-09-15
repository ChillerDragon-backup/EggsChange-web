<?php
//=======================
// cronjob_invites.php  =
//                      =
// checks for invite    =
// reward if a friend   =
// became active at     =
// eggschange -->       =
// reward               =
//=======================

// crontab -e (to edit this procedure)
// 20 4 * * * php /var/www/html/cronjob_invites.php > /var/www/EggsChange/invites_log.txt
// 20 5 * * * php /var/www/html/cronjob_wish_expire.php > /var/www/EggsChange/expire_log.txt

require_once(__DIR__ . "/global.php");


function IsRewardCalculator($reward_state, $OtherWishesAccepted, $OwnWishesAccepted)
{
    if ($reward_state == 0)
    {
        if ($OtherWishesAccepted > 4)
        {
            return 1;
        }
    }
    else if ($reward_state == 1)
    {
        if ($OtherWishesAccepted > 9)
        {
            return 1;
        }
    }
    else if ($reward_state == 2)
    {
        if ($OtherWishesAccepted > 14)
        {
            return 1;
        }
    }
    else if ($reward_state == 3)
    {
        if ($OtherWishesAccepted > 19)
        {
            return 1;
        }
    }
    else if ($reward_state == 4)
    {
        if ($OtherWisesAccepted > 24 && $OwnWishesAccepted > 19)
        {
            return 1;
        }
    }
    
    return 0;
}


$current_date = date_create(date("Y-m-d"));
$current_date_str = $current_date->format('Y-m-d');

$db = new PDO(ABSOLUTE_DATABASE_PATH);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
$stmt = $db->prepare('SELECT * FROM Invites, Accounts WHERE reward_STATE < ? AND Username = Invited');
$stmt->execute(array(MAX_INVITE_REWARD_STATE));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows)
{
    echo "<h1>Invites reward</h1>";
    //print_r($rows);
    
    echo "
        <table>
            <tr>
                <th>ID</th>
                <th>Invitor</th>
                <th>Invited</th>
                <th>OtherWishes</th>
                <th>OwnWishes</th>
                <th>State</th>
                <th>IsRewarded</th>
            </tr>
    ";
    
    foreach ($rows as $row)
    {
        $invitor = $row['Invitor'];
        $invited = $row['Invited'];
        $OtherWishesAccepted = $row['OtherWishesAccepted'];
        $OwnWishesAccepted = $row['OwnWishesAccepted'];
        $reward_state = $row['reward_STATE'];
        $IsRewarded = IsRewardCalculator($reward_state, $OtherWishesAccepted, $OwnWishesAccepted);
        $invite_ID = $row['InviteID'];
        $invite_reward_points = 0;
        
        if ($IsRewarded)
        {
            if ($reward_state == 0)
            {
                $invite_reward_points = 1;
            }
            else if ($reward_state == 1)
            {
                $invite_reward_points = 3;
            }
            else if ($reward_state == 2)
            {
                $invite_reward_points = 6;
            }
            else if ($reward_state == 3)
            {
                $invite_reward_points = 15;
            }
            else if ($reward_state == 4)
            {
                $invite_reward_points = 20;
            }
            
            SendPoints($invitor, "SERVER", $invite_reward_points, "rewarded for '" . $invited . "'s activity (Invite)");
            
            $reward_state++;
            
            $db = new PDO(ABSOLUTE_DATABASE_PATH);
            $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
            $stmt = $db->prepare('UPDATE Invites SET reward_STATE = ? WHERE InviteID = ?');
            $stmt->execute(array($reward_state, $invite_ID));
            
            echo "</br> updated ID=$invite_ID to STATE=$reward_state</br>";
        }
        
        
        
        //echo "<a>Invitor=$invitor Invited=$invited OtAcc=$OtherWishesAccepted OwAcc=$OwnWishesAccepted</br></a>";
        //echo "<a style=\"display:inline-block;white-space:pre-wrap;\">Invitor=$invitor &#09; Invited=$invited &#09; OtAcc=$OtherWishesAccepted &#09; OwAcc=$OwnWishesAccepted &#09 RewState=$reward_state</a></br>";
        
        echo "
            <tr>
                <th>$invite_ID</th>
                <th>$invitor</th>
                <th>$invited</th>
                <th>$OtherWishesAccepted</th>
                <th>$OwnWishesAccepted</th>
                <th>$reward_state</th>
                <th>$IsRewarded</th>
            </tr>
        ";
    }
    
    echo "
        </table>
    ";
}


?>
