<?php
require_once(__DIR__ . "/global.php");

function CreateTableLogin()
{
   class MyDB_Login extends SQLite3
   {
      function __construct()
      {
         $this->open(DATABASE_PATH_RAW) ;
      }
   }
   $check_db = new MyDB_Login() ;
   if(!$check_db) {
      echo $check_db->lastErrorMsg() ;
   } else {
      echo "Opened database successfully\n";
   }

	$sql =<<<EOF
		CREATE TABLE Login
		(
		ID					INTEGER		PRIMARY KEY		AUTOINCREMENT,
		Username			TEXT,
        TimeStamp           DATE,
        IP                  TEXT,
        Location            TEXT,
        Browser             TEXT,
        OS                  TEXT,
        OtherDetails        TEXT
		);
EOF;

   $ret = $check_db->exec($sql) ;
   if(!$ret) {
      echo $check_db->lastErrorMsg() . "</br>";
   } else {
      echo "Login Table created successfully\n</br>";
   }
   $check_db->close();
}
?>
