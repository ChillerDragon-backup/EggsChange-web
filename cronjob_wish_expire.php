<?php
	// crontab -e (to edit this procedure)
	// 20 4 * * * php /var/www/html/cronjob_invites.php > /var/www/EggsChange/invites_log.txt
	// 20 5 * * * php /var/www/html/cronjob_wish_expire.php > /var/www/EggsChange/expire_log.txt

	require_once(__DIR__ . "/global.php");

	$current_date = date_create(date("Y-m-d"));
    $current_date_str = $current_date->format('Y-m-d');

    $db = new PDO(ABSOLUTE_DATABASE_PATH);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $stmt = $db->prepare('SELECT * FROM Wishes WHERE wish_exp_date < ? AND wish_STATE = 0');
    $stmt->execute(array($current_date_str));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	
	
    if ($rows)
    {
		echo "<h1>Expired wishes (current_date_object) str=$current_date_str</h1>";
		
		echo "</br>---- wishes -----</br>";
		
		foreach ($rows as $row)
		{
			if (empty($row['wish_name']))
				break;
			
			$wisher = $row['wisher'];
			$wish_id = $row['ID'];
			$name = $row['wish_name'];
			$exp_date = $row['wish_exp_date'];
			$reward = $row['wish_reward'];
			echo "ID=$wish_id wisher=$wisher reward=$reward title=$name date=$exp_date</br>";
			
			//DELETE WISH
            /*
			$db = new PDO(ABSOLUTE_DATABASE_PATH);
			$stmt = $db->prepare('UPDATE Wishes SET wish_STATE = 3 WHERE ID = ?');
			$stmt->execute(array($wish_id));
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            */
            $del_reason = "expired at [" . $exp_date . "]";
			if (DeleteWish($wish_id, $del_reason))
			{
				echo "<font color=\"red\">Failed to delete the wish</font>";
			}
		
			//GIVE POINTS
			$db = new PDO(ABSOLUTE_DATABASE_PATH);
			$stmt = $db->prepare('UPDATE Accounts SET Points = Points + ? WHERE Username = ?');
			$stmt->execute(array($reward, $wisher));
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows)
			{
				echo "<font color=\"red\">Failed to give points back</font>";
			}
		}
		
		echo "----  -------  -----</br>";
	}
	else
	{
		echo "no wishes expired.";
	}
?>