<?php
require_once(__DIR__ . "/global.php");

function CreateTableWishes()
{
   class MyDB_Wishes extends SQLite3
   {
      function __construct()
      {
         $this->open(DATABASE_PATH_RAW) ;
      }
   }
   $check_db = new MyDB_Wishes() ;
   if(!$check_db) {
      echo $check_db->lastErrorMsg() ;
   } else {
      echo "Opened database successfully\n";
   }

	//wish_STATE 0=Ready 1=Done(waiting for reward) 2=fullfilled 3=deleted  4=declined

	$sql =<<<EOF
		CREATE TABLE Wishes
		(
		ID					INTEGER		PRIMARY KEY		AUTOINCREMENT,
		wish_name			TEXT,
		wish_desc			TEXT,
		wish_img			TEXT,
		wisher				TEXT,
		wish_fullfiller		TEXT,
		wish_proof			TEXT,
		wish_category		TEXT,
		wish_difficulty		INTEGER		DEFAULT 0,
		wish_done_txt		TEXT,
		wish_notdone_txt	TEXT,
		wish_date			DATE,
        wish_fullfill_date  DATE,
        wish_done_date      DATE,
		wish_exp_date		DATE,
        archive_date        DATE,
		wish_reward	 		INTEGER,
        delete_reason       TEXT,
		wish_STATE			INTEGER		DEFAULT 0
		);
EOF;

   $ret = $check_db->exec($sql) ;
   if(!$ret) {
      echo $check_db->lastErrorMsg() . "</br>";
   } else {
      echo "Table created successfully\n</br>";
   }
   $check_db->close();
}
?>
