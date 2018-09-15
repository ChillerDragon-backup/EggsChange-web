<?php
require_once(__DIR__ . "/global.php");

function CreateTableUsers()
{
   class MyDB extends SQLite3
   {
      function __construct()
      {
         $this->open(DATABASE_PATH_RAW) ;
      }
   }
   $check_db = new MyDB() ;
   if(!$check_db) {
      echo $check_db->lastErrorMsg() ;
   } else {
      echo "Opened database successfully\n";
   }

	$sql =<<<EOF
		CREATE TABLE Accounts
		(ID					INTEGER		         PRIMARY KEY		AUTOINCREMENT,
		Username			TEXT    	         NOT NULL,
		Password			TEXT    	         NOT NULL,
        OldPassword         TEXT,
        Mail                TEXT,
		Points				INTEGER		         DEFAULT 0,
		IP					TEXT,
		STATE				INTEGER 	         DEFAULT 0,
		missionID			INTEGER 	         DEFAULT 0,
		InvitedFriends		INTEGER		         DEFAULT 0,
		OwnWishesTotal		INTEGER		         DEFAULT 0,
		OtherWishesTotal	INTEGER		         DEFAULT 0,
		OwnWishesAccepted	INTEGER		         DEFAULT 0,
		OtherWishesAccepted	INTEGER		         DEFAULT 0,
		RegisterDate		DATE,
		LastLogin			DATE,
		REPm				INTEGER 	         DEFAULT 0,
		REPp				INTEGER 	         DEFAULT 0);
EOF;

   $ret = $check_db->exec($sql) ;
   if(!$ret) {
      echo $check_db->lastErrorMsg() . "</br>";
   } else {
      echo "Users Table created successfully\n</br>";
   }
   $check_db->close();
}
?>
