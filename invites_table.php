<?php
require_once(__DIR__ . "/global.php");

function CreateTableInvites()
{
   class MyDB_Invites extends SQLite3
   {
      function __construct()
      {
         $this->open(DATABASE_PATH_RAW) ;
      }
   }
   $check_db = new MyDB_Invites() ;
   if(!$check_db) {
      echo $check_db->lastErrorMsg() ;
   } else {
      echo "Opened database successfully\n";
   }

	$sql =<<<EOF
		CREATE TABLE Invites
		(
        ID					INTEGER		         PRIMARY KEY		AUTOINCREMENT,
		Invitor   			TEXT,
        Invited             TEXT,
        reward_STATE        INTEGER              DEFAULT 0,
        TimeStamp           DATE
        );
EOF;

   $ret = $check_db->exec($sql) ;
   if(!$ret) {
      echo $check_db->lastErrorMsg() . "</br>";
   } else {
      echo "Invites Table created successfully\n</br>";
   }
   $check_db->close();
}
?>
