<?php
require_once(__DIR__ . "/global.php");
HtmlHeader("logout", false);

session_start();
session_destroy(); //clear all before new login
//session_start();


echo "<a>Successfully logged out</a><br>";

       //$_SESSION['IsLogged'] = "loggedout";
        echo "
            <script type=\"text/javascript\">
                    window.setTimeout(function()
                {
                        window.location.href='index.php';
                    }, 2000);
            </script>
        ";
        echo "<form><input type=\"button\" value=\"okay\" onclick=\"window.location.href='index.php'\" /></form>";
fok();
?>
