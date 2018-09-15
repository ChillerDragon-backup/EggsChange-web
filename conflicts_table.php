<?php
require_once(__DIR__ . "/global.php");

function CreateTableConflicts()
{
   class MyDB_Conflicts extends SQLite3
   {
      function __construct()
      {
         $this->open(DATABASE_PATH_RAW) ;
      }
   }
   $check_db = new MyDB_Conflicts() ;
   if(!$check_db) {
      echo $check_db->lastErrorMsg() ;
   } else {
      echo "Opened database successfully\n";
   }


	$sql =<<<EOF
		CREATE TABLE Conflicts
		(
		ID					INTEGER		PRIMARY KEY		AUTOINCREMENT,
		wish_ID				INTEGER,
		judger				TEXT,
		IsRight				TEXT,
        VoteValue           INTEGER
		);
EOF;

   $ret = $check_db->exec($sql) ;
   if(!$ret) {
      echo $check_db->lastErrorMsg() . "</br>";
   } else {
      echo "Conflicts Table created successfully\n</br>";
   }
   $check_db->close();
}
?>
