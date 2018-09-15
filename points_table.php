<?php
require_once(__DIR__ . "/global.php");

function CreateTablePoints()
{
   class MyDB_Points extends SQLite3
   {
      function __construct()
      {
         $this->open(DATABASE_PATH_RAW) ;
      }
   }
   $check_db = new MyDB_Points() ;
   if(!$check_db) {
      echo $check_db->lastErrorMsg() ;
   } else {
      echo "Opened database successfully\n";
   }

	$sql =<<<EOF
		CREATE TABLE Points
		(
        ID					INTEGER		PRIMARY KEY		AUTOINCREMENT,
		Reciver   			TEXT    	NOT NULL,
        Sender              TEXT        NOT NULL,
		Points    			INTEGER,
        Reason              TEXT,
        TransactionDate     DATE
        );
EOF;

   $ret = $check_db->exec($sql) ;
   if(!$ret) {
      echo $check_db->lastErrorMsg() . "</br>";
   } else {
      echo "Points Table created successfully\n</br>";
   }
   $check_db->close();
}
?>
